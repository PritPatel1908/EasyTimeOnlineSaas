<?php

namespace App\Jobs\Tenant;

use App\Attendance\AttCalculator;
use App\Models\Tenant\AttendanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPendingLogs implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $log;

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
        return $this->log->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(AttendanceLog $log, $returnLog = false)
    {
        $this->log = $log;
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
        $this->log->process_tags()->delete();
        $attCalculator = new AttCalculator($this->log, $this->returnLog);

        $attCalculator->debug('----------------------'.'ProcessStarted for #'.$this->log->id.'|'.$this->log->user_code.'|'.$this->log->datetime.'----------------------');
        if ($attCalculator->checkAlreadyCalculated()) {
            $attCalculator->debug('#'.$this->log->id.' is already calculated');
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        if (! $this->log->user_code) {
            $attCalculator->debug('#'.$this->log->id.' does not have valid user');
            $this->log->is_staged = true;
            $this->log->save();
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        if (! $this->log->user) {
            $attCalculator->debug('#'.$this->log->id.' does not have valid user');
            $this->log->is_staged = true;
            $this->log->save();
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }

        $this->log->user_id = $this->log->user->id;
        $this->log->save();

        if ($this->log->user->left_date != null && $this->log->datetime->gt($this->log->user->left_date)) {
            $this->log->process_tags()->create(['name' => 'User Left But Punched']);
        }

        if ($this->log->user->is_inactive) {
            $this->log->process_tags()->create(['name' => 'User Inactive But Punched']);
        }

        $attCalculator->setFyears();
        $area_found = $attCalculator->checkArea();
        if (! $area_found) {
            $this->log->process_tags()->create([
                'name' => 'Machine Or Area Not Found',
            ]);
            $attCalculator->debug('#'.$this->log->id.' does not have valid machine or area');
            $this->log->is_staged = true;
            $this->log->save();
            if ($this->returnLog) {
                $this->returningLogArray = $attCalculator->getReturnLogs();
            }

            return;
        }
        $attCalculator->retrivePreviousLogs();
        $attCalculator->checkIsDuplicatePunch();
        $attCalculator->checkInOrOut();
        $attCalculator->retriveAttendance();
        // if ($this->log->user->late_coming_rule_id != null) {
        $attCalculator->checkLateComingRule();
        // }
        // if ($this->log->user->early_going_rule_id != null) {
        $attCalculator->checkEarlyGoingRule();
        // }
        // if ($this->log->user->overtime_rule_id != null) {
        $attCalculator->checkOverTimeRule();
        // }
        // if ($this->log->user->half_day_rule_id != null) {
        $attCalculator->checkHalfDayRule();
        // }
        // if ($this->log->user->absent_rule_id != null) {
        $attCalculator->checkAbsentRule();
        // }
        $attCalculator->createOrUpdateInOutMuster();

        $this->log->update([
            'is_calculated' => true,
            'is_staged' => false,
        ]);

        $this->log->process_tags()->create([
            'name' => 'Processed',
        ]);

        $attCalculator->debug('----------------------'.'ProcessEnded for #'.$this->log->id.'|'.$this->log->user_code.'|'.$this->log->datetime.'| Calculated:'.$this->log->is_calculated.'----------------------');
        if ($this->returnLog) {
            $this->returningLogArray = $attCalculator->getReturnLogs();
        }
    }
}
