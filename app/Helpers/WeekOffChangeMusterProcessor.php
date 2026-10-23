<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\WOTypeEnumn;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WeekOffChangeMusterProcessor
{
    public static function process(WeekOffChange $weekOffChange): void
    {
        DB::transaction(function () use ($weekOffChange): void {
            $relatedMusters = WeekOffMuster::query()
                ->where('weekoffable_type', WeekOffChange::class)
                ->where('weekoffable_id', $weekOffChange->id)
                ->lockForUpdate()
                ->get();

            $userIds = DB::table('week_off_change_users')
                ->where('week_off_change_id', $weekOffChange->id)
                ->pluck('user_id')
                ->merge($relatedMusters->pluck('user_id'))
                ->unique()
                ->values();

            if ($userIds->isEmpty()) {
                return;
            }

            $changeUserRows = DB::table('week_off_change_users')
                ->whereIn('user_id', $userIds)
                ->get(['user_id', 'week_off_change_id']);
            $changes = WeekOffChange::query()
                ->withoutGlobalScope(DataPolicyFilter::class)
                ->whereIn('id', $changeUserRows->pluck('week_off_change_id')->unique())
                ->with('WeekOffChangeDetails')
                ->orderBy('from_date')
                ->orderBy('id')
                ->get()
                ->keyBy('id');
            $linkedDatesByUser = $relatedMusters
                ->groupBy('user_id')
                ->map(static fn (Collection $musters): array => [
                    'from' => Carbon::parse($musters->min('date'))->startOfDay(),
                    'to' => Carbon::parse($musters->max('date'))->startOfDay(),
                ]);

            foreach ($userIds as $userId) {
                $userChanges = $changeUserRows
                    ->where('user_id', $userId)
                    ->pluck('week_off_change_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->map(static fn (int $id): ?WeekOffChange => $changes->get($id))
                    ->filter()
                    ->sortBy([
                        ['from_date', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values();

                $userRelatedMusters = $relatedMusters->where('user_id', $userId);
                $startDate = $weekOffChange->from_date->copy()->startOfDay();
                $endDate = self::endDate($weekOffChange);
                if ($userRelatedMusters->isNotEmpty()) {
                    $linkedDates = $linkedDatesByUser->get($userId);
                    $startDate = $startDate->min($linkedDates['from']);
                    $endDate = $endDate->max($linkedDates['to']);
                }

                $recordPeriods = $userChanges->map(static fn (WeekOffChange $record): array => [
                    'record' => $record,
                    'from' => $record->from_date->copy()->startOfDay(),
                    'to' => self::endDate($record),
                ]);
                foreach ($recordPeriods as $period) {
                    if ($period['to']->gte($startDate)) {
                        $endDate = $endDate->max($period['to']);
                    }
                }

                $existingMusters = WeekOffMuster::query()
                    ->where('user_id', $userId)
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->lockForUpdate()
                    ->get()
                    ->groupBy(static fn (WeekOffMuster $muster): string => $muster->date->toDateString());
                $changeIds = $userChanges
                    ->pluck('id')
                    ->push($weekOffChange->id)
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all();

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dateKey = $date->toDateString();
                    $activePeriod = $recordPeriods
                        ->filter(static fn (array $period): bool => $period['from']->lte($date) && $period['to']->gte($date))
                        ->last();
                    $musters = $existingMusters->get($dateKey, collect());

                    if ($activePeriod === null) {
                        foreach ($musters as $muster) {
                            if ($muster->weekoffable_type === WeekOffChange::class
                                && in_array((int) $muster->weekoffable_id, $changeIds, true)
                                && ! $muster->is_locked) {
                                $muster->update([
                                    'wo_type' => 0,
                                    'weekoffable_type' => null,
                                    'weekoffable_id' => null,
                                ]);
                            }
                        }

                        continue;
                    }

                    /** @var WeekOffChange $activeChange */
                    $activeChange = $activePeriod['record'];
                    $attributes = [
                        'wo_type' => self::weekOffTypeForDate($activeChange, $date),
                        'weekoffable_type' => WeekOffChange::class,
                        'weekoffable_id' => $activeChange->id,
                    ];
                    $unlockedMusters = $musters->reject(static fn (WeekOffMuster $muster): bool => $muster->is_locked);

                    if ($musters->isEmpty()) {
                        WeekOffMuster::query()->create([
                            'date' => $dateKey,
                            'user_id' => $userId,
                            ...$attributes,
                        ]);

                        continue;
                    }

                    foreach ($unlockedMusters as $muster) {
                        $muster->update($attributes);
                    }
                }
            }
        });
    }

    private static function endDate(WeekOffChange $weekOffChange): Carbon
    {
        if (! $weekOffChange->is_forever) {
            return $weekOffChange->to_date->copy()->startOfDay();
        }

        $financialYear = FinancialYear::query()
            ->where('is_started', true)
            ->orderByDesc('id')
            ->first();

        return Carbon::parse($financialYear?->end_date ?? Carbon::today()->endOfYear())->startOfDay();
    }

    private static function weekOffTypeForDate(WeekOffChange $weekOffChange, Carbon $date): int
    {
        $weekField = match ($date->weekOfMonth) {
            1 => 'first_week',
            2 => 'second_week',
            3 => 'third_week',
            4 => 'fourth_week',
            5 => 'fifth_week',
            default => null,
        };

        if ($weekField === null) {
            return 0;
        }

        $detail = $weekOffChange->WeekOffChangeDetails->first(
            static fn ($detail): bool => (int) $detail->week_days === $date->dayOfWeek
                && (bool) $detail->{$weekField}
        );

        return match ($detail?->wo_type) {
            WOTypeEnumn::FullDay => WOTypeEnumn::FullDay->value,
            WOTypeEnumn::FirstHalf => WOTypeEnumn::FirstHalf->value,
            WOTypeEnumn::SecondHalf => WOTypeEnumn::SecondHalf->value,
            default => 0,
        };
    }
}
