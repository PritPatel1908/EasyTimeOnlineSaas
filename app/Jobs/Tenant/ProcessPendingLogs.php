<?php

namespace App\Jobs\Tenant;

use App\Attendance\AttCalculator;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ShortLeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessPendingLogs implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $logId;

    protected $returnLog;

    protected $returningLogArray = [];

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return (string) $this->logId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(AttendanceLog|int $log, $returnLog = false)
    {
        $this->logId = $log instanceof AttendanceLog ? (int) $log->id : $log;
        $this->returnLog = $returnLog;
    }

    public function getReturnLogs()
    {
        return $this->returningLogArray;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = AttendanceLog::query()->findOrFail($this->logId);
        $attCalculator = new AttCalculator($log, $this->returnLog);

        $attCalculator->debug('----------------------'.'ProcessStarted for #'.$log->id.'|'.$log->user_code.'|'.$log->datetime.'----------------------');
        if ($log->status === 0) {
            $attCalculator->debug('Skipping attendance log because its status is 0');
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        $log->process_tags()->delete();
        if ($attCalculator->checkAlreadyCalculated()) {
            $attCalculator->debug('#'.$log->id.' is already calculated');
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        if (! $log->user_code) {
            $attCalculator->debug('#'.$log->id.' does not found user code');
            $log->is_staged = true;
            $log->save();
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        if (! $log->user) {
            $attCalculator->debug('#'.$log->id.' does not found user');
            $log->is_staged = true;
            $log->save();
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        $log->user_id = $log->user->id;
        $log->save();

        if ($log->user->left_date != null && $log->datetime->gt($log->user->left_date)) {
            $log->process_tags()->create(['name' => 'User Left But Punched']);
        }

        if ($log->user->is_inactive) {
            $log->process_tags()->create(['name' => 'User Inactive But Punched']);
        }

        $attCalculator->setFyears();
        $area_found = $log->is_manual || $attCalculator->checkArea();
        if (! $area_found) {
            $log->process_tags()->create([
                'name' => 'Machine Or Area Not Found',
            ]);
            $attCalculator->debug('#'.$log->id.' does not have valid machine or area');
            $log->is_staged = true;
            $log->save();
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }
        $attCalculator->retrivePreviousLogs();
        $attCalculator->checkIsDuplicatePunch();
        $attCalculator->checkInOrOut();
        if ($log->is_ignored) {
            $attCalculator->debug('Punch ignored by configured gap rules');
            $log->update([
                'is_calculated' => true,
                'is_staged' => false,
            ]);
            $log->process_tags()->create([
                'name' => 'Processed',
            ]);
            $this->dispatchChronologicalReprocessingIfNeeded($log);
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        $attCalculator->retriveAssignedShift();
        if ($attCalculator->shift === null) {
            $attCalculator->debug('No shift matched the user shift muster; skipping punch processing');
            $attCalculator->resetCalculatedShiftMuster();
            $log->is_staged = true;
            $log->save();
            $log->process_tags()->create([
                'name' => 'No Assigned Shift',
                'color' => 'danger',
            ]);
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        $log->save();
        $attCalculator->retriveAttendance();
        $attCalculator->createOrUpdateInOutMuster();
        $attCalculator->syncAttendanceWorkTime();
        // if ($log->user->late_coming_rule_id != null) {
        $attCalculator->checkLateComingRule();
        // }
        // if ($log->user->early_going_rule_id != null) {
        $attCalculator->checkEarlyGoingRule();
        // }
        // if ($log->user->overtime_rule_id != null) {
        $attCalculator->checkOverTimeRule();
        // }
        // if ($log->user->half_day_rule_id != null) {
        $attCalculator->checkHalfDayRule();
        // }
        // if ($log->user->absent_rule_id != null) {
        $attCalculator->checkAbsentRule();
        // }

        $log->update([
            'is_calculated' => true,
            'is_staged' => false,
        ]);

        $log->process_tags()->create([
            'name' => 'Processed',
        ]);

        $attCalculator->debug('----------------------'.'ProcessEnded for #'.$log->id.'|'.$log->user_code.'|'.$log->datetime.'| Calculated:'.$log->is_calculated.'----------------------');
        $this->dispatchChronologicalReprocessingIfNeeded($log);
        $this->dispatchApprovedShortLeavePunches($log);
        if ($this->returnLog) {
            $this->returningLogArray = $attCalculator->getReturnLogs();
        }
    }

    private function dispatchApprovedShortLeavePunches(AttendanceLog $log): void
    {
        if ($log->user_id === null || $log->datetime === null) {
            return;
        }

        $punchDate = Carbon::parse($log->datetime);
        $applicationIds = ShortLeaveApplication::query()
            ->applicableToAttendance()
            ->whereHas(
                'approval_statuses',
                static fn ($query) => $query->where('approval_status', 'approved'),
            )
            ->where('user_id', $log->user_id)
            ->whereIn('date', [
                $punchDate->copy()->subDay()->toDateString(),
                $punchDate->toDateString(),
                $punchDate->copy()->addDay()->toDateString(),
            ])
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id);

        if ($applicationIds->isEmpty()) {
            return;
        }

        $generatedPunchReasons = $applicationIds->mapWithKeys(
            static fn (int $applicationId): array => [
                $applicationId => 'Approved short leave application #'.$applicationId,
            ],
        );
        $alreadyAppliedIds = ManualPunch::query()
            ->whereIn('reason', $generatedPunchReasons->values())
            ->whereHas('Users', static fn ($query) => $query->whereKey($log->user_id))
            ->pluck('reason')
            ->map(static function (string $reason) use ($generatedPunchReasons): ?int {
                $applicationId = array_search($reason, $generatedPunchReasons->all(), true);

                return $applicationId === false ? null : (int) $applicationId;
            })
            ->filter();

        foreach ($applicationIds->diff($alreadyAppliedIds) as $applicationId) {
            ApplyApprovedShortLeavePunches::dispatch((int) $applicationId)
                ->onConnection('database_tenant')
                ->onQueue('processing');
        }
    }

    private function dispatchChronologicalReprocessingIfNeeded(AttendanceLog $log): void
    {
        if ($log->user_id === null || $log->datetime === null) {
            return;
        }

        $latestLaterPunch = AttendanceLog::query()
            ->where('user_id', $log->user_id)
            ->where('is_calculated', true)
            ->where('is_locked', false)
            ->where('status', '!=', 0)
            ->where(function ($query) use ($log): void {
                $query->where('datetime', '>', $log->datetime)
                    ->orWhere(function ($query) use ($log): void {
                        $query->where('datetime', '=', $log->datetime)
                            ->where('id', '>', $log->id);
                    });
            })
            ->orderByDesc('datetime')
            ->orderByDesc('id')
            ->value('datetime');

        if ($latestLaterPunch === null) {
            return;
        }

        $reprocessStart = $log->datetime->copy()->subDay()->startOfDay();
        $reprocessEnd = Carbon::parse($latestLaterPunch);
        $supportedWindowEnd = $log->datetime->copy()->addDays(35);
        if ($reprocessEnd->greaterThan($supportedWindowEnd)) {
            $reprocessEnd = $supportedWindowEnd;
        }

        ReprocessAttendanceLogs::dispatch(
            $reprocessStart->toDateTimeString(),
            $reprocessEnd->toDateTimeString(),
            [(int) $log->user_id],
        );
    }
}
