<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateShortLeaveApplicationApprovals;
use App\Models\Tenant\Category;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShortLeaveApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, int $actorId): ShortLeaveApplication
    {
        $userId = (int) $payload['user_id'];
        unset($payload['user_id']);

        return $this->createForUsers($payload, [$userId], $actorId)[0];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @return array<int, ShortLeaveApplication>
     */
    public function createForUsers(array $payload, array $userIds, int $actorId): array
    {
        unset($payload['user_id']);
        $payload = array_replace($payload, ['status' => 1]);

        return DB::transaction(function () use ($payload, $userIds, $actorId): array {
            $applications = [];
            foreach (array_values(array_unique($userIds)) as $userId) {
                $user = User::query()->lockForUpdate()->find($userId);
                if ($user === null) {
                    throw ValidationException::withMessages([
                        'users' => 'The selected employee could not be found.',
                    ]);
                }

                $this->validateCategoryRules($user, $payload);
                $flowId = $this->approvalFlowId($userId);
                $application = ShortLeaveApplication::query()->create($payload + [
                    'user_id' => $userId,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                CreateShortLeaveApplicationApprovals::dispatch(
                    (int) $application->id,
                    [$flowId],
                    $actorId,
                )->onConnection('database_tenant')->onQueue('processing')->afterCommit();

                $applications[] = $application;
            }

            return $applications;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @return array<string, array<int, string>>
     */
    public function liveValidationErrors(
        array $payload,
        array $userIds,
        ?int $ignoreApplicationId = null,
        bool $dateOnly = false,
    ): array
    {
        foreach (array_values(array_unique($userIds)) as $userId) {
            $user = User::query()->find((int) $userId);
            if ($user === null) {
                return ['users' => ['The selected employee could not be found.']];
            }

            try {
                $this->validateCategoryRules($user, $payload, $ignoreApplicationId, $dateOnly);
                if (! $dateOnly) {
                    $this->approvalFlowId((int) $user->id);
                }
            } catch (ValidationException $exception) {
                $errors = $exception->errors();
                if (isset($errors['user_id'])) {
                    $errors['users'] = $errors['user_id'];
                    unset($errors['user_id']);
                }

                return $errors;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(ShortLeaveApplication $application, array $payload, int $actorId): void
    {
        unset($payload['status']);

        DB::transaction(function () use ($application, $payload, $actorId): void {
            $application = ShortLeaveApplication::query()
                ->with('approval_statuses')
                ->lockForUpdate()
                ->findOrFail($application->id);
            $user = User::query()->lockForUpdate()->findOrFail((int) $payload['user_id']);
            $this->validateCategoryRules($user, $payload, (int) $application->id);
            app(ShortLeaveAttendancePunchService::class)->removeGeneratedPunches($application);
            $this->reverseApprovedMinutes($application);
            $flowId = $this->approvalFlowId((int) $payload['user_id']);
            $application->update($payload + ['updated_by' => $actorId]);

            CreateShortLeaveApplicationApprovals::dispatch(
                (int) $application->id,
                [$flowId],
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    public function delete(ShortLeaveApplication $application): void
    {
        DB::transaction(function () use ($application): void {
            $application = ShortLeaveApplication::query()
                ->with('approval_statuses')
                ->lockForUpdate()
                ->findOrFail($application->id);
            app(ShortLeaveAttendancePunchService::class)->removeGeneratedPunches($application);
            $this->reverseApprovedMinutes($application);
            $application->delete();
        });
    }

    private function reverseApprovedMinutes(ShortLeaveApplication $application): void
    {
        if ((int) $application->status !== 1 || $application->overallApprovalStatus() !== 'approved') {
            return;
        }

        $user = User::query()->lockForUpdate()->findOrFail($application->user_id);
        $user->short_leave_minutes = max(
            0,
            (int) $user->short_leave_minutes - $application->creditMinutes(),
        );
        $user->save();
    }

    private function approvalFlowId(int $userId): int
    {
        $user = User::query()
            ->with('short_leave_approval_flow.approval_flow_details')
            ->find($userId);

        if ($user === null) {
            throw ValidationException::withMessages([
                'user_id' => 'The selected employee could not be found.',
            ]);
        }

        if ($user->short_leave_approval_flow === null) {
            throw ValidationException::withMessages([
                'user_id' => 'A short leave approval flow must be assigned to the selected employee.',
            ]);
        }

        if ($user->short_leave_approval_flow->approval_flow_details->isEmpty()) {
            throw ValidationException::withMessages([
                'user_id' => 'The assigned short leave approval flow has no approval levels.',
            ]);
        }

        return (int) $user->short_leave_approval_flow_id;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateCategoryRules(
        User $user,
        array $payload,
        ?int $ignoreApplicationId = null,
        bool $dateOnly = false,
    ): void
    {
        $categoryId = $user->getAttributeValue('category_id');
        if (is_array($categoryId)) {
            $categoryId = $categoryId[0] ?? null;
        }
        $category = is_numeric((string) $categoryId)
            ? Category::withoutGlobalScopes()->find((int) $categoryId)
            : null;
        if ($category === null) {
            return;
        }

        $employeeName = $user->code !== null ? 'Employee '.$user->code : 'Employee #'.$user->id;
        $date = Carbon::parse((string) $payload['date'])->startOfDay();
        $advanceDays = $category->advance_short_leave_application;
        if (
            $advanceDays !== null
            && $date->greaterThan(today()->addDays((int) $advanceDays)->startOfDay())
        ) {
            throw ValidationException::withMessages([
                'date' => $employeeName.' exceeds the category advance short leave limit of '.$advanceDays.' day(s).',
            ]);
        }

        $monthApplications = ShortLeaveApplication::query()
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->whereBetween('date', [
                $date->copy()->startOfMonth()->toDateString(),
                $date->copy()->endOfMonth()->toDateString(),
            ])
            ->whereDoesntHave('approval_statuses', static function ($query): void {
                $query->whereIn('approval_status', ['rejected', 'cancelled']);
            })
            ->when($ignoreApplicationId !== null, static fn ($query) => $query->where('id', '!=', $ignoreApplicationId))
            ->get();

        $requestMinutes = (int) ($payload['minutes'] ?? 0);
        $maxPerApplication = (int) ($category->max_short_leave_minutes_per_application ?? 0);
        if ($maxPerApplication > 0 && $requestMinutes > $maxPerApplication) {
            throw ValidationException::withMessages([
                'minutes' => $employeeName.' cannot exceed '.$maxPerApplication.' short leave minutes per application.',
            ]);
        }

        $maxOccurrences = (int) ($category->max_occurance_of_short_leave_in_month ?? 0);
        if ($maxOccurrences > 0 && $monthApplications->count() >= $maxOccurrences) {
            throw ValidationException::withMessages([
                'date' => $employeeName.' has reached the maximum of '.$maxOccurrences.' short leave application(s) for this month.',
            ]);
        }

        if ($dateOnly) {
            return;
        }

        $maxMonthlyMinutes = (int) ($category->max_short_leave_minutes_per_month ?? 0);
        if ($maxMonthlyMinutes > 0) {
            $usedMinutes = $monthApplications->sum(
                static fn (ShortLeaveApplication $application): int => $application->creditMinutes(),
            );
            if ($usedMinutes + $requestMinutes > $maxMonthlyMinutes) {
                throw ValidationException::withMessages([
                    'minutes' => $employeeName.' has only '.max(0, $maxMonthlyMinutes - $usedMinutes).' short leave minute(s) remaining this month.',
                ]);
            }
        }
    }
}
