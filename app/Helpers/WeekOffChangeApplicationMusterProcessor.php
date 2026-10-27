<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\WOTypeEnumn;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WeekOffChangeApplicationMusterProcessor
{
    public static function process(WeekOffChangeApplication $application): void
    {
        $application->loadMissing('WeekOffChangeDetails');

        $startDate = $application->from_date?->copy()->startOfDay();
        if ($startDate === null) {
            throw new RuntimeException('The week-off change application has no start date.');
        }

        $endDate = $application->is_forever
            ? self::financialYearEndDate()
            : $application->to_date?->copy()->startOfDay();
        if ($endDate === null || $endDate->lt($startDate)) {
            throw new RuntimeException('The week-off change application has an invalid end date.');
        }

        $userIds = DB::table('week_off_change_application_users')
            ->where('week_off_change_application_id', $application->id)
            ->pluck('user_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        if ($userIds->isEmpty()) {
            throw new RuntimeException('The week-off change application has no employees.');
        }

        DB::transaction(function () use ($application, $startDate, $endDate, $userIds): void {
            foreach ($userIds as $userId) {
                $existingMusters = WeekOffMuster::query()
                    ->where('user_id', $userId)
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->lockForUpdate()
                    ->get()
                    ->groupBy(static fn (WeekOffMuster $muster): string => $muster->date->toDateString());

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dateKey = $date->toDateString();
                    $musters = $existingMusters->get($dateKey, collect());
                    $attributes = [
                        'wo_type' => self::weekOffTypeForDate($application, $date),
                        'weekoffable_type' => WeekOffChangeApplication::class,
                        'weekoffable_id' => $application->id,
                    ];

                    if ($musters->isEmpty()) {
                        WeekOffMuster::query()->create([
                            'date' => $dateKey,
                            'user_id' => $userId,
                            'is_locked' => false,
                            ...$attributes,
                        ]);

                        continue;
                    }

                    foreach ($musters as $muster) {
                        if (! $muster->is_locked) {
                            $muster->update($attributes);
                        }
                    }
                }
            }
        });
    }

    private static function financialYearEndDate(): Carbon
    {
        $endDate = FinancialYear::query()
            ->where('is_started', true)
            ->orderByDesc('id')
            ->value('end_date');

        return Carbon::parse($endDate ?? Carbon::today()->endOfYear())->startOfDay();
    }

    private static function weekOffTypeForDate(WeekOffChangeApplication $application, Carbon $date): int
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

        $detail = $application->WeekOffChangeDetails->first(
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
