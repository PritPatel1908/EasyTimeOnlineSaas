<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\StatusMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoffAttendanceWriter
{
    public function createForApprovedApplication(CoffApplication $application): void
    {
        $application->loadMissing('user');
        $user = $application->user;
        if ($user === null) {
            throw new RuntimeException('The COFF application has no employee.');
        }

        $fromDate = Carbon::parse($application->coff_from_date)->startOfDay();
        $toDate = Carbon::parse($application->coff_to_date)->startOfDay();
        $statuses = StatusMaster::query()
            ->whereIn('code', ['CO', 'COA', 'ACO'])
            ->get()
            ->keyBy('code');

        foreach (['CO', 'COA', 'ACO'] as $code) {
            if (! $statuses->has($code)) {
                throw new RuntimeException("The COFF attendance status {$code} is not configured.");
            }
        }

        DB::transaction(function () use ($application, $fromDate, $toDate, $statuses, $user): void {
            for ($date = $fromDate->copy(); $date->lte($toDate); $date->addDay()) {
                $statusCode = $this->statusCodeForDate($application, $date, $fromDate, $toDate);
                $attendanceQuery = Attendance::year($date->year)->newQuery();
                $attendance = $attendanceQuery
                    ->where('user_id', $user->id)
                    ->whereDate('date', $date->toDateString())
                    ->lockForUpdate()
                    ->first();

                if ($attendance !== null && $attendance->is_locked) {
                    throw new RuntimeException(
                        'Cannot apply the approved COFF to locked attendance for '.$date->toDateString().'.',
                    );
                }

                $attributes = [
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'status_master_id' => $statuses->get($statusCode)->id,
                    'is_half_day' => $statusCode !== 'CO',
                    'remarks' => 'CO Against '.$application->coff_against_date->format('Y-m-d'),
                ];

                if ($attendance === null) {
                    $attendanceQuery->create($attributes);
                } else {
                    $attendance->update($attributes);
                }
            }
        });
    }

    private function statusCodeForDate(
        CoffApplication $application,
        Carbon $date,
        Carbon $fromDate,
        Carbon $toDate,
    ): string {
        if ($application->is_half_day) {
            return $application->is_second_half ? 'ACO' : 'COA';
        }

        if ($date->isSameDay($fromDate) && $application->is_second_half) {
            return 'ACO';
        }

        if ($date->isSameDay($toDate) && $application->is_first_half) {
            return 'COA';
        }

        return 'CO';
    }

}
