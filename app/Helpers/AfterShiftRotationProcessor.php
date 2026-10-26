<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\ShiftRotation;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AfterShiftRotationProcessor
{
    private const INSERT_CHUNK_SIZE = 400;

    public static function processAfterSave(int $shiftRotationId, ?CarbonInterface $throughDate = null): void
    {
        $shiftRotation = ShiftRotation::query()
            ->with(['rotation_values' => static fn ($query) => $query->orderBy('id')])
            ->findOrFail($shiftRotationId);

        if ($shiftRotation->start_date === null) {
            throw new RuntimeException("Shift rotation {$shiftRotation->id} must have a start date before generating musters.");
        }

        if ($shiftRotation->is_forever) {
            $endDate = FinancialYear::query()
                ->where('is_started', true)
                ->orderByDesc('id')
                ->value('end_date')
                ?? CarbonImmutable::today()->endOfYear()->toDateString();
        } else {
            $endDate = $shiftRotation->end_date;
        }

        $startDate = CarbonImmutable::parse($shiftRotation->start_date)->startOfDay();
        $endDate = CarbonImmutable::parse($endDate)->startOfDay();
        if ($throughDate !== null) {
            $requestedEndDate = CarbonImmutable::parse($throughDate)->startOfDay();
            if ($requestedEndDate->gt($endDate)) {
                $endDate = $requestedEndDate;
            }
        }

        if ($endDate->lt($startDate)) {
            throw new RuntimeException("Shift rotation {$shiftRotation->id} ends before it starts.");
        }

        $rotationValues = $shiftRotation->rotation_values;
        if ($rotationValues->isEmpty()) {
            throw new RuntimeException("Shift rotation {$shiftRotation->id} must have at least one shift step before generating musters.");
        }

        foreach ($rotationValues as $rotationValue) {
            if ($rotationValue->days < 1) {
                throw new RuntimeException("Shift rotation {$shiftRotation->id} contains a rotation step with an invalid day count.");
            }
        }

        DB::transaction(function () use ($shiftRotation, $rotationValues, $startDate, $endDate): void {
            DB::table('rotation_musters')
                ->where('rotation_id', $shiftRotation->id)
                ->delete();

            $rows = [];
            $currentDate = $startDate;
            $rotationIndex = 0;
            $previousIndex = 0;
            $timestamp = now();

            while ($currentDate->lte($endDate)) {
                $scheduledIndex = $rotationIndex;
                $rotationValue = $rotationValues[$scheduledIndex];
                $blockDays = (int) $rotationValue->days;
                $daysToCalendarBoundary = self::daysToCalendarBoundary(
                    $currentDate,
                    $shiftRotation->skip_days_on_month_end,
                    $shiftRotation->skip_days_on_year_end,
                );

                $assignmentIndex = $scheduledIndex;
                if ($daysToCalendarBoundary !== null
                    && $blockDays > $daysToCalendarBoundary
                    && $daysToCalendarBoundary < ($blockDays / 2)) {
                    $assignmentIndex = $previousIndex;
                    $blockDays = $daysToCalendarBoundary;
                    $rotationIndex = ($assignmentIndex + 1) % $rotationValues->count();
                } else {
                    $blockDays = min($blockDays, $daysToCalendarBoundary ?? $blockDays);
                    $rotationIndex = ($scheduledIndex + 1) % $rotationValues->count();
                }

                $shiftId = $rotationValues[$assignmentIndex]->shift_id;
                $blockEndDate = $currentDate->addDays($blockDays - 1);
                if ($blockEndDate->gt($endDate)) {
                    $blockEndDate = $endDate;
                }

                for ($date = $currentDate; $date->lte($blockEndDate); $date = $date->addDay()) {
                    $rows[] = [
                        'rotation_id' => $shiftRotation->id,
                        'date' => $date->toDateString(),
                        'shift_id' => $shiftId,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];

                    if (count($rows) === self::INSERT_CHUNK_SIZE) {
                        DB::table('rotation_musters')->insert($rows);
                        $rows = [];
                    }
                }

                $currentDate = $blockEndDate->addDay();
                $previousIndex = $assignmentIndex;
            }

            if ($rows !== []) {
                DB::table('rotation_musters')->insert($rows);
            }

            $shiftRotation->forceFill(['shift_status' => true])->save();
        });
    }

    public static function daysToCalendarBoundary(
        CarbonImmutable $date,
        bool $skipMonthEnd,
        bool $skipYearEnd,
    ): ?int {
        $boundaries = [];

        if ($skipMonthEnd) {
            $boundaries[] = $date->endOfMonth()->startOfDay();
        }

        if ($skipYearEnd) {
            $boundaries[] = $date->endOfYear()->startOfDay();
        }

        if ($boundaries === []) {
            return null;
        }

        $boundary = collect($boundaries)->sortBy(static fn (CarbonImmutable $value) => $value->getTimestamp())->first();

        return (int) $date->diffInDays($boundary) + 1;
    }
}
