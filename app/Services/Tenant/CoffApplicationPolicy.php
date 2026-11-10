<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CoffApplicationPolicy
{
    /**
     * @param  array<int, int>  $userIds
     * @return array<int, array{date: string, credits: array<int, array{user_id: int, name: string, days: float}>}>
     */
    public function eligibleDates(array $userIds, ?int $excludeApplicationId = null): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === []) {
            return [];
        }

        [$users, $categoriesByUser] = $this->loadUsersAndCategories($userIds);
        if ($users->count() !== count($userIds)) {
            return [];
        }
        foreach ($userIds as $userId) {
            $categories = $categoriesByUser[$userId] ?? collect();
            if ($categories->isEmpty() || $categories->contains(
                static fn (Category $category): bool => ! $category->is_eligible_for_c_off,
            )) {
                return [];
            }
        }

        $creditsByUser = array_fill_keys($userIds, []);
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances(\d{4})\z/', $tableName) !== 1
                || ! Schema::hasColumns($tableName, ['user_id', 'date', 'is_coff', 'coff_credit_dayes'])) {
                continue;
            }

            foreach (DB::table($tableName)
                ->whereIn('user_id', $userIds)
                ->where('is_coff', true)
                ->where('coff_credit_dayes', '>', 0)
                ->get(['user_id', 'date', 'coff_credit_dayes']) as $attendance) {
                $userId = (int) $attendance->user_id;
                $date = Carbon::parse($attendance->date)->startOfDay();
                if ($this->isExpired($date, $categoriesByUser[$userId] ?? collect())) {
                    continue;
                }
                $creditsByUser[$userId][$date->toDateString()] = (float) $attendance->coff_credit_dayes;
            }
        }

        $applications = DB::table('coff_applications')
            ->whereIn('user_id', $userIds)
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('deleted_at')
            ->when(
                $excludeApplicationId !== null,
                static fn ($query) => $query->where('id', '!=', $excludeApplicationId),
            )
            ->get([
                'user_id',
                'coff_against_date',
                'coff_from_date',
                'coff_to_date',
                'is_half_day',
                'is_first_half',
                'is_second_half',
            ]);

        foreach ($applications as $application) {
            $userId = (int) $application->user_id;
            $date = Carbon::parse($application->coff_against_date)->toDateString();
            if (isset($creditsByUser[$userId][$date])) {
                $creditsByUser[$userId][$date] -= $this->requestedDays((array) $application);
            }
        }

        $firstUserId = $userIds[0];
        $eligibleDates = [];
        foreach (array_keys($creditsByUser[$firstUserId]) as $date) {
            $credits = [];
            foreach ($userIds as $userId) {
                $remainingDays = $creditsByUser[$userId][$date] ?? 0;
                if ($remainingDays <= 0) {
                    continue 2;
                }
                $credits[] = [
                    'user_id' => $userId,
                    'name' => (string) $users->get($userId)->name,
                    'days' => round($remainingDays, 2),
                ];
            }
            $eligibleDates[] = ['date' => $date, 'credits' => $credits];
        }

        usort($eligibleDates, static fn (array $a, array $b): int => strcmp($a['date'], $b['date']));

        return $eligibleDates;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     */
    public function validate(array $payload, array $userIds, ?int $excludeApplicationId = null): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $fromDate = Carbon::parse($payload['coff_from_date'])->startOfDay();
        $toDate = Carbon::parse($payload['coff_to_date'])->startOfDay();
        $againstDate = Carbon::parse($payload['coff_against_date'])->startOfDay();
        $isHalfDay = (bool) ($payload['is_half_day'] ?? false);
        $isFirstHalf = (bool) ($payload['is_first_half'] ?? false);
        $isSecondHalf = (bool) ($payload['is_second_half'] ?? false);

        if ($toDate->lt($fromDate)
            || ($isHalfDay && (! $fromDate->isSameDay($toDate) || $isFirstHalf === $isSecondHalf))
            || (! $isHalfDay && $fromDate->isSameDay($toDate) && ($isFirstHalf || $isSecondHalf))) {
            $this->reject('is_half_day', 'The selected COFF date range and half-day options are invalid.');
        }

        $requestedDays = $this->requestedDays($payload);
        if ($requestedDays <= 0) {
            $this->reject('coff_from_date', 'The selected COFF range must be greater than zero days.');
        }

        [$users, $categoriesByUser] = $this->loadUsersAndCategories($userIds);
        $applications = $this->applicationsForUsers($userIds, $fromDate, $toDate, $excludeApplicationId);
        $eligibleDates = collect($this->eligibleDates($userIds, $excludeApplicationId))->keyBy('date');
        $dateCredits = $eligibleDates->get($againstDate->toDateString());
        if ($dateCredits === null) {
            $this->reject('coff_against_date', 'The selected COFF against date has no valid, unexpired credit for every selected employee.');
        }

        foreach ($userIds as $userId) {
            $user = $users->get($userId);
            $categories = $categoriesByUser[$userId] ?? collect();
            if ($user === null || $categories->isEmpty()) {
                $this->reject('users', 'Every selected employee must have a COFF category assigned.');
            }
            foreach ($categories as $category) {
                if (! $category->is_eligible_for_c_off) {
                    $this->reject('users', 'COFF is not enabled for every selected employee category.');
                }
            }

            if (($isHalfDay || $isFirstHalf || $isSecondHalf)
                && $categories->contains(static fn (Category $category): bool => ! $category->allow_halfday_c_off)) {
                $this->reject('is_half_day', 'Half-day COFF is not allowed for every selected employee category.');
            }

            if ($fromDate->lt(Carbon::today())) {
                foreach ($categories as $category) {
                    if (! $category->allow_backdated_leave) {
                        $this->reject('coff_from_date', 'Backdated COFF is not allowed for every selected employee category.');
                    }
                    $backdatedLimit = (int) ($category->backdated_day_limit ?? 0);
                    if ($backdatedLimit > 0 && $fromDate->lt(Carbon::today()->subDays($backdatedLimit))) {
                        $this->reject('coff_from_date', 'The selected COFF date is outside the allowed backdated day limit.');
                    }
                }
            }

            foreach ($categories as $category) {
                $advanceLimit = (int) ($category->advance_day_limit ?? 0);
                if ($toDate->gt(Carbon::today()->addDays($advanceLimit))) {
                    $this->reject('coff_to_date', 'The selected COFF date is outside the allowed advance day limit.');
                }

                $minimumDays = (float) ($category->min_avail ?? 0);
                if ($minimumDays > 0 && $requestedDays < $minimumDays) {
                    $this->reject('coff_from_date', 'The requested COFF duration is below the category minimum.');
                }
                $maximumDays = (float) ($category->max_avail ?? 0);
                if ($maximumDays > 0 && $requestedDays > $maximumDays) {
                    $this->reject('coff_to_date', 'The requested COFF duration exceeds the category maximum.');
                }
                $maximumAccumulation = (float) ($category->maximum_accumulation ?? 0);
                if ($maximumAccumulation > 0 && $requestedDays > $maximumAccumulation) {
                    $this->reject('coff_to_date', 'The requested COFF duration exceeds the category maximum accumulation.');
                }

                $monthLimit = (int) ($category->maximum_request_in_a_month ?? 0);
                $yearLimit = (int) ($category->maximum_request_in_a_year ?? 0);
                $userApplications = $applications->get($userId, collect());
                for ($month = $fromDate->copy()->startOfMonth(); $month->lte($toDate); $month->addMonth()) {
                    if ($monthLimit > 0 && $this->countOverlappingApplications(
                        $userApplications,
                        $month->copy()->startOfMonth(),
                        $month->copy()->endOfMonth(),
                    ) >= $monthLimit) {
                        $this->reject('coff_from_date', 'The employee has reached the maximum COFF requests allowed in a month.');
                    }
                }
                for ($year = $fromDate->copy()->startOfYear(); $year->lte($toDate); $year->addYear()) {
                    if ($yearLimit > 0 && $this->countOverlappingApplications(
                        $userApplications,
                        $year->copy()->startOfYear(),
                        $year->copy()->endOfYear(),
                    ) >= $yearLimit) {
                        $this->reject('coff_from_date', 'The employee has reached the maximum COFF requests allowed in a year.');
                    }
                }
            }

            $employeeCredit = collect($dateCredits['credits'])->firstWhere('user_id', $userId);
            $availableDays = (float) ($employeeCredit['days'] ?? 0);
            foreach ($categories as $category) {
                $maximumAccumulation = (float) ($category->maximum_accumulation ?? 0);
                if ($maximumAccumulation > 0) {
                    $availableDays = min($availableDays, $maximumAccumulation);
                }
            }
            if ($requestedDays > $availableDays) {
                $this->reject('coff_against_date', 'The requested COFF duration exceeds the selected date\'s remaining credit balance.');
            }
        }
    }

    /**
     * @param  array<int, int>  $userIds
     * @return array{0: Collection<int, User>, 1: array<int, Collection<int, Category>>}
     */
    private function loadUsersAndCategories(array $userIds): array
    {
        $users = User::query()->whereIn('id', $userIds)->get(['id', 'name', 'category_id'])->keyBy('id');
        $categoryIds = $users->flatMap(static fn (User $user): array => (array) $user->category_id)
            ->filter(static fn (mixed $id): bool => is_numeric($id))
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        $categories = Category::query()->whereIn('id', $categoryIds)->get()->keyBy('id');
        $categoriesByUser = [];

        foreach ($users as $user) {
            $categoriesByUser[(int) $user->id] = collect((array) $user->category_id)
                ->map(static fn (mixed $id): ?Category => is_numeric($id) ? $categories->get((int) $id) : null)
                ->filter(static fn (?Category $category): bool => $category !== null)
                ->values();
        }

        return [$users, $categoriesByUser];
    }

    private function isExpired(Carbon $againstDate, Collection $categories): bool
    {
        foreach ($categories as $category) {
            $lapseDays = (int) ($category->c_off_lapse_in_days ?? 0);
            if ($lapseDays > 0 && $againstDate->copy()->addDays($lapseDays)->lt(Carbon::today())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, int>  $userIds
     * @return Collection<int, Collection<int, object>>
     */
    private function applicationsForUsers(
        array $userIds,
        Carbon $fromDate,
        Carbon $toDate,
        ?int $excludeApplicationId,
    ): Collection {
        return DB::table('coff_applications')
            ->whereIn('user_id', $userIds)
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('deleted_at')
            ->whereDate('coff_to_date', '>=', $fromDate->copy()->startOfYear()->toDateString())
            ->whereDate('coff_from_date', '<=', $toDate->copy()->endOfYear()->toDateString())
            ->when(
                $excludeApplicationId !== null,
                static fn ($query) => $query->where('id', '!=', $excludeApplicationId),
            )
            ->get([
                'user_id',
                'coff_from_date',
                'coff_to_date',
            ])
            ->groupBy('user_id');
    }

    /**
     * @param  Collection<int, object>  $applications
     */
    private function countOverlappingApplications(Collection $applications, Carbon $periodStart, Carbon $periodEnd): int
    {
        return $applications->filter(static function (object $application) use ($periodStart, $periodEnd): bool {
            return Carbon::parse($application->coff_from_date)->startOfDay()->lte($periodEnd)
                && Carbon::parse($application->coff_to_date)->startOfDay()->gte($periodStart);
        })->count();
    }

    /**
     * @param  array<string, mixed>  $application
     */
    private function requestedDays(array $application): float
    {
        if ((bool) ($application['is_half_day'] ?? false)) {
            return 0.5;
        }

        $fromDate = Carbon::parse($application['coff_from_date'])->startOfDay();
        $toDate = Carbon::parse($application['coff_to_date'])->startOfDay();
        $days = $fromDate->diffInDays($toDate) + 1;
        if ((bool) ($application['is_second_half'] ?? false)) {
            $days -= 0.5;
        }
        if ((bool) ($application['is_first_half'] ?? false)) {
            $days -= 0.5;
        }

        return max(0, $days);
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
