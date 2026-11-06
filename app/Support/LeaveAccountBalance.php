<?php

namespace App\Support;

use Illuminate\Support\Collection;

class LeaveAccountBalance
{
    public static function forLeaveType(iterable $details, int $leaveTypeId): float
    {
        return collect($details)
            ->where('leave_type_id', $leaveTypeId)
            ->sort(static function ($left, $right): int {
                $yearComparison = (int) ($left->transaction_year ?? 0)
                    <=> (int) ($right->transaction_year ?? 0);
                if ($yearComparison !== 0) {
                    return $yearComparison;
                }

                $transactionComparison = (int) ($left->transaction_id ?? 0)
                    <=> (int) ($right->transaction_id ?? 0);
                if ($transactionComparison !== 0) {
                    return $transactionComparison;
                }

                return (int) $left->id <=> (int) $right->id;
            })
            ->last()
            ?->balance ?? 0;
    }

    /**
     * @return array<string, float>
     */
    public static function all(iterable $details): array
    {
        $detailsByLeaveType = collect($details)->groupBy('leave_type_id');

        return $detailsByLeaveType->mapWithKeys(static function (Collection $leaveTypeDetails, $leaveTypeId): array {
            return [(string) $leaveTypeId => self::forLeaveType($leaveTypeDetails, (int) $leaveTypeId)];
        })->all();
    }
}
