<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\StatusMaster;
use Carbon\Carbon;
use RuntimeException;

class LeaveAttendanceWriter
{
    public function createForApprovedApplication(LeaveApplication $application): void
    {
        $application->loadMissing(['users', 'leave_type']);

        $leaveType = $application->leave_type;
        if ($leaveType === null) {
            throw new RuntimeException('The approved leave application has no leave type.');
        }

        $fromDate = Carbon::parse($application->from_date)->startOfDay();
        $toDate = Carbon::parse($application->to_date)->startOfDay();

        foreach ($application->users as $user) {
            for ($date = $fromDate->copy(); $date->lte($toDate); $date->addDay()) {
                $statusCode = $leaveType->code;

                if ($application->is_only_half
                    || ($date->isSameDay($fromDate) && $application->is_second_half)) {
                    $statusCode = 'A'.$statusCode;
                } elseif ($date->isSameDay($toDate) && $application->is_first_half) {
                    $statusCode .= 'A';
                }

                $status = StatusMaster::query()->where('code', $statusCode)->firstOrFail();

                $attendance = Attendance::year($date->format('Y'));
                $existingAttendance = $attendance->newQuery()
                    ->where('user_id', $user->id)
                    ->whereDate('date', $date->toDateString())
                    ->first();

                if ($existingAttendance === null) {
                    $attendance->newQuery()->create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'status_master_id' => $status->id,
                    ]);
                } else {
                    $existingAttendance->update(['status_master_id' => $status->id]);
                }
            }
        }
    }
}
