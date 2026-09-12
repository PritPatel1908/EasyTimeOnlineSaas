<?php

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Coff;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApproveUserManualAttendance implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $manualAttendance;

    protected $user;

    public $shift = null;

    public $curatt;

    public $halfdayRuleLateFlag = false;

    public $halfdayRuleEarlyFlag = false;

    public $halfdayRuleWorkHourFlag = false;

    public $absentRuleLateFlag = false;

    public $absentRuleEarlyFlag = false;

    public $absentRuleWorkHourFlag = false;

    public $overtime_early = null;

    public $overtime_late = null;

    public $otFlag = false;

    /**
     * Create a new job instance.
     */
    public function __construct($manualAttendance)
    {
        $this->manualAttendance = $manualAttendance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->manualAttendance->Users as $user) {
            $this->user = null;
            $this->user = $user;
            $this->retriveAssignedShift();
            $this->createAttendance();
            $this->assignStatus();
            // if ($this->user->late_coming_rule_id != null) {
            $this->checkLateComingRule();
            // }
            // if ($this->user->early_going_rule_id != null) {
            $this->checkEarlyGoingRule();
            // }
            // if ($this->user->overtime_rule_id != null) {
            $this->checkOverTimeRule();
            // }
            // if ($this->user->half_day_rule_id != null) {
            $this->checkHalfDayRule();
            // }
            // if ($this->user->absent_rule_id != null) {
            $this->checkAbsentRule();
            // }
        }
    }

    public function debug($message)
    {
        Log::channel('attprocess')->debug('#'.$this->manualAttendance->id.' '.$message);
    }

    public function retriveAssignedShift(): void
    {
        $this->debug('~~~Retriving assigned shift');
        if ($this->manualAttendance->shift_type == 'auto') {
            $this->debug('Shift type is auto');
            $shift = $this->manualAttendance->shift->first();
            $shift->auto_from = $this->manualAttendance->in_time->copy()->setTimeFromTimeString($shift->auto_from->toTimeString());
            $shift->auto_to = $this->manualAttendance->in_time->copy()->setTimeFromTimeString($shift->auto_to->toTimeString());
            if ($shift->auto_from > $shift->auto_to) {
                if ($this->manualAttendance->in_time->between($shift->auto_from, $shift->auto_to->copy()->addDay())) {
                    $this->debug('Auto Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                    $this->shift = $shift;
                } elseif ($this->manualAttendance->in_time->between($att_day = $shift->auto_from->copy()->subDay(), $shift->auto_to)) {
                    $this->debug('Auto Previous Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                    $this->shift = $shift;
                }
            } elseif ($this->manualAttendance->in_time->between($shift->auto_from, $shift->auto_to)) {
                $this->debug('Auto Shift Assigned : '.$shift->id.'|'.$shift->code);
                $this->shift = $shift;
            }
            $this->debug('Shift #'.$shift->code.' is not matched');
            $this->debug('BOOL'.$this->manualAttendance->in_time.'|'.$shift->auto_from.'|'.$shift->auto_to);
        } elseif ($this->manualAttendance->shift_type == 'rotational') {
            $this->debug('Shift type is rotational');
            $shift = $this->manualAttendance->shift_rotation->first();

            $shift->auto_from = $this->manualAttendance->in_time->copy()->setTimeFromTimeString($shift->auto_from->toTimeString());
            $shift->auto_to = $this->manualAttendance->in_time->copy()->setTimeFromTimeString($shift->auto_to->toTimeString());
            if ($shift->auto_from > $shift->auto_to) {
                if ($this->manualAttendance->in_time->between($shift->auto_from, $shift->auto_to->copy()->addDay())) {
                    $this->debug('Rotational Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                    $this->shift = $shift;
                } elseif ($this->manualAttendance->in_time->between($att_day = $shift->auto_from->copy()->subDay(), $shift->auto_to)) {
                    $this->debug('Rotational Previous Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                    $this->shift = $shift;
                }
            } elseif ($this->manualAttendance->in_time->between($shift->auto_from, $shift->auto_to)) {
                $this->debug('Rotational Shift Assigned : '.$shift->id.'|'.$shift->code);
                $this->shift = $shift;
            }
            $this->debug('Shift #'.$shift->code.' is not matched');
            $this->debug('BOOL'.$this->manualAttendance->in_time.'|'.$shift->auto_from.'|'.$shift->auto_to);
        } else {
            $this->debug('Fixed Shift : '.$this->manualAttendance->shift?->code);
            $this->shift = $this->manualAttendance->shift;
        }

        if ($this->shift == null) {
            // TODO: assign default shift from general configuration
            $this->debug('No shift found for user assigning default shift');
            $this->shift = Shift::first();
        }

        $shift_muster = ShiftMuster::where('user_id', $this->user->id)->where('is_locked', false)->where('date', $this->manualAttendance->in_time->copy()->format('Y-m-d'))->first();

        if ($shift_muster) {
            $shift_muster->calculated_shift = $this->shift->id;
            $shift_muster->is_calculated = true;
            $shift_muster->save();
        }
    }

    public function createAttendance(): void
    {
        $this->curatt = Attendance::year($this->manualAttendance->in_time->year)->updateOrCreate([
            'user_id' => $this->user->id,
            'date' => $this->manualAttendance->in_time->copy()->format('Y-m-d'),
            'is_locked' => false,
        ], [
            'location_id' => $this->manualAttendance->location_id,
            'company_id' => $this->manualAttendance->company_id,
            'department_id' => $this->user->department_id,
            'sub_department_id' => $this->user->sub_department_id,
            'category_id' => $this->user->category_id,
            'sub_category_id' => $this->user->sub_category_id,
            'in_time' => $this->manualAttendance->in_time,
            'out_time' => $this->manualAttendance->out_time,
            'shift_id' => $this->shift->id,
            'area_id' => $this->user->areas->first()->id,
            'shift_code' => $this->shift->code,
            'shift_in_time' => $this->shift->in_time,
            'shift_out_time' => $this->shift->out_time,
            'is_manual' => true,
        ]);
    }

    public function assignStatus(): void
    {
        if ($this->user->category->single_punch_allowed_present && $this->curatt->in_time) {
            $this->debug('Single Punch Allowed');
            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
            $this->curatt->save();

            StatusMuster::where('user_id', $this->user->id)
                ->where('date', $this->manualAttendance->in_time->copy()->format('Y-m-d'))
                ->where('is_locked', false)
                ->update([
                    'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                    'day_count' => 1.0,
                ]);
        } else {
            if ($this->curatt->in_time && $this->curatt->out_time == null) {
                $this->debug('User in time get and out time null');
                $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                $this->curatt->save();

                StatusMuster::where('user_id', $this->user->id)
                    ->where('is_locked', false)
                    ->where('date', $this->manualAttendance->in_time->copy()->format('Y-m-d'))
                    ->update([
                        'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                        'day_count' => 0.5,
                    ]);
            } elseif ($this->curatt->in_time == null && $this->curatt->out_time) {
                $this->debug('User in time null and out time get');
                $this->curatt->status_master_id = StatusMaster::where('code', 'AP')->first()->id;
                $this->curatt->save();

                StatusMuster::where('user_id', $this->user->id)
                    ->where('is_locked', false)
                    ->where('date', $this->manualAttendance->in_time->copy()->format('Y-m-d'))
                    ->update([
                        'status_master_id' => StatusMaster::where('code', 'AP')->first()->id,
                        'day_count' => 0.5,
                    ]);
            } elseif ($this->curatt->in_time && $this->curatt->out_time) {
                $this->debug('User in time get and out time get');
                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                $this->curatt->save();

                StatusMuster::where('user_id', $this->user->id)
                    ->where('date', $this->manualAttendance->in_time->copy()->format('Y-m-d'))
                    ->where('is_locked', false)
                    ->update([
                        'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                        'day_count' => 1.0,
                    ]);
            } else {
                $this->debug('User in time and out time null');
                $this->curatt->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                $this->curatt->save();

                StatusMuster::where('user_id', $this->user->id)
                    ->where('date', $this->manualAttendance->in_time->copy()->format('Y-m-d'))
                    ->where('is_locked', false)
                    ->update([
                        'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                        'day_count' => 0.0,
                    ]);
            }
        }
    }

    public function checkHalfDayRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        $coff = Coff::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        if ($leave_application == null || $coff == null) {
            if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                $this->debug('~~~Checking HalfDay Rule');
                if ($this->user->half_day_rule && $this->shift && $this->curatt && $this->manualAttendance && $this->manualAttendance->out_time && $this->manualAttendance->in_time) {
                    $halfdayRule = $this->user?->half_day_rule;

                    $punchInTime = Carbon::parse($this->manualAttendance->in_time);
                    $punchOutTime = Carbon::parse($this->manualAttendance->out_time);

                    $differenceInMinutes = $this->shift->in_time->diffInMinutes($punchInTime);
                    $differenceOutMinutes = $punchOutTime->diffInMinutes($this->shift->out_time);

                    $work_difference = $punchInTime->diffInMinutes($punchOutTime);
                    // 1. Check is allow single punch
                    if ($halfdayRule->single_punch_allowed && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                        $this->debug('Single Punch Allowed');

                        $this->curatt->is_half_day = false;
                        $this->curatt->save();

                        $processTags = $this->curatt->process_tags();
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                        $this->curatt->save();

                        StatusMuster::where('user_id', $this->user->id)
                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                            ->update([
                                'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                'day_count' => 1.0,
                            ]);

                        $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                    } else {
                        // 2. Check punch in time and out time
                        if (($punchInTime && $punchInTime != null) && ($punchOutTime && $punchOutTime != null)) {
                            $this->curatt->is_half_day = false;
                            $this->curatt->save();

                            $processTags = $this->curatt->process_tags();
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->save();

                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))->first();
                            if ($statusMuster) {
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();
                            }

                            $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();

                            // 3. Check work hours with user work time
                            if ($work_difference < $halfdayRule->work_hr_less_than_minutes && $halfdayRule->work_hr_less_than_minutes > 0) {
                                $this->debug('Check User Work Time To Work Hours Less Than Time');

                                $this->halfdayRuleWorkHourFlag = true;
                                $this->curatt->is_half_day = true;
                                $this->curatt->save();

                                // 3.1 Check user is first halft | second half
                                if ($punchInTime->format('H:i:s') > $this->curatt->shift_in_time->format('H:i:s') && $punchOutTime->format('H:i:s') < $this->curatt->shift->first_half_end_time->format('H:i:s')) {
                                    $processTags = $this->curatt->process_tags();
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                            'day_count' => 0.5,
                                        ]);

                                    $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                    $processTags->create(['name' => 'Half Day | Is First Half', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    $processTags = $this->curatt->process_tags();
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'AP')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'AP')->first()->id,
                                            'day_count' => 0.5,
                                        ]);

                                    $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                    $processTags->create(['name' => 'Half Day | Is Second Half', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                }
                            } else {
                                $this->halfdayRuleWorkHourFlag = false;
                                $this->debug('User Work Time Not Less Than To Work Hours Less Than Time');
                            }

                            // 4. Check user is late | early minutes and no of late | early to compare
                            if ($differenceInMinutes > $halfdayRule->late_coming_minutes && $halfdayRule->late_coming_minutes > 0) {
                                $today = Carbon::today();

                                $lateDateToCheck = $today->copy()->subDays($halfdayRule->consecutive_late_coming - 1);

                                $att_of_late_with_days = Attendance::where('user_id', $this->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$lateDateToCheck, $today])
                                    ->count();

                                $att_of_late = Attendance::where('user_id', $this->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_late_with_days >= $halfdayRule->consecutive_late_coming && $halfdayRule->consecutive_late_coming > 0) {
                                    if ($halfdayRule->ignore_month_end_late && $this->isMonthEnd($this->manualAttendance->in_time->format('Y-m-d'))) {
                                        $this->debug('User has exceeded the consecutive late coming limit but is not marked as HalfDay due to ignore month end late');
                                    } else {
                                        $this->debug('User has exceeded the consecutive late coming');
                                        $this->halfdayRuleLateFlag = true;
                                    }
                                } elseif ($att_of_late > $halfdayRule->no_of_late && $halfdayRule->no_of_late > 0) {
                                    $this->debug('User has exceeded the no late limit');
                                    $this->halfdayRuleLateFlag = true;
                                } else {
                                    $this->halfdayRuleLateFlag = false;
                                    $this->debug('User has not exceeded his limit');
                                }

                                if ($this->halfdayRuleLateFlag) {
                                    $this->curatt->is_half_day = true;
                                    $this->curatt->save();

                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                            'day_count' => 0.5,
                                        ]);

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                    $processTags->create(['name' => 'Half Day', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                }
                            } else {
                                $this->debug('Half Day Rule is not calculate | User not cross the limit of late coming');
                            }

                            if ($differenceOutMinutes > $halfdayRule->early_going_minutes && $halfdayRule->early_going_minutes > 0) {
                                $today = Carbon::today();

                                $earlyDateToCheck = $today->copy()->subDays($halfdayRule->consecutive_early_going - 1);

                                $att_of_early_with_days = Attendance::where('user_id', $this->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$earlyDateToCheck, $today])
                                    ->count();

                                $att_of_early = Attendance::where('user_id', $this->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_early_with_days >= $halfdayRule->consecutive_early_going && $halfdayRule->consecutive_early_going > 0) {
                                    if ($halfdayRule->ignore_month_end_early && $this->isMonthEnd($this->manualAttendance->in_time->format('Y-m-d'))) {
                                        $this->debug('User has exceeded the consecutive early going but is not marked as HalfDay due to ignore month end late');
                                    } else {
                                        $this->debug('User has exceeded the consecutive early going limit');
                                        $this->halfdayRuleEarlyFlag = true;
                                    }
                                } elseif ($att_of_early > $halfdayRule->no_of_early && $halfdayRule->no_of_early > 0) {
                                    $this->debug('User has exceeded the no early limit');
                                    $this->halfdayRuleEarlyFlag = true;
                                } else {
                                    $this->debug('User has not exceeded his limit');
                                    $this->halfdayRuleEarlyFlag = false;
                                }

                                if ($this->halfdayRuleEarlyFlag) {
                                    $this->curatt->is_half_day = true;
                                    $this->curatt->save();

                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                            'day_count' => 0.5,
                                        ]);

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                    $processTags->create(['name' => 'Half Day', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                }
                            } else {
                                $this->debug('Half Day Rule is not calculate | User not cross the limit of early going');
                            }

                            if ($this->halfdayRuleWorkHourFlag == false && $halfdayRule->ignore_month_end_late == false && $this->halfdayRuleLateFlag == false && $halfdayRule->ignore_month_end_early == false && $this->halfdayRuleEarlyFlag == false) {
                                if ($this->curatt->is_half_day = true) {
                                    $this->curatt->is_half_day = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                }
                            }
                        } else {
                            $this->debug('Not have user punch in time | out time');
                            $this->curatt->is_half_day = true;
                            $this->curatt->save();

                            $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->user->id)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                ->where('is_locked', false)
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                    'day_count' => 0.5,
                                ]);

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                            $processTags->create(['name' => 'Half Day', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                        }
                    }
                } else {
                    if ($this->curatt->shift && $this->curatt) {
                        $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                        $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                        $differenceInMinutes = $punchInTime ? $this->curatt->shift_in_time->diffInMinutes($punchInTime) : 0;
                        $differenceOutMinutes = $punchOutTime ? $punchOutTime->diffInMinutes($this->curatt->shift_out_time) : 0;

                        $work_difference = $punchInTime->diffInMinutes($punchOutTime);

                        $shift_hrs = $this->curatt->shift_in_time->diffInMinutes($this->curatt->shift_out_time);
                        $shift_half_hrs = $shift_hrs ? $shift_hrs / 2 : 0;

                        // 1. Check is allow single punch
                        if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                            $this->debug('Single Punch Allowed');

                            $this->curatt->is_half_day = false;
                            $this->curatt->save();

                            $processTags = $this->curatt->process_tags();
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                    'day_count' => 1.0,
                                ]);

                            $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                        } else {
                            // 2. Check punch in time and out time
                            if (($punchInTime && $punchInTime != null) && ($punchOutTime && $punchOutTime != null)) {
                                $this->curatt->is_half_day = false;
                                $this->curatt->save();

                                $processTags = $this->curatt->process_tags();
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();

                                // 3. Check work hours with user work time
                                if ($work_difference < $shift_half_hrs && $shift_half_hrs > 0) {
                                    $this->debug('Check User Work Time To Work Hours Less Than Time');

                                    $this->halfdayRuleWorkHourFlag = true;
                                    $this->curatt->is_half_day = true;
                                    $this->curatt->save();

                                    // 3.1 Check user is first halft | second half
                                    if ($punchInTime->format('H:i:s') > $this->curatt->shift_in_time->format('H:i:s') && $punchOutTime->format('H:i:s') < $this->curatt->shift->first_half_end_time->format('H:i:s')) {
                                        $processTags = $this->curatt->process_tags();
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                        $this->curatt->save();

                                        StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                            ->update([
                                                'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                                'day_count' => 0.5,
                                            ]);

                                        $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                        $processTags->create(['name' => 'Half Day | Is First Half', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        $processTags = $this->curatt->process_tags();
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'AP')->first()->id;
                                        $this->curatt->save();

                                        StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                            ->update([
                                                'status_master_id' => StatusMaster::where('code', 'AP')->first()->id,
                                                'day_count' => 0.5,
                                            ]);

                                        $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                        $processTags->create(['name' => 'Half Day | Is Second Half', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    }
                                } else {
                                    $this->halfdayRuleWorkHourFlag = false;
                                    $this->debug('User Work Time Not Less Than To Work Hours Less Than Time');
                                }

                                if ($this->halfdayRuleWorkHourFlag == false && $this->halfdayRuleLateFlag == false && $this->halfdayRuleEarlyFlag == false) {
                                    if ($this->curatt->is_half_day = true) {
                                        $this->curatt->is_half_day = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                    }
                                }
                            } else {
                                $this->debug('Not have user punch in time | out time');
                                $this->curatt->is_half_day = true;
                                $this->curatt->save();

                                $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                $this->curatt->save();

                                StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                        'day_count' => 0.5,
                                    ]);

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                $processTags->create(['name' => 'Half Day', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            }
                        }
                    } else {
                        $this->debug('Half Day Rule is not calculate | User Shift not assign');
                        if ($this->curatt && $this->curatt->is_half_day = true) {
                            $this->curatt->is_half_day = false;
                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                            $this->curatt->save();

                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                            if ($statusMuster) {
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();
                            }

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                        }
                    }
                }
            } else {
                $this->debug('First enable half day rule in setting');
            }
        }
    }

    public function isMonthEnd($date)
    {
        $lastDayOfPreviousMonth = now()->subMonth()->endOfMonth()->format('Y-m-d');
        $firstDayOfCurrentMonth = now()->startOfMonth()->format('Y-m-d');

        if ($date == $lastDayOfPreviousMonth || $date == $firstDayOfCurrentMonth) {
            return true;
        }

        return false;
    }

    public function checkAbsentRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        $coff = Coff::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        if ($leave_application == null || $coff == null) {
            if (setting('absent_rules', '0') == true) { // GeneralHelper::checkSettings("absent_rules") === true) {
                $this->debug('~~~Checking Absent Rule');
                if ($this->user->absent_rule && $this->curatt->shift && $this->curatt) {
                    $absentRule = $this->user?->absent_rule;

                    $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                    $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                    $differenceInMinutes = $punchInTime ? $this->curatt->shift_in_time->diffInMinutes($punchInTime) : 0;
                    $differenceOutMinutes = $punchOutTime ? $punchOutTime->diffInMinutes($this->curatt->shift_out_time) : 0;

                    $work_difference = $punchInTime->diffInMinutes($punchOutTime);
                    // 1. Check is allow single punch
                    if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                        $this->debug('Single Punch Allowed');

                        $this->curatt->is_absent = false;
                        $this->curatt->save();

                        $processTags = $this->curatt->process_tags();
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                        $this->curatt->save();

                        StatusMuster::where('user_id', $this->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                            ->update([
                                'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                'day_count' => 1.0,
                            ]);

                        $processTags->where('name', 'Absent')->delete();
                    } else {
                        // 2. Check punch in time and out time
                        if (($punchInTime && $punchInTime != null) && ($punchOutTime && $punchOutTime != null)) {
                            $this->curatt->is_absent = false;
                            $this->curatt->save();

                            $processTags = $this->curatt->process_tags();
                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                            $this->curatt->save();

                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                            if ($statusMuster) {
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();
                            }

                            $processTags->where('name', 'Absent')->delete();

                            // 3. Check work hours with user work time]
                            if ($work_difference < $absentRule->work_hr_less_than_minutes && $absentRule->work_hr_less_than_minutes > 0) {
                                $this->debug('Check User Work Time To Work Hours Less Than Time');

                                if ($this->halfdayRuleWorkHourFlag == false) {
                                    $this->curatt->is_half_day = true;
                                    $this->curatt->save();

                                    $processTags = $this->curatt->process_tags();
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                            'day_count' => 0.5,
                                        ]);

                                    $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                    $processTags->create(['name' => 'Half Day', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                }
                            } else {
                                $this->debug('User Work Time Not Less Than To Work Hours Less Than Time');
                                if ($this->halfdayRuleWorkHourFlag == false) {
                                    if ($this->curatt->is_half_day = true) {
                                        $this->curatt->is_half_day = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Half Day')->delete();
                                    }
                                }
                            }

                            // 4. Check user is late | early minutes and no of late | early to compare
                            if ($differenceInMinutes > $absentRule->late_coming_minutes && $absentRule->late_coming_minutes > 0) {
                                $today = Carbon::today();

                                // TODO: check weekoff in get dates
                                $lateDateToCheck = $today->copy()->subDays($absentRule->consecutive_late_coming - 1);

                                $att_of_late_with_days = Attendance::where('user_id', $this->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$lateDateToCheck, $today])
                                    ->count();

                                $att_of_late = Attendance::where('user_id', $this->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_late_with_days >= $absentRule->consecutive_late_coming && $absentRule->consecutive_late_coming > 0) {
                                    if ($absentRule->ignore_month_end_late && $this->isMonthEnd($this->manualAttendance->in_time->format('Y-m-d'))) {
                                        $this->debug('User has exceeded the consecutive late coming limit but is not marked as Absent due to ignore month end late');
                                    } else {
                                        $this->debug('User has exceeded the consecutive late coming limit');
                                        $this->absentRuleLateFlag = true;
                                    }
                                } elseif ($att_of_late > $absentRule->no_of_late && $absentRule->no_of_late > 0) {
                                    $this->debug('User has exceeded the no late limit');
                                    $this->absentRuleLateFlag = true;
                                } else {
                                    $this->debug('User has not exceeded his limit');
                                    $this->absentRuleLateFlag = false;
                                }

                                if ($this->absentRuleLateFlag) {
                                    $this->curatt->is_absent = true;
                                    $this->curatt->save();

                                    $this->curatt->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                                            'day_count' => 0.0,
                                        ]);

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Absent')->delete();
                                    $processTags->create(['name' => 'Absent', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                }
                            } else {
                                $this->debug('Absent Rule is not calculate | User not cross the limit of late coming');
                            }

                            if ($differenceOutMinutes > $absentRule->early_going_minutes && $absentRule->early_going_minutes > 0) {
                                $today = Carbon::today();

                                // TODO: check weekoff in get dates
                                $earlyDateToCheck = $today->copy()->subDays($absentRule->consecutive_early_going - 1);

                                $att_of_early_with_days = Attendance::where('user_id', $this->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$earlyDateToCheck, $today])
                                    ->count();

                                $att_of_early = Attendance::where('user_id', $this->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_early_with_days >= $absentRule->consecutive_early_going && $absentRule->consecutive_early_going > 0) {
                                    if ($absentRule->ignore_month_end_early && $this->isMonthEnd($this->manualAttendance->in_time->format('Y-m-d'))) {
                                        $this->debug('User has exceeded the consecutive early going limit but is not marked as Absent due to ignore month end late');
                                    } else {
                                        $this->debug('User has exceeded the consecutive early going limit');
                                        $this->absentRuleEarlyFlag = true;
                                    }
                                } elseif ($att_of_early > $absentRule->no_of_early && $absentRule->no_of_early > 0) {
                                    $this->debug('User has exceeded the no early limit');
                                    $this->absentRuleEarlyFlag = true;
                                } else {
                                    $this->debug('User has not exceeded his limit');
                                    $this->absentRuleEarlyFlag = false;
                                }

                                if ($this->absentRuleEarlyFlag) {
                                    $this->curatt->is_absent = true;
                                    $this->curatt->save();

                                    $this->curatt->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                                            'day_count' => 0.0,
                                        ]);

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Absent')->delete();
                                    $processTags->create(['name' => 'Absent', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                }
                            } else {
                                $this->debug('Absent Rule is not calculate | User not cross the limit of early going');
                            }

                            if ($this->absentRuleWorkHourFlag == false && $absentRule->ignore_month_end_late == false && $this->absentRuleLateFlag == false && $absentRule->ignore_month_end_early == false && $this->absentRuleEarlyFlag == false) {
                                if ($this->curatt->is_absent = true) {
                                    $this->curatt->is_absent = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Absent')->delete();
                                }
                            }
                        } else {
                            $this->debug('Not have user punch in time | out time');
                            $this->curatt->is_absent = true;
                            $this->curatt->save();

                            $this->curatt->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                                    'day_count' => 0.0,
                                ]);
                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Absent')->delete();
                            $processTags->create(['name' => 'Absent', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                        }
                    }
                } else {
                    if ($this->curatt && $this->curatt->shift) {
                        $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                        $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                        $differenceInMinutes = $punchInTime ? $this->curatt->shift_in_time->diffInMinutes($punchInTime) : 0;
                        $differenceOutMinutes = $punchOutTime ? $punchOutTime->diffInMinutes($this->curatt->shift_out_time) : 0;

                        $work_difference = $punchInTime->diffInMinutes($punchOutTime);

                        $shift_hrs = $this->curatt->shift_in_time->diffInMinutes($this->curatt->shift_out_time);
                        $shift_half_hrs = $shift_hrs ? $shift_hrs / 2 : 0;
                        // 1. Check is allow single punch
                        if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                            $this->debug('Single Punch Allowed');

                            $this->curatt->is_absent = false;
                            $this->curatt->save();

                            $processTags = $this->curatt->process_tags();
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                    'day_count' => 1.0,
                                ]);

                            $processTags->where('name', 'Absent')->delete();
                        } else {
                            // 2. Check punch in time and out time
                            if (($punchInTime && $punchInTime != null) && ($punchOutTime && $punchOutTime != null)) {
                                $this->curatt->is_absent = false;
                                $this->curatt->save();

                                $processTags = $this->curatt->process_tags();
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags->where('name', 'Absent')->delete();

                                // 3. Check work hours with user work time]
                                if ($work_difference < $shift_half_hrs && $shift_half_hrs > 0) {
                                    $this->debug('Check User Work Time To Work Hours Less Than Time');

                                    if ($this->halfdayRuleWorkHourFlag == false) {
                                        $this->curatt->is_half_day = true;
                                        $this->curatt->save();

                                        $processTags = $this->curatt->process_tags();
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                                        $this->curatt->save();

                                        StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                            ->update([
                                                'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                                                'day_count' => 0.5,
                                            ]);

                                        $processTags->where('name', 'Half Day | Is First Half')->orWhere('name', 'Half Day | Is Second Half')->orWhere('name', 'Half Day')->delete();
                                        $processTags->create(['name' => 'Half Day', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    }
                                } else {
                                    $this->debug('User Work Time Not Less Than To Work Hours Less Than Time');
                                    if ($this->halfdayRuleWorkHourFlag == false) {
                                        if ($this->curatt->is_half_day = true) {
                                            $this->curatt->is_half_day = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Half Day')->delete();
                                        }
                                    }
                                }

                                if ($this->absentRuleWorkHourFlag == false && $this->absentRuleLateFlag == false && $this->absentRuleEarlyFlag == false) {
                                    if ($this->curatt->is_absent = true) {
                                        $this->curatt->is_absent = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Absent')->delete();
                                    }
                                }
                            } else {
                                $this->debug('Not have user punch in time | out time');
                                $this->curatt->is_absent = true;
                                $this->curatt->save();

                                $this->curatt->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                                $this->curatt->save();

                                StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                                        'day_count' => 0.0,
                                    ]);
                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Absent')->delete();
                                $processTags->create(['name' => 'Absent', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            }
                        }
                    } else {
                        $this->debug('Absent Rule is not calculate | User Shift not assign');
                        if ($this->curatt && $this->curatt->is_absent = true) {
                            $this->curatt->is_absent = false;
                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                            $this->curatt->save();

                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                            if ($statusMuster) {
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();
                            }

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Absent')->delete();
                        }
                    }
                }
            } else {
                $this->debug('First enable absent rule in setting');
            }
        }
    }

    public function checkEarlyGoingRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        $short_leave = ShortLeaveApplication::where('user_id', $this->user->id)
            ->where('date', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))->first();

        $coff = Coff::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        if ($leave_application != null) {
            if ($leave_application->is_first_half && $leave_application->to_date == $this->manualAttendance->in_time->format('Y-m-d')) {
                $punchInTime = Carbon::parse($this->curatt->in_time);
                if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                    $this->debug('Single Punch Allowed');

                    $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                    $this->curatt->is_early = false;
                    $this->curatt->save();

                    $processTags = $this->curatt->process_tags();
                    $processTags->where('name', 'Early Going')->delete();
                } else {
                    if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                        $this->debug('~~~Checking EarlyGoing Rule');
                        if ($this->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                            $ignoreEarlyGoingMinutes = $this->user?->early_going_rule?->ignore_early_going_minutes;
                            $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                            $punchOutTime = Carbon::parse($this->curatt->out_time);

                            if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                if ($ignoreEarlyGoingMinutes >= 0 && $differenceOutMinutes > $ignoreEarlyGoingMinutes) {
                                    $this->debug('User punch out time is less than shift out time and exceeds ignore early going minutes, so user is early out');
                                    $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                                    $this->curatt->is_early = true;
                                    $this->curatt->save();

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                    $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($this->curatt->is_early = true) {
                                        $this->curatt->is_early = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                    }
                                    $this->debug('User Not Less Than Early Going Minutes');
                                }
                            } else {
                                if ($this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                            }
                        } else {
                            if ($this->curatt->shift_out_time && $this->curatt->out_time) {
                                $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                                $punchOutTime = Carbon::parse($this->curatt->out_time);

                                if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                    $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                    if ($differenceOutMinutes > 0) {
                                        $this->debug('User punch out time is less than shift out time, so user is early out');
                                        $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                                        $this->curatt->is_early = true;
                                        $this->curatt->save();

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                        $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_early = true) {
                                            $this->curatt->is_early = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Early Going')->delete();
                                        }
                                        $this->debug('User Not Less Than Early Going Minutes');
                                    }
                                } else {
                                    if ($this->curatt->is_early = true) {
                                        $this->curatt->is_early = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                    }
                                    $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                                }
                            } else {
                                if ($this->curatt && $this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Early Going Rule is calculate | Punch Out time not have this user');
                            }
                        }
                    } else {
                        $this->debug('First enable early going rule in setting');
                    }
                }
            }
        } elseif ($short_leave != null) {
            if ($short_leave->short_leave_type == '2') {
                $punchInTime = Carbon::parse($this->curatt->in_time);
                if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                    $this->debug('Single Punch Allowed');

                    $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                    $this->curatt->is_early = false;
                    $this->curatt->save();

                    $processTags = $this->curatt->process_tags();
                    $processTags->where('name', 'Early Going')->delete();
                } else {
                    if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                        $this->debug('~~~Checking EarlyGoing Rule');
                        if ($this->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                            $ignoreEarlyGoingMinutes = $this->user?->early_going_rule?->ignore_early_going_minutes;
                            $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                            $punchOutTime = Carbon::parse($this->curatt->out_time);

                            if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                if ($differenceOutMinutes > $short_leave->minutes) {
                                    if ($ignoreEarlyGoingMinutes >= 0 && $differenceOutMinutes > $ignoreEarlyGoingMinutes) {
                                        $this->debug('User punch out time is less than shift out time and exceeds ignore early going minutes, so user is early out');
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                        $this->curatt->is_early = true;
                                        $this->curatt->save();

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                        $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_early = true) {
                                            $this->curatt->is_early = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Early Going')->delete();
                                        }
                                        $this->debug('User Not Less Than Early Going Minutes');
                                    }
                                } else {
                                    if ($ignoreEarlyGoingMinutes >= 0 && $differenceOutMinutes > $ignoreEarlyGoingMinutes) {
                                        $this->debug('User punch out time is less than shift out time and exceeds ignore early going minutes, so user is early out');
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                        $this->curatt->is_early = true;
                                        $this->curatt->save();

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                        $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_early = true) {
                                            $this->curatt->is_early = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Early Going')->delete();
                                        }
                                    }
                                }
                            } else {
                                if ($this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                            }
                        } else {
                            $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                            $punchOutTime = Carbon::parse($this->curatt->out_time);

                            if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                if ($differenceOutMinutes > $short_leave->minutes) {
                                    $this->debug('User punch out time is less than shift out time, so user is early out');
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                    $this->curatt->is_early = true;
                                    $this->curatt->save();

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                    $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($differenceOutMinutes > 0) {
                                        $this->debug('User punch out time is less than shift out time, so user is early out');
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                        $this->curatt->is_early = true;
                                        $this->curatt->save();

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                        $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_early = true) {
                                            $this->curatt->is_early = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Early Going')->delete();
                                        }
                                    }
                                }
                            } else {
                                if ($this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                            }
                        }
                    } else {
                        $this->debug('First enable early going rule in setting');
                    }
                }
            }
        } elseif ($coff != null) {
            if ($coff->is_first_half && $coff->to_date == $this->manualAttendance->in_time->format('Y-m-d')) {
                $punchInTime = Carbon::parse($this->curatt->in_time);
                if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                    $this->debug('Single Punch Allowed');

                    $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                    $this->curatt->is_early = false;
                    $this->curatt->save();

                    $processTags = $this->curatt->process_tags();
                    $processTags->where('name', 'Early Going')->delete();
                } else {
                    if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                        $this->debug('~~~Checking EarlyGoing Rule');
                        if ($this->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                            $ignoreEarlyGoingMinutes = $this->user?->early_going_rule?->ignore_early_going_minutes;
                            $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                            $punchOutTime = Carbon::parse($this->curatt->out_time);

                            if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                if ($ignoreEarlyGoingMinutes >= 0 && $differenceOutMinutes > $ignoreEarlyGoingMinutes) {
                                    $this->debug('User punch out time is less than shift out time and exceeds ignore early going minutes, so user is early out');
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                                    $this->curatt->is_early = true;
                                    $this->curatt->save();

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                    $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($this->curatt->is_early = true) {
                                        $this->curatt->is_early = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                    }
                                    $this->debug('User Not Less Than Early Going Minutes');
                                }
                            } else {
                                if ($this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                            }
                        } else {
                            if ($this->curatt->shift_out_time && $this->curatt->out_time) {
                                $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                                $punchOutTime = Carbon::parse($this->curatt->out_time);

                                if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                    $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                    if ($differenceOutMinutes > 0) {
                                        $this->debug('User punch out time is less than shift out time, so user is early out');
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                                        $this->curatt->is_early = true;
                                        $this->curatt->save();

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                        $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_early = true) {
                                            $this->curatt->is_early = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Early Going')->delete();
                                        }
                                        $this->debug('User Not Less Than Early Going Minutes');
                                    }
                                } else {
                                    if ($this->curatt->is_early = true) {
                                        $this->curatt->is_early = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                    }
                                    $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                                }
                            } else {
                                if ($this->curatt && $this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Early Going Rule is calculate | Punch Out time not have this user');
                            }
                        }
                    } else {
                        $this->debug('First enable early going rule in setting');
                    }
                }
            }
        } else {
            $punchInTime = Carbon::parse($this->curatt->in_time);
            if ($this->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                $this->debug('Single Punch Allowed');
                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                $this->curatt->is_early = false;
                $this->curatt->save();

                $processTags = $this->curatt->process_tags();
                $processTags->where('name', 'Early Going')->delete();
            } else {
                if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                    $this->debug('~~~Checking EarlyGoing Rule');
                    if ($this->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                        $ignoreEarlyGoingMinutes = $this->user?->early_going_rule?->ignore_early_going_minutes;
                        $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                        $punchOutTime = Carbon::parse($this->curatt->out_time);

                        if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                            $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                            if ($ignoreEarlyGoingMinutes >= 0 && $differenceOutMinutes > $ignoreEarlyGoingMinutes) {
                                $this->debug('User punch out time is less than shift out time and exceeds ignore early going minutes, so user is early out');
                                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                $this->curatt->is_early = true;
                                $this->curatt->save();

                                StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                        'day_count' => 1,
                                    ]);

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Early Going')->delete();
                                $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            } else {
                                if ($this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('User Not Less Than Early Going Minutes');
                            }
                        } else {
                            if ($this->curatt->is_early = true) {
                                $this->curatt->is_early = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Early Going')->delete();
                            }
                            $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                        }
                    } else {
                        if ($this->curatt->shift_out_time && $this->curatt->out_time) {
                            $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                            $punchOutTime = Carbon::parse($this->curatt->out_time);

                            if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                                $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                                if ($differenceOutMinutes > 0) {
                                    $this->debug('User punch out time is less than shift out time, so user is early out');
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                    $this->curatt->is_early = true;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                            'day_count' => 1,
                                        ]);

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                    $processTags->create(['name' => 'Early Going', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($this->curatt->is_early = true) {
                                        $this->curatt->is_early = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Early Going')->delete();
                                    }
                                    $this->debug('User Not Less Than Early Going Minutes');
                                }
                            } else {
                                if ($this->curatt->is_early = true) {
                                    $this->curatt->is_early = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Early Going')->delete();
                                }
                                $this->debug('Punch Out Time Not Have | Shift Out Time Not Have | Punch Out Time Not Greater Than Shift Out Time');
                            }
                        } else {
                            if ($this->curatt->is_early = true) {
                                $this->curatt->is_early = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Early Going')->delete();
                            }
                            $this->debug('Early Going Rule is not set for user | Punch Out time not have this user');
                        }
                    }
                } else {
                    $this->debug('First enable early going rule in setting');
                }
            }
        }
    }

    public function checkLateComingRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        $short_leave = ShortLeaveApplication::where('user_id', $this->user->id)
            ->where('date', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))->first();

        $coff = Coff::where('user_id', $this->user->id)
            ->where('from_date', '<=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->where('to_date', '>=', Carbon::parse($this->manualAttendance->in_time)->format('Y-m-d'))
            ->first();

        if ($leave_application != null) {
            if ($leave_application->is_first_half && $leave_application->from_date == $today = Carbon::today()->format('Y-m-d')) {
                if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                    $this->debug('~~~Checking LateComing Rule');
                    if ($this->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                        $ignoreLateComingMinutes = $this->user?->late_coming_rule?->ignore_late_coming_minutes;
                        $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                        $punchInTime = Carbon::parse($this->curatt->in_time);

                        if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                            $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                            if ($differenceInMinutes > $ignoreLateComingMinutes && $ignoreLateComingMinutes >= 0) {
                                $this->debug('User punch in time is greater than shift in time and exceeds ignore late coming minutes, so user is late');
                                $this->curatt->status_master_id = StatusMaster::where('code', 'P'.$leave_application->leave_type->code)->first()->id;
                                $this->curatt->is_late = true;
                                $this->curatt->save();

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                                $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();

                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                                $this->debug('User Not Greater Than Late Coming Minutes');
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                        }
                    } else {
                        if ($this->curatt->shift_in_time && $this->curatt->in_time) {
                            $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                            $punchInTime = Carbon::parse($this->curatt->in_time);

                            if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                                $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                                if ($differenceInMinutes > 0) {
                                    $this->debug('User punch in time is greater than shift in time, so user is late');
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'P'.$leave_application->leave_type->code)->first()->id;
                                    $this->curatt->is_late = true;
                                    $this->curatt->save();

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                    $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($this->curatt->is_late = true) {
                                        $this->curatt->is_late = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Late Coming')->delete();
                                    }
                                    $this->debug('User Not Greater Than Late Coming Minutes');
                                }
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                                $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Late Coming Rule is not set for user');
                        }
                    }
                } else {
                    $this->debug('First enable late coming rule in setting');
                }
            }
        } elseif ($short_leave != null) {
            if ($short_leave->short_leave_type == '1') {
                if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                    $this->debug('~~~Checking LateComing Rule');
                    if ($this->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                        $ignoreLateComingMinutes = $this->user?->late_coming_rule?->ignore_late_coming_minutes;
                        $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                        $punchInTime = Carbon::parse($this->curatt->in_time);

                        if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                            $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                            if ($differenceInMinutes > $short_leave->minutes) {
                                if ($differenceInMinutes > $ignoreLateComingMinutes && $ignoreLateComingMinutes >= 0) {
                                    $this->debug('User punch in time is greater than shift in time and exceeds ignore late coming minutes, so user is late');
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                    $this->curatt->is_late = true;
                                    $this->curatt->save();

                                    StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                            'day_count' => 1,
                                        ]);

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                    $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($this->curatt->is_late = true) {
                                        $this->curatt->is_late = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Late Coming')->delete();
                                    }
                                    $this->debug('User Not Greater Than Late Coming Minutes');
                                }
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                        }
                    } else {
                        if ($this->curatt->shift_in_time && $this->curatt->in_time) {
                            $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                            $punchInTime = Carbon::parse($this->curatt->in_time);

                            if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                                $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                                if ($differenceInMinutes > $short_leave->minutes) {
                                    if ($differenceInMinutes > 0) {
                                        $this->debug('User punch in time is greater than shift in time, so user is late');
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                        $this->curatt->is_late = true;
                                        $this->curatt->save();

                                        StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                            ->update([
                                                'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                                'day_count' => 1,
                                            ]);

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Late Coming')->delete();
                                        $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_late = true) {
                                            $this->curatt->is_late = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Late Coming')->delete();
                                        }
                                        $this->debug('User Not Greater Than Late Coming Minutes');
                                    }
                                } else {
                                    if ($differenceInMinutes > 0) {
                                        $this->debug('User punch in time is greater than shift in time, so user is late');
                                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                        $this->curatt->is_late = true;
                                        $this->curatt->save();

                                        StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                            ->update([
                                                'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                                'day_count' => 1,
                                            ]);

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Late Coming')->delete();
                                        $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                    } else {
                                        if ($this->curatt->is_late = true) {
                                            $this->curatt->is_late = false;
                                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                                            $this->curatt->save();

                                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                            if ($statusMuster) {
                                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                                $statusMuster->day_count = $statusMuster->day_count;
                                                $statusMuster->save();
                                            }

                                            $processTags = $this->curatt->process_tags();
                                            $processTags->where('name', 'Late Coming')->delete();
                                        }
                                    }
                                }
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                                $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Late Coming Rule is not set for user');
                        }
                    }
                } else {
                    $this->debug('First enable late coming rule in setting');
                }
            }
        } elseif ($coff != null) {
            if ($coff->is_first_half && $coff->from_date == $this->manualAttendance->in_time->format('Y-m-d')) {
                if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                    $this->debug('~~~Checking LateComing Rule');
                    if ($this->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                        $ignoreLateComingMinutes = $this->user?->late_coming_rule?->ignore_late_coming_minutes;
                        $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                        $punchInTime = Carbon::parse($this->curatt->in_time);

                        if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                            $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                            if ($differenceInMinutes > $ignoreLateComingMinutes && $ignoreLateComingMinutes >= 0) {
                                $this->debug('User punch in time is greater than shift in time and exceeds ignore late coming minutes, so user is late');
                                $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                                $this->curatt->is_late = true;
                                $this->curatt->save();

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                                $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                                $this->debug('User Not Greater Than Late Coming Minutes');
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                        }
                    } else {
                        if ($this->curatt->shift_in_time && $this->curatt->in_time) {
                            $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                            $punchInTime = Carbon::parse($this->curatt->in_time);

                            if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                                $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                                if ($differenceInMinutes > 0) {
                                    $this->debug('User punch in time is greater than shift in time, so user is late');
                                    $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                                    $this->curatt->is_late = true;
                                    $this->curatt->save();

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                    $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                                } else {
                                    if ($this->curatt->is_late = true) {
                                        $this->curatt->is_late = false;
                                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                                        $this->curatt->save();

                                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                        if ($statusMuster) {
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();
                                        }

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Late Coming')->delete();
                                    }
                                    $this->debug('User Not Greater Than Late Coming Minutes');
                                }
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                                $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Late Coming Rule is not set for user');
                        }
                    }
                } else {
                    $this->debug('First enable late coming rule in setting');
                }
            }
        } else {
            if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                $this->debug('~~~Checking LateComing Rule');
                if ($this->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                    $ignoreLateComingMinutes = $this->user?->late_coming_rule?->ignore_late_coming_minutes;
                    $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                    $punchInTime = Carbon::parse($this->curatt->in_time);

                    if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                        $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                        if ($differenceInMinutes > $ignoreLateComingMinutes && $ignoreLateComingMinutes >= 0) {
                            $this->debug('User punch in time is greater than shift in time and exceeds ignore late coming minutes, so user is late');
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->is_late = true;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                    'day_count' => 1,
                                ]);

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Late Coming')->delete();
                            $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('User Not Greater Than Late Coming Minutes');
                        }
                    } else {
                        if ($this->curatt->is_late = true) {
                            $this->curatt->is_late = false;
                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                            $this->curatt->save();

                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                            if ($statusMuster) {
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();
                            }

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Late Coming')->delete();
                        }
                        $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                    }
                } else {
                    if ($this->curatt->shift_in_time && $this->curatt->in_time) {
                        $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                        $punchInTime = Carbon::parse($this->curatt->in_time);

                        if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                            $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                            if ($differenceInMinutes > 0) {
                                $this->debug('User punch in time is greater than shift in time, so user is late');
                                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                $this->curatt->is_late = true;
                                $this->curatt->save();

                                StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                                        'day_count' => 1,
                                    ]);

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                                $processTags->create(['name' => 'Late Coming', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            } else {
                                if ($this->curatt->is_late = true) {
                                    $this->curatt->is_late = false;
                                    $this->curatt->status_master_id = $this->curatt->status_master_id;
                                    $this->curatt->save();

                                    $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                    if ($statusMuster) {
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();
                                    }

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                                $this->debug('User Not Greater Than Late Coming Minutes');
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                                if ($statusMuster) {
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();
                                }

                                $processTags = $this->curatt->process_tags();
                                $processTags->where('name', 'Late Coming')->delete();
                            }
                            $this->debug('Punch In Time Not Have | Shift In Time Not Have | Punch In Time Not Greater Than Shift In Time');
                        }
                    } else {
                        if ($this->curatt->is_late = true) {
                            $this->curatt->is_late = false;
                            $this->curatt->status_master_id = $this->curatt->status_master_id;
                            $this->curatt->save();

                            $statusMuster = StatusMuster::where('user_id', $this->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                            if ($statusMuster) {
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();
                            }

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Late Coming')->delete();
                        }
                        $this->debug('Late Coming Rule is not set for user');
                    }
                }
            } else {
                $this->debug('First enable late coming rule in setting');
            }
        }
    }

    public function checkOverTimeRule(): void
    {
        if (setting('over_time_rules', '0') == true) { // GeneralHelper::checkSettings("over_time_rules") === true) {
            $this->debug('~~~Checking OverTime Rule');
            if ($this->user->overtime_rule) {
                if ($this->curatt->out_time) {
                    $overtimeRule = $this->user?->overtime_rule;

                    $shiftInTime = $this->curatt->shift_in_time ? Carbon::parse($this->curatt->shift_in_time) : null;
                    $shiftOutTime = $this->curatt->shift_out_time ? Carbon::parse($this->curatt->shift_out_time) : null;
                    $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                    $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                    if ($punchOutTime > $shiftOutTime) {
                        $out_diff = $shiftOutTime ? $shiftOutTime->diffInMinutes($punchOutTime) : 0;
                    } else {
                        $out_diff = 0;
                    }
                    $this->debug('After shift overtime minutes : '.$out_diff);

                    if ($punchInTime < $shiftInTime) {
                        $early_diff = $punchInTime ? $punchInTime->diffInMinutes($shiftInTime) : 0;
                        $diffInHoursMinutes = sprintf('%02d:%02d', floor($early_diff / 60), $early_diff % 60);
                        $this->debug('Early diff in minutes: '.$early_diff.', Early diff in HH:SS format : '.$diffInHoursMinutes);
                        if ($overtimeRule->ignore_early_come) {
                            if ($diffInHoursMinutes > $overtimeRule->ignore_early_come_minutes->format('H:i')) {
                                // $this->overtime_early = $diffInHoursMinutes;
                                $this->overtime_early = $early_diff;
                                $out_diff = $out_diff + $this->overtime_early;
                            }
                            $this->debug('Early diff value after out punch add : '.$out_diff);
                        } else {
                            if ($punchInTime < $shiftInTime) {
                                $this->overtime_early = $early_diff;
                                $out_diff = $out_diff + $this->overtime_early;
                            }
                            if ($punchOutTime < $shiftOutTime) {
                                $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                            }
                            $this->debug('Early diff value after out punch add : '.$out_diff);
                        }
                    }

                    if ($punchInTime > $shiftInTime) {
                        $late_diff = $shiftInTime ? $shiftInTime->diffInMinutes($punchInTime) : 0;
                        $diffOutHoursMinutes = sprintf('%02d:%02d', floor($late_diff / 60), $late_diff % 60);
                        $this->debug('Late diff in minutes: '.$late_diff.', Late diff in HH:SS format : '.$diffOutHoursMinutes);
                        if ($overtimeRule->ignore_late_come) {
                            if ($diffOutHoursMinutes > $overtimeRule->ignore_late_come_minutes->format('H:i')) {
                                $this->overtime_late = $late_diff;
                                $out_diff = $out_diff - $this->overtime_late;
                            }
                            $this->debug('Late diff value after out punch minus : '.$out_diff);
                        } else {
                            if ($punchInTime > $shiftInTime) {
                                $this->overtime_late = $late_diff;
                                $out_diff = $out_diff - $this->overtime_late;
                            }
                            if ($punchOutTime < $shiftOutTime) {
                                $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                            }
                            $this->debug('Late diff value after out punch minus : '.$out_diff);
                        }
                    }

                    if ($out_diff >= 0) {
                        $totalDiff = $out_diff;
                        $overtimerule_slabs = $overtimeRule->overtime_slabs()->get();
                        $this->debug('Final Total Overtime : '.$totalDiff.', Total Over time early : '.$this->overtime_early.', Total Over time late : '.$this->overtime_late);
                        foreach ($overtimerule_slabs as $overtimerule_slab) {
                            if (sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) > '00:00' && sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) >= $overtimerule_slab->value_from->format('H:i') && sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) <= $overtimerule_slab->value_to->format('H:i')) {
                                $this->handleOvertime($overtimerule_slab);
                                $this->otFlag = true;
                                break;
                            } else {
                                $this->otFlag = false;
                            }
                        }
                    } else {
                        $this->otFlag = false;
                    }

                    if ($this->otFlag == false) {
                        $this->handleRegularHours();
                    }
                } else {
                    $this->debug('User not have out punch');
                }
            } else {
                if ($this->curatt->out_time && $this->manualAttendance->user->category) {
                    $shiftInTime = $this->curatt->shift_in_time ? Carbon::parse($this->curatt->shift_in_time) : null;
                    $shiftOutTime = $this->curatt->shift_out_time ? Carbon::parse($this->curatt->shift_out_time) : null;
                    $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                    $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                    $skipOvertimeMinutes = 0;
                    if ($this->manualAttendance->user->category->skip_overtime) {
                        // Convert hh:mm:ss to minutes
                        $skipOvertimeTime = $this->manualAttendance->user->category->skip_overtime;
                        if ($skipOvertimeTime) {
                            [$hours, $minutes, $seconds] = explode(':', $skipOvertimeTime);
                            $skipOvertimeMinutes = ($hours * 60) + $minutes;
                            $this->debug('Skip overtime minutes from category: '.$skipOvertimeMinutes);
                        }
                    }

                    if ($punchOutTime > $shiftOutTime) {
                        $out_diff = $shiftOutTime ? $shiftOutTime->diffInMinutes($punchOutTime) : 0;
                    } else {
                        $out_diff = 0;
                    }
                    $this->debug('After shift overtime minutes (before skip): '.$out_diff);

                    if ($punchInTime < $shiftInTime) {
                        $early_diff = $punchInTime ? $punchInTime->diffInMinutes($shiftInTime) : 0;
                        $diffInHoursMinutes = sprintf('%02d:%02d', floor($early_diff / 60), $early_diff % 60);
                        $this->debug('Early diff in minutes: '.$early_diff.', Early diff in HH:SS format : '.$diffInHoursMinutes);
                        if ($punchInTime < $shiftInTime) {
                            $this->overtime_early = $early_diff;
                            $out_diff = $out_diff + $this->overtime_early;
                        }
                        if ($punchOutTime < $shiftOutTime) {
                            $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                        }
                        $this->debug('Early diff value after out punch add : '.$out_diff);
                    }

                    if ($punchInTime > $shiftInTime) {
                        $late_diff = $shiftInTime ? $shiftInTime->diffInMinutes($punchInTime) : 0;
                        $diffOutHoursMinutes = sprintf('%02d:%02d', floor($late_diff / 60), $late_diff % 60);
                        $this->debug('Late diff in minutes: '.$late_diff.', Late diff in HH:SS format : '.$diffOutHoursMinutes);
                        if ($punchInTime > $shiftInTime) {
                            $this->overtime_late = $late_diff;
                            $out_diff = $out_diff - $this->overtime_late;
                        }
                        if ($punchOutTime < $shiftOutTime) {
                            $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                        }
                        $this->debug('Late diff value after out punch minus : '.$out_diff);
                    }

                    // Apply skip overtime - subtract the configured skip time from calculated overtime
                    if ($skipOvertimeMinutes > 0 && $out_diff > 0) {
                        $overtimeBeforeSkip = $out_diff;
                        $out_diff = max(0, $out_diff - $skipOvertimeMinutes);
                        $this->debug('Overtime after applying skip period: '.$out_diff.' (reduced by '.$skipOvertimeMinutes.' minutes)');
                    }

                    if ($out_diff >= 0) {
                        $totalDiff = $out_diff;
                        $this->debug('Final Total Overtime : '.$totalDiff.', Total Over time early : '.$this->overtime_early.', Total Over time late : '.$this->overtime_late);
                        if (sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) > '00:00') {
                            $formattedOvertime = sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60);
                            $this->debug('User get overtime '.$formattedOvertime.' Hour');

                            // Store the overtime amount in a field if needed
                            // $this->curatt->overtime_minutes = $totalDiff;

                            $this->curatt->status_master_id = StatusMaster::where('code', 'PPO')->first()->id;
                            $this->curatt->is_over_time = true;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->manualAttendance->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->manualAttendance->datetime->format('Y-m-d'))
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'PPO')->first()->id,
                                    'day_count' => 1,
                                ]);

                            $processTags = $this->curatt->process_tags();
                            $processTags->where('name', 'Overtime')->delete();
                            $processTags->create(['name' => 'Overtime', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                            $this->otFlag = true;
                        } else {
                            $this->otFlag = false;
                        }
                    } else {
                        $this->otFlag = false;
                    }

                    if ($this->otFlag == false) {
                        $this->handleRegularHours();
                    }
                } else {
                    if ($this->curatt->is_over_time = true) {
                        $this->curatt->is_over_time = false;
                        $this->curatt->status_master_id = $this->curatt->status_master_id;
                        $this->curatt->save();

                        $statusMuster = StatusMuster::where('user_id', $this->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
                        if ($statusMuster) {
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();
                        }

                        $processTags = $this->curatt->process_tags();
                        $processTags->where('name', 'Overtime')->delete();
                    }
                    $this->debug('User not have out punch | Not calculate overtime');
                }
            }
        } else {
            $this->debug('First enable overtime rule in setting');
        }
    }

    // function addTime($time1, $time2)
    // {
    //     // Split the time strings into hours and minutes
    //     list($hours1, $minutes1) = explode(':', $time1);
    //     list($hours2, $minutes2) = explode(':', $time2);

    //     // Convert to total minutes
    //     $totalMinutes1 = ($hours1 * 60) + $minutes1;
    //     $totalMinutes2 = ($hours2 * 60) + $minutes2;

    //     // Add the total minutes
    //     $totalMinutes = $totalMinutes1 + $totalMinutes2;

    //     // Convert back to hours and minutes
    //     $hours = floor($totalMinutes / 60);
    //     $minutes = $totalMinutes % 60;

    //     // Format to H:i
    //     return sprintf('%02d:%02d', $hours, $minutes);
    // }

    // function minusTime($time1, $time2)
    // {
    //     // Split the time strings into hours and minutes
    //     list($hours1, $minutes1) = explode(':', $time1);
    //     list($hours2, $minutes2) = explode(':', $time2);

    //     // Convert to total minutes
    //     $totalMinutes1 = ($hours1 * 60) + $minutes1;
    //     $totalMinutes2 = ($hours2 * 60) + $minutes2;

    //     // minus the total minutes
    //     $totalMinutes = $totalMinutes1 + $totalMinutes2;

    //     // Convert back to hours and minutes
    //     $hours = floor($totalMinutes / 60);
    //     $minutes = $totalMinutes % 60;

    //     // Format to H:i
    //     return sprintf('%02d:%02d', $hours, $minutes);
    // }

    private function handleOvertime($overtimerule_slab)
    {
        $this->debug('User get overtime '.$overtimerule_slab->head_value->format('H:i').' Hour');
        $this->curatt->status_master_id = StatusMaster::where('code', 'PPO')->first()->id;
        $this->curatt->is_over_time = true;
        $this->curatt->save();

        StatusMuster::where('user_id', $this->user->id)
            ->where('is_locked', false)
            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))
            ->update([
                'status_master_id' => StatusMaster::where('code', 'PPO')->first()->id,
                'day_count' => 1,
            ]);

        $processTags = $this->curatt->process_tags();
        $processTags->where('name', 'Overtime')->delete();
        $processTags->create(['name' => 'Overtime', 'success' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
    }

    private function handleRegularHours()
    {
        $this->debug('User  not get overtime | not have any overtime slab for user overtime hour');
        $this->curatt->is_over_time = false;
        $this->curatt->status_master_id = $this->curatt->status_master_id;
        $this->curatt->save();

        $statusMuster = StatusMuster::where('user_id', $this->user->id)
            ->where('is_locked', false)
            ->where('date', $this->manualAttendance->in_time->format('Y-m-d'))->first();
        if ($statusMuster) {
            $statusMuster->status_master_id = $statusMuster->status_master_id;
            $statusMuster->day_count = $statusMuster->day_count;
            $statusMuster->save();
        }

        $processTags = $this->curatt->process_tags();
        $processTags->where('name', 'Overtime')->delete();
    }
}
