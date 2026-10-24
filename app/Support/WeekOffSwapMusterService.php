<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\WOTypeEnumn;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffMuster;
use App\Models\Tenant\WeekOffSwap;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class WeekOffSwapMusterService
{
    /**
     * @param Collection<int, User> $users
     * @return Collection<int, User>
     */
    public static function usersWithoutWeekOff(
        Collection $users,
        string $weekDate,
    ): Collection {
        $userIds = $users->pluck('id')->map(static fn (mixed $id): int => (int) $id);
        if ($userIds->isEmpty()) {
            return collect();
        }

        $weekOffTypes = array_map(
            static fn (WOTypeEnumn $type): int => $type->value,
            WOTypeEnumn::cases(),
        );
        $usersWithWeekOff = DB::table('week_off_musters')
            ->whereDate('date', $weekDate)
            ->whereIn('user_id', $userIds)
            ->where('is_locked', false)
            ->whereIn('wo_type', $weekOffTypes)
            ->pluck('user_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        return $users->reject(
            static fn (User $user): bool => in_array((int) $user->id, $usersWithWeekOff, true)
        )->values();
    }

    public static function applySwap(WeekOffSwap $weekOffSwap): void
    {
        $weekOffTypes = array_map(
            static fn (WOTypeEnumn $type): int => $type->value,
            WOTypeEnumn::cases(),
        );

        DB::transaction(function () use ($weekOffSwap, $weekOffTypes): void {
            $weekDate = $weekOffSwap->week_date->toDateString();
            $swapDate = $weekOffSwap->swap_date->toDateString();
            $userIds = $weekOffSwap->Users()->pluck('user_id')->map(static fn ($id): int => (int) $id);
            $previouslyProcessedUserIds = WeekOffMuster::query()
                ->where('weekoffable_type', $weekOffSwap::class)
                ->where('weekoffable_id', $weekOffSwap->id)
                ->pluck('user_id')
                ->map(static fn ($id): int => (int) $id);
            $allUserIds = $userIds->merge($previouslyProcessedUserIds)->unique()->values();

            foreach ($allUserIds as $userId) {
                $linkedMusters = WeekOffMuster::query()
                    ->where('weekoffable_type', $weekOffSwap::class)
                    ->where('weekoffable_id', $weekOffSwap->id)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->get();
                $previousDestination = $linkedMusters->first(
                    static fn (WeekOffMuster $muster): bool => in_array((int) $muster->wo_type, $weekOffTypes, true)
                );
                $previousSource = $linkedMusters->first(
                    static fn (WeekOffMuster $muster): bool => (int) $muster->wo_type === 0
                );
                $sourceTypeOverride = null;

                if ($previousSource !== null && $previousDestination !== null) {
                    $previousSource->is_locked = false;
                    $previousSource->weekoffable_type = null;
                    $previousSource->weekoffable_id = null;
                    $previousSource->save();

                    $weekDateIsPreviousDestination = $previousDestination->date?->toDateString() === $weekDate;
                    if ($weekDateIsPreviousDestination) {
                        $sourceTypeOverride = $previousDestination->wo_type;
                    } else {
                        $previousSource->wo_type = $previousDestination->wo_type;
                        $previousSource->save();
                    }

                    $previousDestination->wo_type = 0;
                    $previousDestination->is_locked = false;
                    $previousDestination->weekoffable_type = null;
                    $previousDestination->weekoffable_id = null;
                    $previousDestination->save();
                }

                if (! $userIds->contains($userId) || $weekDate === $swapDate) {
                    continue;
                }

                $weekOffMuster = WeekOffMuster::query()
                    ->whereDate('date', $weekDate)
                    ->where('user_id', $userId)
                    ->where('is_locked', false)
                    ->lockForUpdate()
                    ->first();
                $swapDateMuster = WeekOffMuster::query()
                    ->whereDate('date', $swapDate)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($weekOffMuster !== null && $sourceTypeOverride !== null) {
                    $weekOffMuster->wo_type = $sourceTypeOverride;
                }

                if (
                    $weekOffMuster === null
                    || $swapDateMuster === null
                    || (bool) $swapDateMuster->is_locked
                    || ! in_array((int) $weekOffMuster->wo_type, $weekOffTypes, true)
                ) {
                    continue;
                }

                $originalWeekOffType = $weekOffMuster->wo_type;
                $weekOffMuster->weekoffable_type = $weekOffSwap::class;
                $weekOffMuster->weekoffable_id = $weekOffSwap->id;
                $weekOffMuster->wo_type = 0;
                $weekOffMuster->save();

                $swapDateMuster->weekoffable_type = $weekOffSwap::class;
                $swapDateMuster->weekoffable_id = $weekOffSwap->id;
                $swapDateMuster->wo_type = $originalWeekOffType;
                $swapDateMuster->is_locked = false;
                $swapDateMuster->save();
            }
        });
    }
}
