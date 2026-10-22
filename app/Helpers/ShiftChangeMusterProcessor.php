<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\RotationMuster;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\ShiftMuster;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class ShiftChangeMusterProcessor
{
    public static function process(ShiftChange|ShiftChangeApplication $shiftChange): void
    {
        DB::transaction(function () use ($shiftChange): void {
            $shiftChanges = $shiftChange instanceof ShiftChange
                ? self::orderedShiftChangesForUsers($shiftChange)
                : collect([$shiftChange]);

            foreach ($shiftChanges as $record) {
                self::processRecord($record);
            }
        });
    }

    /**
     * @return Collection<int, ShiftChange>
     */
    private static function orderedShiftChangesForUsers(ShiftChange $shiftChange): Collection
    {
        $userIds = $shiftChange->Users()->pluck('users.id');

        if ($userIds->isEmpty()) {
            return new Collection;
        }

        return ShiftChange::query()
            ->whereHas('Users', static fn (Builder $query) => $query->whereIn('users.id', $userIds))
            ->with(['Users' => static fn (BelongsToMany $query) => $query->whereIn('users.id', $userIds)])
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();
    }

    private static function processRecord(ShiftChange|ShiftChangeApplication $shiftChange): void
    {
            $users = $shiftChange->Users()->get();
            $userIds = $users->pluck('id')->all();
            $fromDate = $shiftChange->from_date->copy();

            if ($shiftChange->is_forever) {
                $financialYear = FinancialYear::query()
                    ->where('is_started', true)
                    ->orderByDesc('id')
                    ->first();
                $toDate = $financialYear?->end_date ?? Carbon::today()->endOfYear();
            } else {
                $toDate = $shiftChange->to_date;
            }

            $shiftIds = $shiftChange->shifts()
                ->pluck('shift_id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();
            $hasDateRange = $fromDate <= $toDate;

            while ($fromDate <= $toDate) {
                foreach ($userIds as $userId) {
                    $existingMuster = ShiftMuster::query()
                        ->whereDate('date', $fromDate)
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if ($existingMuster?->is_locked) {
                        continue;
                    }

                    $attributes = [
                        'is_auto' => $shiftChange->shift_type === 'auto',
                        'shift' => $shiftChange->shift_type === 'rotational'
                            ? RotationMuster::query()
                                ->where('rotation_id', $shiftChange->shift_rotation_id)
                                ->whereDate('date', $fromDate)
                                ->firstOrFail()
                                ->shift_id
                            : $shiftIds,
                        'shiftchangeable_type' => $shiftChange::class,
                        'shiftchangeable_id' => $shiftChange->id,
                    ];

                    if ($existingMuster === null) {
                        ShiftMuster::query()->create([
                            'date' => $fromDate,
                            'user_id' => $userId,
                            ...$attributes,
                        ]);
                    } else {
                        $existingMuster->update($attributes);
                    }
                }

                $fromDate->addDay();
            }

            if ($hasDateRange) {
                foreach ($users as $user) {
                    $user->shift_status = true;
                    $user->save();
                }
            }
    }
}
