<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\InOutMuster;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffMuster;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CloseUseFinancialYearMusters implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record;

    protected $authUser;

    /**
     * Create a new job instance.
     */
    public function __construct($record, $authuser)
    {
        $this->record = $record;
        $this->authUser = $authuser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userIds = User::where('id', '!=', 1)
            ->where('left_date', null)
            ->where('is_inactive', false)
            ->pluck('id')
            ->toArray();

        $startDate = $this->record->start_date;
        $endDate = $this->record->end_date;

        AttendanceLog::whereIn('user_id', $userIds)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->update(['is_locked' => true]);

        Log::info('Attendance logs locked for financial year', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_count' => count($userIds),
        ]);

        Attendance::whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->update(['is_locked' => true]);

        Log::info('Attendance locked for financial year', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_count' => count($userIds),
        ]);

        InOutMuster::whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->update(['is_locked' => true]);

        Log::info('IOut muster locked for financial year', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_count' => count($userIds),
        ]);

        ShiftMuster::whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->update(['is_locked' => true]);

        Log::info('Shift muster locked for financial year', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_count' => count($userIds),
        ]);

        StatusMuster::whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->update(['is_locked' => true]);

        Log::info('Status muster locked for financial year', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_count' => count($userIds),
        ]);

        WeekOffMuster::whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->update(['is_locked' => true]);

        Log::info('Week off muster locked for financial year', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_count' => count($userIds),
        ]);
    }
}
