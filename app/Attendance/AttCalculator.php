<?php

namespace App\Attendance;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\Coff;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\InOutMuster;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\StatusMaster;
// use App\Models\VisitorInOutMuster;
use App\Models\Tenant\StatusMuster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttCalculator
{
    protected $log;

    protected $returnLog;

    protected $machine;

    protected $area;

    protected $prv_log = null;

    public $f_years = null;

    public $shift = null;

    public $attendance_date = null;

    public $attendance_changed = false;

    public $previousDayLog = false;

    public $curatt = null;

    public $new_att_created = false;

    public $returningLogArray = [];

    public $halfdayRuleLateFlag = false;

    public $halfdayRuleEarlyFlag = false;

    public $halfdayRuleWorkHourFlag = false;

    public $absentRuleLateFlag = false;

    public $absentRuleEarlyFlag = false;

    public $absentRuleWorkHourFlag = false;

    public $overtime_early = 0;

    public $overtime_late = 0;

    public $overtime = null;

    public $otFlag = false;

    public function __construct(AttendanceLog $log, $returnLog = false)
    {
        $this->log = $log;
        $this->returnLog = $returnLog;
        if ($this->returnLog || $this->log->debug_me) {
            DB::enableQueryLog();
        }
    }

    public function debug($message)
    {
        if ($this->log->debug_me) {
            Log::channel('attprocess')->debug('#'.$this->log->id.' '.$message);
        }
        if ($this->returnLog) {
            $this->returningLogArray[] = '#'.$this->log->id.' '.$message;
        }
    }

    public function getReturnLogs()
    {
        return $this->returningLogArray;
    }

    public function checkAlreadyCalculated(): bool
    {
        return $this->log->is_calculated;
    }

    public function setFyears($year = null, $previousDayYear = null): void
    {
        if ($year == null) {
            $year = $this->log->datetime->year;
        }
        if ($previousDayYear == null) {
            $previousDayYear = $this->log->datetime->copy()->subDay()->year;
        }
        if ($year != $previousDayYear) {
            $this->f_years = [
                'prv' => $previousDayYear,
                'cur' => $year,
            ];
            $this->debug('F_years are '.$year.' and '.$previousDayYear);
        } else {
            $this->f_years = $year;
            $this->debug('F_years is '.$year);
        }
        $this->attendance_date = $this->log->datetime->format('Y-m-d');
    }

    public function checkArea(): bool
    {
        $this->machine = $this->log->machine;
        $this->area = $this->log->area;

        return $this->machine != null && $this->area != null;
    }

    public function retrivePreviousLogs(): void
    {
        $from_day = $this->log->datetime->copy()->subDay()->startOfDay();
        $this->debug('~~~Retriving previous logs');

        $this->prv_log = AttendanceLog::where(['user_code' => $this->log->user_code, 'is_calculated' => true, 'is_ignored' => false, 'has_error' => false, 'is_locked' => false])
            ->whereBetween('datetime', [$from_day, $this->log->datetime])
            ->where('attendance_logs.id', '!=', $this->log->id)
            ->whereHas('machine', function (Builder $query) {
                $query->where('area_id', $this->area->id);
            })
            ->orderBy('datetime', 'desc')
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();
        // $this->debug('Query:' . DB::getQueryLog()[0]['query'] . ' Bindings:' . json_encode(DB::getQueryLog()[0]['bindings']));
        if ($this->prv_log != null) {
            $this->debug('Retrived previous logs #'.$this->prv_log->id.'|'.$this->prv_log->user_code.'|'.$this->prv_log->datetime);
        } else {
            $this->debug('Previous log not found between '.$from_day.' and '.$this->log->datetime);
        }
    }

    public function checkIsDuplicatePunch(): bool
    {
        $this->debug('~~~Checking is duplicate punch');
        if ($this->prv_log == null) {
            $this->debug('No Duplicate log found');
        } else {
            $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();
            if ($punchDirectionConsiderSame->value != 0 && $this->prv_log->dms_device_id == $this->log->dms_device_id && $this->prv_log->datetime->diffInMinutes($this->log->datetime) < $punchDirectionConsiderSame->value) {
                $this->debug('Duplicate punch found');
                $this->log->is_ignored = true;
                $this->log->process_tags()->create(['name' => 'Duplicate Punch', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
                $this->log->save();

                return true;
            } else {
                $this->debug('No duplicate punch found');
            }
        }

        return false;
    }

    public function checkInOrOut(): void
    {
        $this->debug('~~~Checking in or out. Machine Punch Type is '.$this->machine->access_direction);
        if ($this->log->is_manual) {
            if ($this->log->is_out) {
                $this->debug('Fixed Punch type is "Out Device" due to manual punch');
                $this->log->is_out = true;
            } else {
                $this->debug('Fixed Punch type is "In Device" due to manual punch');
                $this->log->is_out = false;
            }
        } elseif ($this->machine->access_direction == 'in' || $this->machine->access_direction == '1') {
            $this->debug('Fixed Punch type is "In Device"');
            $this->log->is_out = false;
        } elseif ($this->machine->access_direction == 'out' || $this->machine->access_direction == '2') {
            $this->debug('Fixed Punch type is "Out Device"');
            $this->log->is_out = true;
        } else {
            if ($this->prv_log == null) {
                $this->debug('Marking as In punch as no previous log found');
                $this->log->is_out = false;
            } else {
                $prv_log_shift = Shift::find(ShiftMuster::where('user_id', $this->log->user->id)->where('is_locked', false)->where('date', $this->prv_log->datetime->format('Y-m-d'))->first()->calculated_shift);
                if ($prv_log_shift !== null && $prv_log_shift->is_night_shift) {
                    if ($prv_log_shift->set_cutoff && $prv_log_shift->cutoff_time > '00:00' && $this->log->datetime->format('H:i:s') > $prv_log_shift->out_time->format('H:i:s')) {
                        $diffInMinutes = $this->prv_log->datetime->diffInMinutes($this->log->datetime);
                        $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();

                        if ($punchDirectionConsiderSame->value != 0 && $diffInMinutes < $punchDirectionConsiderSame->value) {
                            // If less than 2 minutes, use same value as previous log
                            $this->debug('Time difference less than '.$punchDirectionConsiderSame->value.' minutes. Using same in/out value as previous log');
                            $this->log->is_out = $this->prv_log->is_out;
                        } else {
                            $this->debug('Time shift is night shift or this shift have cutoff. Toggling in/out value');
                            $this->log->is_out = ! $this->prv_log->is_out;
                        }
                    } elseif (GeneralConfiguration::where('key', 'max_gap_btwn_prv_in_and_curtt_out')->first()->value > '00:00') {
                        $diffInMinutes = $this->prv_log->datetime->diffInMinutes($this->log->datetime);
                        $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();

                        if ($punchDirectionConsiderSame->value != 0 && $diffInMinutes < $punchDirectionConsiderSame->value) {
                            // If less than 2 minutes, use same value as previous log
                            $this->debug('Time difference less than '.$punchDirectionConsiderSame->value.' minutes. Using same in/out value as previous log');
                            $this->log->is_out = $this->prv_log->is_out;
                        } else {
                            $this->debug('Time shift is night shift or this shift have cutoff. Toggling in/out value');
                            $this->log->is_out = ! $this->prv_log->is_out;
                        }
                    } else {
                        $diffInMinutes = $this->prv_log->datetime->diffInMinutes($this->log->datetime);
                        $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();

                        if ($punchDirectionConsiderSame->value != 0 && $diffInMinutes < $punchDirectionConsiderSame->value) {
                            // If less than 2 minutes, use same value as previous log
                            $this->debug('Time difference less than '.$punchDirectionConsiderSame->value.' minutes. Using same in/out value as previous log');
                            $this->log->is_out = $this->prv_log->is_out;
                        } else {
                            $this->debug('Time shift is night shift or this shift have cutoff. Toggling in/out value');
                            $this->log->is_out = ! $this->prv_log->is_out;
                        }
                    }
                } else {
                    if ($this->prv_log->datetime->format('Y-m-d') != $this->log->datetime->format('Y-m-d')) {
                        $this->debug('Previous log date is different from current log date. Marking as In punch');
                        $this->log->is_out = false;
                    } else {
                        $diffInMinutes = $this->prv_log->datetime->diffInMinutes($this->log->datetime);
                        $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();

                        if ($punchDirectionConsiderSame->value != 0 && $diffInMinutes < $punchDirectionConsiderSame->value) {
                            // If less than 2 minutes, use same value as previous log
                            $this->debug('Time difference less than '.$punchDirectionConsiderSame->value.' minutes. Using same in/out value as previous log');
                            $this->log->is_out = $this->prv_log->is_out;
                        } else {
                            $this->debug('Time shift is night shift or this shift have cutoff. Toggling in/out value');
                            $this->log->is_out = ! $this->prv_log->is_out;
                        }
                    }
                }
                // if ($this->shift->is_night_shift || ($this->shift->set_cutoff && $this->shift->cutoff_time > '00:00' && $this->log->datetime->format('H:i:s') > $this->shift->out_time->format('H:i:s'))) {
                //     if ($this->prv_log->datetime->format('H:i:s') >= $this->shift->in_time->format('H:i:s') && $this->prv_log->datetime->format('H:i:s') <= $this->shift->out_time->format('H:i:s')) {
                //         $diffInMinutes = $this->prv_log->datetime->diffInMinutes($this->log->datetime);
                //         $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();

                //         if ($diffInMinutes < $punchDirectionConsiderSame->value) {
                //             dd($punchDirectionConsiderSame->value, $diffInMinutes);
                //             // If less than 2 minutes, use same value as previous log
                //             $this->debug('Time difference less than ' . $punchDirectionConsiderSame->value . ' minutes. Using same in/out value as previous log');
                //             $this->log->is_out = $this->prv_log->is_out;
                //         } else {
                //             $this->debug('Time shift is night shift or this shift have cutoff. Toggling in/out value');
                //             $this->log->is_out = !$this->prv_log->is_out;
                //         }
                //     } else {
                //         $this->debug('Previous log date is same but shift different. Marking as In punch');
                //         $this->log->is_out = false;
                //     }
                // } else {
                //     // Check if previous log date is different from current log date
                //     if ($this->prv_log->datetime->format('Y-m-d') != $this->log->datetime->format('Y-m-d')) {
                //         $this->debug('Previous log date is different from current log date. Marking as In punch');
                //         $this->log->is_out = false;
                //     } else {
                //         // Same date, check time difference
                //         dd($this->prv_log->datetime->format('H:i:s'), $this->shift->in_time->format('H:i:s'), $this->prv_log->datetime->format('H:i:s') <= $this->shift->out_time->format('H:i:s'));
                //         if ($this->prv_log->datetime->format('H:i:s') >= $this->shift->in_time->format('H:i:s') && $this->prv_log->datetime->format('H:i:s') <= $this->shift->out_time->format('H:i:s')) {
                //             $diffInMinutes = $this->prv_log->datetime->diffInMinutes($this->log->datetime);
                //             $this->debug('Diff value : ' . $diffInMinutes);
                //             $punchDirectionConsiderSame = GeneralConfiguration::where('key', 'punch_direction_consider_same')->first();

                //             if ($punchDirectionConsiderSame && $diffInMinutes < $punchDirectionConsiderSame->value) {
                //                 dd($punchDirectionConsiderSame->value, $diffInMinutes);
                //                 // If less than configured minutes, use same value as previous log
                //                 $this->debug('Time difference less than ' . $punchDirectionConsiderSame->value . ' minutes. Using same in/out value as previous log');
                //                 $this->log->is_out = $this->prv_log->is_out;
                //             } else {
                //                 $this->debug('Toggling in/out value');
                //                 $this->log->is_out = !$this->prv_log->is_out;
                //             }
                //         } else {
                //             $this->debug('Previous log date is same but shift different. Marking as In punch');
                //             $this->log->is_out = false;
                //         }
                //     }
                // }
            }
        }
    }

    public function retriveAttendance($curatt_params = null): void
    {
        $recheck = ($curatt_params == null) ? false : true;
        $this->curatt = $curatt_params;
        $this->debug('~~~Retriving previous attendance');
        if (is_array($this->f_years)) {
            $prvatt = $this->queryPrvAttendance($this->f_years['prv']);
        } else {
            $prvatt = $this->queryPrvAttendance($this->f_years);
        }
        if ($prvatt) {
            $this->debug('Previous attendance found #'.$prvatt->id.'|'.$prvatt->date.'|'.$prvatt->shift_code.'|'.$prvatt->in_time.'|'.$prvatt->out_time);
            if ($prvatt->is_night_shift) {
                $this->debug('Previous attendance is night shift');
                // TODO: cuttoff time here from general configuration
                if ($this->log->datetime > $prvatt->datetime) {
                    $shift_in_time = Carbon::parse($prvatt->date)
                        ->setTimeFromTimeString($prvatt->shift->in_time->format('H:i:s'));
                    $shift_out_time = Carbon::parse($this->log->datetime->format('Y-m-d'))
                        ->setTimeFromTimeString($prvatt->shift->out_time->format('H:i:s'));
                } else {
                    $shift_in_time = Carbon::parse($this->log->datetime->format('Y-m-d'))
                        ->setTimeFromTimeString($prvatt->shift->in_time->format('H:i:s'));
                    $shift_out_time = Carbon::parse($this->log->datetime->format('Y-m-d'))
                        ->setTimeFromTimeString($prvatt->shift->out_time->format('H:i:s'));
                }

                if ($this->log->datetime->between($shift_in_time, $shift_out_time)) {
                    $this->debug('Current log is between shift in and shift out');
                    $this->shift = $prvatt->shift;
                    $this->attendance_date = $prvatt->date->format('Y-m-d');
                    $this->curatt = $prvatt;
                    $this->f_years = (is_array($this->f_years)) ? $this->f_years['prv'] : $this->f_years;
                    $this->previousDayLog = true;
                } else {
                    $this->debug('Current log is not between shift in and shift out');
                }
            } else {
                $this->debug('Previous attendance is not night shift');
            }
        }
        if ($this->curatt == null) {
            $this->f_years = (is_array($this->f_years)) ? $this->f_years['cur'] : $this->f_years;
            $this->curatt = $this->queryCrrAttendance($this->f_years);
        }

        if ($this->log->is_out) {
            if ($this->curatt->out_time == null && $this->curatt->in_time == null) {
                $this->debug('In and Out is null so no need to update shift');
                $this->retriveAssignedShift();
                if ($this->attendance_changed) {
                    $this->debug('Attendance date changed to '.$this->attendance_date.' while out punch');
                    if ($prvatt && ($prvatt->out_time == null || $prvatt->out_time->lte($this->log->datetime))) {
                        if ($this->new_att_created) {
                            $this->debug('Removing new attendance created');
                            $this->curatt->delete();
                        }
                        $this->debug('Setting previous attendance as current attendance');
                        $this->curatt = $prvatt;
                        $this->debug('Updating Out Because outtime is null or less than or equal current log time');
                        $this->curatt->out_time = $this->log->datetime;
                        $this->curatt->out_log_id = $this->log->id;
                        $this->curatt->shift_id = $this->shift->id;
                        $this->curatt->shift_code = $this->shift->code;
                        $this->curatt->save();
                    }
                } else {
                    $this->debug('Updating Out Because outtime is null');
                    $this->curatt->out_time = $this->log->datetime;
                    $this->curatt->out_log_id = $this->log->id;
                    $this->curatt->shift_id = $this->shift->id;
                    $this->curatt->shift_code = $this->shift->code;
                    $this->curatt->save();
                }
            } elseif ($this->curatt->out_time == null || $this->curatt->out_time->lte($this->log->datetime)) {
                $this->debug('Updating Out Because outtime is null or less than or equal current log time');
                $this->curatt->out_time = $this->log->datetime;
                $this->curatt->out_log_id = $this->log->id;
                $this->curatt->save();
            } elseif ($this->curatt->in_time == null || $this->curatt->in_time->gte($this->log->datetime)) {
                $this->debug('Updating In Because intime is null or greater than or equal current log time');
                $this->retriveAssignedShift();
                if ($this->attendance_changed) {
                    $this->debug('Attendance date changed to '.$this->attendance_date.' while out punch');
                    if ($this->new_att_created) {
                        $this->debug('Removing new attendance created');
                        $this->curatt->delete();
                    }
                    if (
                        $this->curatt->out_time !== null
                        && ($this->curatt->out_time->eq($this->log->datetime)
                            || $this->curatt->out_time->lt($this->log->datetime))
                    ) {
                        $this->debug('Removing attendance that wrongly created during another punch process');
                        $this->curatt->delete();
                    } else {
                    }

                    $this->f_years = Carbon::createFromDate($this->attendance_date)->year;
                    $this->curatt = $this->queryCrrAttendance($this->f_years);
                    if (! $recheck) {
                        $this->retriveAttendance($this->curatt);

                        return;
                    } else {
                        if ($this->curatt->out_time == null || $this->curatt->out_time->lt($this->log->datetime)) {
                            $this->debug('Updating Out Because outtime is null or less than current log time');
                            $this->curatt->out_time = $this->log->datetime;
                            $this->curatt->out_log_id = $this->log->id;
                            $this->curatt->shift_id = $this->shift->id;
                            $this->curatt->shift_code = $this->shift->code;
                            $this->curatt->save();
                        } else {
                            $this->curatt->in_time = $this->log->datetime;
                            $this->curatt->in_log_id = $this->log->id;
                            $this->curatt->shift_id = $this->shift->id;
                            $this->curatt->shift_code = $this->shift->code;
                            $this->curatt->is_night_shift = $this->shift->is_night_shift;
                            $this->curatt->shift_in_time = Carbon::createFromDate($this->attendance_date)->setTimeFromTimeString($this->shift->in_time->toTimeString());
                            $this->curatt->shift_out_time = Carbon::createFromDate($this->attendance_date)->setTimeFromTimeString($this->shift->out_time->toTimeString());
                            $this->curatt->save();
                        }
                    }
                } else {
                    $this->curatt->in_time = $this->log->datetime;
                    $this->curatt->in_log_id = $this->log->id;
                    $this->curatt->shift_id = $this->shift->id;
                    $this->curatt->shift_code = $this->shift->code;
                    $this->curatt->is_night_shift = $this->shift->is_night_shift;
                    $this->curatt->shift_in_time = Carbon::createFromDate($this->attendance_date)->setTimeFromTimeString($this->shift->in_time->toTimeString());
                    $this->curatt->shift_out_time = Carbon::createFromDate($this->attendance_date)->setTimeFromTimeString($this->shift->out_time->toTimeString());
                    $this->curatt->save();
                }
            } else {
                $this->debug('datetime is between in and out time so no need to update attendance');
            }
        } else {
            if ($this->curatt->in_time == null || $this->curatt->in_time->gte($this->log->datetime)) {
                $this->debug('Updating In Because intime is null or greater than or equal to current log time');
                $this->retriveAssignedShift();
                if ($this->attendance_changed) {
                    $this->debug('Attendance date changed to '.$this->attendance_date.' while in punch');
                    if ($this->new_att_created) {
                        $this->debug('Removing new attendance created');
                        $this->curatt->delete();
                    } elseif ($this->curatt->out_time == null && $this->curatt->in_time == null) {
                        $this->debug('Removing Blank attendance of date '.$this->curatt->date);
                        $this->curatt->delete();
                    } elseif ($this->curatt->out_time !== null && $this->curatt->out_time->lt($this->log->datetime)) {
                        $this->debug('Removing attendance that wrongly created during out punch process');
                        $this->curatt->delete();
                    }
                    $this->f_years = Carbon::createFromDate($this->attendance_date)->year;
                    $this->curatt = $this->queryCrrAttendance($this->f_years);

                    if (! $recheck) {
                        $this->retriveAttendance($this->curatt);

                        return;
                    }
                }
                if ($this->curatt->out_time == null || $this->curatt->out_time->gt($this->log->datetime)) {
                    $this->curatt->in_time = $this->log->datetime;
                    $this->curatt->in_log_id = $this->log->id;
                    $this->curatt->shift_id = $this->shift->id;
                    $this->curatt->shift_code = $this->shift->code;
                    $this->curatt->is_night_shift = $this->shift->is_night_shift;
                    $this->curatt->shift_in_time = Carbon::createFromDate($this->attendance_date)
                        ->setTimeFromTimeString($this->shift->in_time->toTimeString());
                    $this->curatt->shift_out_time = Carbon::createFromDate($this->attendance_date)
                        ->setTimeFromTimeString($this->shift->out_time->toTimeString());
                    $this->curatt->save();
                } else {
                    $this->debug('Updating Out Because outtime less then or equal current log time');
                    $this->curatt->in_time = $this->log->datetime;
                    $this->curatt->in_log_id = $this->log->id;
                    $this->curatt->out_time = null;
                    $this->curatt->out_log_id = null;
                    $this->curatt->shift_id = $this->shift->id;
                    $this->curatt->shift_code = $this->shift->code;
                    $this->curatt->is_night_shift = $this->shift->is_night_shift;
                    $this->curatt->shift_in_time = Carbon::createFromDate($this->attendance_date)
                        ->setTimeFromTimeString($this->shift->in_time->toTimeString());
                    $this->curatt->shift_out_time = Carbon::createFromDate($this->attendance_date)
                        ->setTimeFromTimeString($this->shift->out_time->toTimeString());
                    $this->curatt->save();
                }
            } elseif ($this->curatt->out_time == null || $this->curatt->out_time->lte($this->log->datetime)) {
                // Ignore out_time if punch type is IN
                if ($this->log->is_out == false && GeneralConfiguration::where('key', 'process_another_in_punch')->first() && GeneralConfiguration::where('key', 'process_another_in_punch')->where('value', false)->first()) {
                    $this->debug('ignore this punch because process another in punch is false');
                } else {
                    $this->debug('Updating Out Because outtime is null or less than current log time');
                    $this->curatt->out_time = $this->log->datetime;
                    $this->curatt->out_log_id = $this->log->id;
                    $this->curatt->save();
                }
            } else {
                $this->debug('datetime is between in and out time so no need to update attendance');
            }
        }
        $this->shift = $this->curatt->shift;

        $shift_muster = ShiftMuster::where('user_id', $this->log->user->id)->where('is_locked', false)->where('date', $this->log->datetime->format('Y-m-d'))->first();
        if ($shift_muster) {
            $shift_muster->calculated_shift = $this->shift->id;
            $shift_muster->is_calculated = true;
            $shift_muster->save();
        }

        $leave_application = LeaveApplication::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        $coff = Coff::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        if ($leave_application != null) {
            if ($leave_application->is_only_second_half) {
                if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                    $this->debug('Single Punch Allowed For Only Second Half Leave');
                    $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                    $this->curatt->save();

                    $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->first();
                    $status_muster->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                    $status_muster->day_count = $status_muster->day_count + 0.5;
                    $status_muster->save();
                } else {
                    if ($this->curatt->in_time && $this->curatt->out_time) {
                        $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                        $status_muster->day_count = $status_muster->day_count + 0.5;
                        $status_muster->save();
                    } else {
                        $this->debug('Second Half In or Out Punch Missing');
                        $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'A')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'A')->first()->id;
                        $status_muster->day_count = $status_muster->day_count + 0.5;
                        $status_muster->save();
                    }
                }
            } elseif ($leave_application->is_second_half) {
                if ($leave_application->from_date == $this->log->datetime->format('Y-m-d')) {
                    if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                        $this->debug('Single Punch Allowed For Second Half Leave');
                        $this->curatt->status_master_id = StatusMaster::where('code', 'P'.$leave_application->leave_type->code)->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'P'.$leave_application->leave_type->code)->first()->id;
                        $status_muster->day_count = $status_muster->day_count + 0.5;
                        $status_muster->save();
                    } else {
                        if ($this->curatt->in_time && $this->curatt->out_time) {
                            $this->curatt->status_master_id = StatusMaster::where('code', 'P'.$leave_application->leave_type->code)->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', 'P'.$leave_application->leave_type->code)->first()->id;
                            $status_muster->day_count = $status_muster->day_count + 0.5;
                            $status_muster->save();
                        } else {
                            $this->debug('First Half In or Out Punch Missing');
                            $this->curatt->status_master_id = StatusMaster::where('code', 'A'.$leave_application->leave_type->code)->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', 'A'.$leave_application->leave_type->code)->first()->id;
                            $status_muster->day_count = $status_muster->day_count + 0.5;
                            $status_muster->save();
                        }
                    }
                }
            } elseif ($leave_application->is_first_half) {
                if ($leave_application->to_date == $this->log->datetime->format('Y-m-d')) {
                    if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                        $this->debug('Single Punch Allowed For First Half Leave');
                        $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                        $status_muster->day_count = $status_muster->day_count + 0.5;
                        $status_muster->save();
                    } else {
                        if ($this->curatt->in_time && $this->curatt->out_time) {
                            $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                            $status_muster->day_count = $status_muster->day_count + 0.5;
                            $status_muster->save();
                        } else {
                            $this->debug('Second Half In or Out Punch Missing');
                            $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'A')->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'A')->first()->id;
                            $status_muster->day_count = $status_muster->day_count + 0.5;
                            $status_muster->save();
                        }
                    }
                }
            }
        } elseif ($coff != null) {
            if ($coff->only_a_half_day) {
                if ($coff->is_this_second_half) {
                    if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                        $this->debug('Single Punch Allowed Present For Coff Second Half');
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                        $status_muster->day_count = 1;
                        $status_muster->save();
                    } else {
                        if ($this->curatt->in_time && $this->curatt->out_time) {
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                            $status_muster->day_count = 1;
                            $status_muster->save();
                        } else {
                            $this->debug('Second Half In or Out Punch Missing');
                            $this->curatt->status_master_id = StatusMaster::where('code', 'ACO')->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', 'ACO')->first()->id;
                            $status_muster->day_count = 0.5;
                            $status_muster->save();
                        }
                    }
                } else {
                    if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                        $this->debug('Single Punch Allowed Present For Coff First Half');
                        $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                        $status_muster->day_count = 1;
                        $status_muster->save();
                    } else {
                        if ($this->curatt->in_time && $this->curatt->out_time) {
                            $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                            $status_muster->day_count = 1;
                            $status_muster->save();
                        } else {
                            $this->debug('Second Half In or Out Punch Missing');
                            $this->curatt->status_master_id = StatusMaster::where('code', 'COA')->first()->id;
                            $this->curatt->save();

                            $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
                                ->first();
                            $status_muster->status_master_id = StatusMaster::where('code', 'COA')->first()->id;
                            $status_muster->day_count = 0.5;
                            $status_muster->save();
                        }
                    }
                }
            } elseif ($this->log->datetime->format('Y-m-d') == Carbon::parse($coff->from_date)->format('Y-m-d') && $coff->is_second_half) {
                if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                    $this->debug('Single Punch Allowed For Second Half Coff');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                    $this->curatt->save();

                    $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->first();
                    $status_muster->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                    $status_muster->day_count = 1;
                    $status_muster->save();
                } else {
                    if ($this->curatt->in_time && $this->curatt->out_time) {
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                        $status_muster->day_count = 1;
                        $status_muster->save();
                    } else {
                        $this->curatt->status_master_id = StatusMaster::where('code', 'ACO')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'ACO')->first()->id;
                        $status_muster->day_count = 0.5;
                        $status_muster->save();
                    }
                }
            } elseif ($this->log->datetime->format('Y-m-d') == Carbon::parse($coff->to_date)->format('Y-m-d') && $coff->is_first_half) {
                if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                    $this->debug('Single Punch Allowed For First Half Coff');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                    $this->curatt->save();

                    $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->first();
                    $status_muster->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                    $status_muster->day_count = 1;
                    $status_muster->save();
                } else {
                    if ($this->curatt->in_time && $this->curatt->out_time) {
                        $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                        $status_muster->day_count = 1;
                        $status_muster->save();
                    } else {
                        $this->curatt->status_master_id = StatusMaster::where('code', 'COA')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'COA')->first()->id;
                        $status_muster->day_count = 0.5;
                        $status_muster->save();
                    }
                }
            } else {
                if ($this->log->user->category->single_punch_allowed_present && $this->curatt->in_time) {
                    $this->debug('Single Punch Allowed For Coff');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                    $this->curatt->save();

                    $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->first();
                    $status_muster->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                    $status_muster->day_count = $status_muster->day_count + 0.5;
                    $status_muster->save();
                } else {
                    if ($this->curatt->in_time && $this->curatt->out_time && $this->curatt->in_time->format('H:i') >= $this->curatt->shift->in_time->format('H:i') && $this->curatt->out_time->format('H:i') <= $this->curatt->shift->first_half_end_time->format('H:i')) {
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'PCO')->first()->id;
                        $status_muster->day_count = $status_muster->day_count + 0.5;
                        $status_muster->save();
                    } elseif ($this->curatt->in_time && $this->curatt->out_time && $this->curatt->in_time->format('H:i') >= $this->curatt->shift->second_half_start_time->format('H:i') && $this->curatt->out_time->format('H:i') <= $this->curatt->shift->out_time->format('H:i')) {
                        $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                        $this->curatt->save();

                        $status_muster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
                            ->first();
                        $status_muster->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                        $status_muster->day_count = $status_muster->day_count + 0.5;
                        $status_muster->save();
                    } else {
                        $this->debug('Second Half In or Out Punch Missing');
                    }
                }
            }
        } else {
            if ($this->log->user->category->single_punch_allowed_present != null && $this->log->user->category->single_punch_allowed_present == 'true' && $this->curatt->in_time) {
                $this->debug('Single Punch Allowed');
                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                $this->curatt->save();

                StatusMuster::where('user_id', $this->log->user->id)
                    ->where('is_locked', false)
                    ->where('date', $this->log->datetime->format('Y-m-d'))
                    ->update([
                        'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                        'day_count' => 1.0,
                    ]);
            } else {
                if ($this->curatt->in_time && $this->curatt->out_time == null) {
                    $this->debug('User in time get and out time null');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'PA')->first()->id;
                    $this->curatt->save();

                    StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->update([
                            'status_master_id' => StatusMaster::where('code', 'PA')->first()->id,
                            'day_count' => 0.5,
                        ]);
                } elseif ($this->curatt->in_time == null && $this->curatt->out_time) {
                    $this->debug('User in time null and out time get');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'AP')->first()->id;
                    $this->curatt->save();

                    StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->update([
                            'status_master_id' => StatusMaster::where('code', 'AP')->first()->id,
                            'day_count' => 0.5,
                        ]);
                } elseif ($this->curatt->in_time && $this->curatt->out_time) {
                    $this->debug('User in time get and out time get');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                    $this->curatt->save();

                    StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->update([
                            'status_master_id' => StatusMaster::where('code', 'PP')->first()->id,
                            'day_count' => 1.0,
                        ]);
                } else {
                    $this->debug('User in time and out time null');
                    $this->curatt->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                    $this->curatt->save();

                    StatusMuster::where('user_id', $this->log->user->id)
                        ->where('is_locked', false)
                        ->where('date', $this->log->datetime->format('Y-m-d'))
                        ->update([
                            'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                            'day_count' => 0.0,
                        ]);
                }
            }
        }

        if ($this->log->is_manual) {
            $this->curatt->is_manual = true;
            $this->curatt->save();
        } else {
            $this->curatt->is_manual = false;
            $this->curatt->save();
        }

        // Handle cutoff time logic
        $this->handleCutoffTime();
    }

    public function handleCutoffTime()
    {
        if ($this->log->is_out) {
            // Get previous day's attendance
            $prev_att = $this->queryPrvAttendance($this->f_years);

            if ($prev_att && $prev_att->shift != null && $prev_att->shift != null) {
                if ($prev_att->shift->set_cutoff && $prev_att->shift->cutoff_time->format('H:i') > '00:00') {
                    $shift_start = Carbon::parse($prev_att->date)
                        ->setTimeFromTimeString($prev_att->shift->in_time->format('H:i:s'));

                    $cutoff_time = Carbon::parse($prev_att->date)
                        ->setTimeFromTimeString($prev_att->shift->in_time->format('H:i:s'))
                        ->addMinutes($prev_att->shift->cutoff_time->hour * 60 + $prev_att->shift->cutoff_time->minute);

                    // Check if current punch is between shift start and cutoff time
                    if (
                        $this->log->is_out &&
                        $this->log->datetime->between($shift_start, $cutoff_time)
                    ) {

                        // Delete any attendance created for current day
                        if ($this->curatt && $this->curatt->date->format('Y-m-d') == $this->log->datetime->format('Y-m-d')) {
                            $this->curatt->delete();
                        }

                        // Update previous day's attendance with current out punch
                        $this->curatt = $prev_att;
                        $this->curatt->out_time = $this->log->datetime;
                        $this->curatt->save();
                    }
                } else {
                    $shift_start = Carbon::parse($prev_att->date)
                        ->setTimeFromTimeString($prev_att->shift->in_time->format('H:i:s'));

                    $max_gap = GeneralConfiguration::where('key', 'max_gap_btwn_prv_in_and_curtt_out')->first()->value;
                    if ($max_gap > '00:00') {
                        [$hours, $minutes] = explode(':', $max_gap); // Extract hours and minutes

                        $totalMinutes = ($hours * 60) + $minutes; // Convert to total minutes

                        $shift_cutoff_time = Carbon::parse($prev_att->date)
                            ->setTimeFromTimeString($prev_att->shift->in_time->format('H:i:s'))
                            ->addMinutes($totalMinutes);

                        // Check if current punch is between shift start and cutoff time
                        if (
                            $this->log->is_out &&
                            $this->log->datetime->between($shift_start, $shift_cutoff_time)
                        ) {
                            // Delete any attendance created for current day
                            if ($this->curatt && $this->curatt->date->format('Y-m-d') == $this->log->datetime->format('Y-m-d')) {
                                $this->curatt->delete();
                            }
                            // Update previous day's attendance with current out punch
                            $this->curatt = $prev_att;
                            $this->curatt->out_time = $this->log->datetime;
                            $this->curatt->save();
                        }
                    }
                }
            }
        }
    }

    public function queryPrvAttendance($year)
    {
        return Attendance::year($year)->where('user_id', $this->log->user->id)
            ->where('date', $this->log->datetime->copy()->subDays()->format('Y-m-d'))
            ->where('is_locked', false)
            ->orderBy('date', 'desc')
            ->limit(1)
            ->first();
    }

    public function queryCrrAttendance($year)
    {
        $att = Attendance::year($year)
            ->where('user_id', $this->log->user->id)
            ->where('date', $this->attendance_date)
            ->where('is_locked', false)
            ->orderBy('date', 'desc')
            ->limit(1)
            ->first();
        if ($att == null) {
            $this->new_att_created = true;
            $this->debug('Creating new attendance because no attendance found for date '.$this->attendance_date);
            $att = Attendance::year($year)->updateOrCreate([
                'user_id' => $this->log->user->id,
                'date' => $this->attendance_date,
                'is_locked' => false,
            ], [
                'location_id' => $this->machine->location_id,
                'company_id' => $this->machine->company_id,
                'department_id' => $this->log->user->department_id,
                'sub_department_id' => $this->log->user->sub_department_id,
                'category_id' => $this->log->user->category_id,
                'sub_category_id' => $this->log->user->sub_category_id,
                'area_id' => $this->area->id,
            ]);
        }

        return $att;
    }

    public function retriveAssignedShift(): void
    {
        $this->debug('~~~Retriving assigned shift');
        if ($this->log->user->shift_type == 'auto') {
            $this->debug('Shift type is auto');
            // $shifts = $this->log->user->shifts;
            $shift_musters = ShiftMuster::where('user_id', $this->log->user->id)->where('is_locked', false)->where('is_auto', true)->where('date', $this->log->datetime->format('Y-m-d'))->get();
            $shifts = collect([]);
            foreach ($shift_musters as $shift_muster) {
                $shifts = $shifts->concat(Shift::whereIn('id', $shift_muster->shift)->get());
            }
            if ($shifts && count($shifts) > 0) {
                // $processTags = $this->curatt->process_tags();
                // $processTags->where('name', 'No assigned shift')->delete();
                foreach ($shifts as $shift) {
                    $shift->auto_from = $this->log->datetime->copy()->setTimeFromTimeString($shift->auto_from->toTimeString());
                    $shift->auto_to = $this->log->datetime->copy()->setTimeFromTimeString($shift->auto_to->toTimeString());
                    if ($shift->auto_from > $shift->auto_to) {
                        if ($this->log->datetime->between($shift->auto_from, $shift->auto_to->copy()->addDay())) {
                            $this->debug('Auto Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                            $this->shift = $shift;
                            break;
                        } elseif ($this->log->datetime->between($att_day = $shift->auto_from->copy()->subDay(), $shift->auto_to)) {
                            $this->debug('Auto Previous Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                            $this->attendance_date = $att_day->format('Y-m-d');
                            $this->attendance_changed = true;
                            $this->debug('Attendance date changed to '.$this->attendance_date);
                            $this->shift = $shift;
                            break;
                        }
                    } elseif ($this->log->datetime->between($shift->auto_from, $shift->auto_to)) {
                        $this->debug('Auto Shift Assigned : '.$shift->id.'|'.$shift->code);
                        $this->shift = $shift;
                        break;
                    }
                    $this->debug('Shift #'.$shift->code.' is not matched');
                    $this->debug('BOOL'.$this->log->datetime.'|'.$shift->auto_from.'|'.$shift->auto_to);
                }
            }
        }
        // TODO: check shift_type is rotational if rotational to assign rotational wise shift is work properly
        elseif ($this->log->user->shift_type == 'rotational') {
            $this->debug('Shift type is rotational');
            // $shifts = $this->log->user?->shift_rotation?->rotation_values;
            $shift_musters = ShiftMuster::where('user_id', $this->log->user->id)->where('is_locked', false)->where('date', $this->log->datetime->format('Y-m-d'))->get();
            $shifts = collect([]);
            foreach ($shift_musters as $shift_muster) {
                $shifts = $shifts->concat(Shift::whereIn('id', $shift_muster->shift)->get());
            }
            if ($shifts && count($shifts) > 0) {
                foreach ($shifts as $shift) {
                    // dd($rotational_shift->rotation_values);
                    $shift->auto_from = $this->log->datetime->copy()->setTimeFromTimeString($shift->auto_from->toTimeString());
                    $shift->auto_to = $this->log->datetime->copy()->setTimeFromTimeString($shift->auto_to->toTimeString());
                    if ($shift->auto_from > $shift->auto_to) {
                        if ($this->log->datetime->between($shift->auto_from, $shift->auto_to->copy()->addDay())) {
                            $this->debug('Rotational Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                            $this->shift = $shift;
                            break;
                        } elseif ($this->log->datetime->between($att_day = $shift->auto_from->copy()->subDay(), $shift->auto_to)) {
                            $this->debug('Rotational Previous Night Shift Assigned : '.$shift->id.'|'.$shift->code);
                            $this->attendance_date = $att_day->format('Y-m-d');
                            $this->attendance_changed = true;
                            $this->debug('Attendance date changed to '.$this->attendance_date);
                            $this->shift = $shift;
                            break;
                        }
                    } elseif ($this->log->datetime->between($shift->auto_from, $shift->auto_to)) {
                        $this->debug('Rotational Shift Assigned : '.$shift->id.'|'.$shift->code);
                        $this->shift = $shift;
                        break;
                    }
                    $this->debug('Shift #'.$shift->code.' is not matched');
                    $this->debug('BOOL'.$this->log->datetime.'|'.$shift->auto_from.'|'.$shift->auto_to);
                }
            }
        } else {
            $shift_musters = ShiftMuster::where('user_id', $this->log->user->id)->where('is_locked', false)->where('date', $this->log->datetime->format('Y-m-d'))->get();
            $shifts = collect([]);
            foreach ($shift_musters as $shift_muster) {
                $shifts = $shifts->concat(Shift::whereIn('id', $shift_muster->shift)->get());
            }
            if ($shifts && count($shifts) > 0) {
                $this->debug('Fixed Shift : '.$shifts?->first()->code);
                $this->shift = $shifts->first();
            }
        }

        if ($this->shift == null) {
            // TODO: assign default shift from general configuration
            $this->debug('No shift found for user assigning default shift');
            $this->shift = Shift::first();
            // $processTags = $this->curatt->process_tags();
            // $processTags->where('name', 'Default Shift Assign')->delete();
            // $processTags->create(['name' => 'Default Shift Assign', 'color' => 'danger', 'icon' => 'heroicon-o-document-duplicate']);
        }
    }

    // TODO:check halfday rule and calculate according shift after testing
    public function checkHalfDayRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        $coff = Coff::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        if ($leave_application == null || $coff == null) {
            if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                $this->debug('~~~Checking HalfDay Rule');
                if ($this->log->user->half_day_rule && $this->curatt->shift && $this->curatt) {
                    $halfdayRule = $this->log->user?->half_day_rule;

                    $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                    $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                    $differenceInMinutes = $punchInTime ? $this->curatt->shift_in_time->diffInMinutes($punchInTime) : 0;
                    $differenceOutMinutes = $punchOutTime ? $punchOutTime->diffInMinutes($this->curatt->shift_out_time) : 0;

                    $work_difference = $punchInTime->diffInMinutes($punchOutTime);
                    // 1. Check is allow single punch
                    if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                        $this->debug('Single Punch Allowed');

                        $this->curatt->is_half_day = false;
                        $this->curatt->save();

                        $processTags = $this->curatt->process_tags();
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                        $this->curatt->save();

                        StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();

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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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
                                $today = $this->log->datetime;

                                $lateDateToCheck = $today->copy()->subDays($halfdayRule->consecutive_late_coming - 1);

                                $att_of_late_with_days = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$lateDateToCheck, $today])
                                    ->count();

                                $att_of_late = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_late_with_days >= $halfdayRule->consecutive_late_coming && $halfdayRule->consecutive_late_coming > 0) {
                                    if ($halfdayRule->ignore_month_end_late && $this->isMonthEnd($this->log->datetime->format('Y-m-d'))) {
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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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
                                $today = $this->log->datetime;

                                $earlyDateToCheck = $today->copy()->subDays($halfdayRule->consecutive_early_going - 1);

                                $att_of_early_with_days = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$earlyDateToCheck, $today])
                                    ->count();

                                $att_of_early = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_early_with_days >= $halfdayRule->consecutive_early_going && $halfdayRule->consecutive_early_going > 0) {
                                    if ($halfdayRule->ignore_month_end_early && $this->isMonthEnd($this->log->datetime->format('Y-m-d'))) {
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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                            StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
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
                        if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                            $this->debug('Single Punch Allowed');

                            $this->curatt->is_half_day = false;
                            $this->curatt->save();

                            $processTags = $this->curatt->process_tags();
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                                        StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                        StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))
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

                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();

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

    // TODO:check absent rule and calculate according shift after testing
    public function checkAbsentRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        $coff = Coff::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        if ($leave_application == null || $coff == null) {
            if (setting('absent_rules', '0') == true) { // GeneralHelper::checkSettings("absent_rules") === true) {
                $this->debug('~~~Checking Absent Rule');
                if ($this->log->user->absent_rule && $this->curatt->shift && $this->curatt) {
                    $absentRule = $this->log->user?->absent_rule;

                    $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                    $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                    $differenceInMinutes = $punchInTime ? $this->curatt->shift_in_time->diffInMinutes($punchInTime) : 0;
                    $differenceOutMinutes = $punchOutTime ? $punchOutTime->diffInMinutes($this->curatt->shift_out_time) : 0;

                    $work_difference = $punchInTime->diffInMinutes($punchOutTime);
                    // 1. Check is allow single punch
                    if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                        $this->debug('Single Punch Allowed');

                        $this->curatt->is_absent = false;
                        $this->curatt->save();

                        $processTags = $this->curatt->process_tags();
                        $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                        $this->curatt->save();

                        StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();

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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

                                        $processTags = $this->curatt->process_tags();
                                        $processTags->where('name', 'Half Day')->delete();
                                    }
                                }
                            }

                            // 4. Check user is late | early minutes and no of late | early to compare
                            if ($differenceInMinutes > $absentRule->late_coming_minutes && $absentRule->late_coming_minutes > 0) {
                                $today = $this->log->datetime;

                                // TODO: check weekoff in get dates
                                $lateDateToCheck = $today->copy()->subDays($absentRule->consecutive_late_coming - 1);

                                $att_of_late_with_days = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$lateDateToCheck, $today])
                                    ->count();

                                $att_of_late = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_late', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_late_with_days >= $absentRule->consecutive_late_coming && $absentRule->consecutive_late_coming > 0) {
                                    if ($absentRule->ignore_month_end_late && $this->isMonthEnd($this->log->datetime->format('Y-m-d'))) {
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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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
                                $today = $this->log->datetime;

                                // TODO: check weekoff in get dates
                                $earlyDateToCheck = $today->copy()->subDays($absentRule->consecutive_early_going - 1);

                                $att_of_early_with_days = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$earlyDateToCheck, $today])
                                    ->count();

                                $att_of_early = Attendance::where('user_id', $this->log->user->id)
                                    ->where('is_early', true)
                                    ->where('is_locked', false)
                                    ->whereMonth('date', now()->month)
                                    ->count();

                                if ($att_of_early_with_days >= $absentRule->consecutive_early_going && $absentRule->consecutive_early_going > 0) {
                                    if ($absentRule->ignore_month_end_early && $this->isMonthEnd($this->log->datetime->format('Y-m-d'))) {
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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                            StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
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
                        if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $punchOutTime == null) {
                            $this->debug('Single Punch Allowed');

                            $this->curatt->is_absent = false;
                            $this->curatt->save();

                            $processTags = $this->curatt->process_tags();
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                                        StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))
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

                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();

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

    // TODO:check earlygoing rule and calculate according shift after testing
    public function checkEarlyGoingRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        $short_leave = ShortLeaveApplication::where('user_id', $this->log->user->id)
            ->where('date', $this->log->datetime->format('Y-m-d'))->first();

        $coff = Coff::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        if ($leave_application != null) {
            if ($leave_application->is_first_half && $leave_application->to_date == $this->log->datetime->format('Y-m-d')) {
                $punchInTime = Carbon::parse($this->curatt->in_time);
                if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                    $this->debug('Single Punch Allowed');

                    $this->curatt->status_master_id = StatusMaster::where('code', $leave_application->leave_type->code.'P')->first()->id;
                    $this->curatt->is_early = false;
                    $this->curatt->save();

                    $processTags = $this->curatt->process_tags();
                    $processTags->where('name', 'Early Going')->delete();
                } else {
                    if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                        $this->debug('~~~Checking EarlyGoing Rule');
                        if ($this->log->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                            $ignoreEarlyGoingMinutes = $this->log->user?->early_going_rule?->ignore_early_going_minutes;
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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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
                if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                    $this->debug('Single Punch Allowed');

                    $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                    $this->curatt->is_early = false;
                    $this->curatt->save();

                    $processTags = $this->curatt->process_tags();
                    $processTags->where('name', 'Early Going')->delete();
                } else {
                    if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                        $this->debug('~~~Checking EarlyGoing Rule');
                        if ($this->log->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                            $ignoreEarlyGoingMinutes = $this->log->user?->early_going_rule?->ignore_early_going_minutes;
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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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
            if ($coff->is_first_half && $coff->to_date == $this->log->datetime->format('Y-m-d')) {
                $punchInTime = Carbon::parse($this->curatt->in_time);
                if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                    $this->debug('Single Punch Allowed');

                    $this->curatt->status_master_id = StatusMaster::where('code', 'COP')->first()->id;
                    $this->curatt->is_early = false;
                    $this->curatt->save();

                    $processTags = $this->curatt->process_tags();
                    $processTags->where('name', 'Early Going')->delete();
                } else {
                    if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                        $this->debug('~~~Checking EarlyGoing Rule');
                        if ($this->log->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                            $ignoreEarlyGoingMinutes = $this->log->user?->early_going_rule?->ignore_early_going_minutes;
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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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
            if ($this->log->user->category->single_punch_allowed_present && $punchInTime && $punchInTime != null && $this->curatt->out_time == null) {
                $this->debug('Single Punch Allowed');
                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                $this->curatt->is_early = false;
                $this->curatt->save();

                $processTags = $this->curatt->process_tags();
                $processTags->where('name', 'Early Going')->delete();
            } else {
                if (setting('early_going_rules', '0') == true) { // GeneralHelper::checkSettings("early_going_rules") === true) {
                    $this->debug('~~~Checking EarlyGoing Rule');
                    if ($this->log->user->early_going_rule && $this->curatt->shift_out_time && $this->curatt->out_time) {
                        $ignoreEarlyGoingMinutes = $this->log->user?->early_going_rule?->ignore_early_going_minutes;
                        $shiftOutTime = Carbon::parse($this->curatt->shift_out_time);
                        $punchOutTime = Carbon::parse($this->curatt->out_time);

                        if ($punchOutTime && $shiftOutTime && $punchOutTime->lte($shiftOutTime)) {
                            $differenceOutMinutes = $punchOutTime->diffInMinutes($shiftOutTime);

                            if ($ignoreEarlyGoingMinutes >= 0 && $differenceOutMinutes > $ignoreEarlyGoingMinutes) {
                                $this->debug('User punch out time is less than shift out time and exceeds ignore early going minutes, so user is early out');
                                $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                                $this->curatt->is_early = true;
                                $this->curatt->save();

                                StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

    // TODO:check latecoming rule and calculate according shift after testing
    public function checkLateComingRule(): void
    {
        $leave_application = LeaveApplication::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        $short_leave = ShortLeaveApplication::where('user_id', $this->log->user->id)
            ->where('date', $this->log->datetime->format('Y-m-d'))->first();

        $coff = Coff::where('user_id', $this->log->user->id)
            ->where('from_date', '<=', $this->log->datetime->format('Y-m-d'))
            ->where('to_date', '>=', $this->log->datetime->format('Y-m-d'))
            ->first();

        if ($leave_application != null) {
            if ($leave_application->is_first_half && $leave_application->from_date == $this->log->datetime->format('Y-m-d')) {
                if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                    $this->debug('~~~Checking LateComing Rule');
                    if ($this->log->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                        $ignoreLateComingMinutes = $this->log->user?->late_coming_rule?->ignore_late_coming_minutes;
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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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
                    if ($this->log->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                        $ignoreLateComingMinutes = $this->log->user?->late_coming_rule?->ignore_late_coming_minutes;
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

                                    StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

                                    $processTags = $this->curatt->process_tags();
                                    $processTags->where('name', 'Late Coming')->delete();
                                }
                            }
                        } else {
                            if ($this->curatt->is_late = true) {
                                $this->curatt->is_late = false;
                                $this->curatt->status_master_id = $this->curatt->status_master_id;
                                $this->curatt->save();

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                                        StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                        StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                                ->where('is_locked', false)
                                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                                            $statusMuster->day_count = $statusMuster->day_count;
                                            $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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
            if ($coff->is_first_half && $coff->from_date == $this->log->datetime->format('Y-m-d')) {
                if (setting('half_day_rules', '0') == true) { // GeneralHelper::checkSettings("half_day_rules") === true) {
                    $this->debug('~~~Checking LateComing Rule');
                    if ($this->log->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                        $ignoreLateComingMinutes = $this->log->user?->late_coming_rule?->ignore_late_coming_minutes;
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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                            ->where('is_locked', false)
                                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                                        $statusMuster->day_count = $statusMuster->day_count;
                                        $statusMuster->save();

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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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
                if ($this->log->user->late_coming_rule && $this->curatt->shift_in_time && $this->curatt->in_time) {
                    $ignoreLateComingMinutes = $this->log->user?->late_coming_rule?->ignore_late_coming_minutes;
                    $shiftInTime = Carbon::parse($this->curatt->shift_in_time);
                    $punchInTime = Carbon::parse($this->curatt->in_time);

                    if ($punchInTime && $shiftInTime && $punchInTime->gte($shiftInTime)) {
                        $differenceInMinutes = $shiftInTime->diffInMinutes($punchInTime);

                        if ($differenceInMinutes > $ignoreLateComingMinutes && $ignoreLateComingMinutes >= 0) {
                            $this->debug('User punch in time is greater than shift in time and exceeds ignore late coming minutes, so user is late');
                            $this->curatt->status_master_id = StatusMaster::where('code', 'PP')->first()->id;
                            $this->curatt->is_late = true;
                            $this->curatt->save();

                            StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();

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

                                StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))
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

                                    $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                        ->where('is_locked', false)
                                        ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                    $statusMuster->status_master_id = $statusMuster->status_master_id;
                                    $statusMuster->day_count = $statusMuster->day_count;
                                    $statusMuster->save();

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

                                $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                    ->where('is_locked', false)
                                    ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                                $statusMuster->status_master_id = $statusMuster->status_master_id;
                                $statusMuster->day_count = $statusMuster->day_count;
                                $statusMuster->save();

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

                            $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                            $statusMuster->status_master_id = $statusMuster->status_master_id;
                            $statusMuster->day_count = $statusMuster->day_count;
                            $statusMuster->save();

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

    // TODO:check overtime rule and calculate according shift after testing
    public function checkOverTimeRule(): void
    {
        if (setting('over_time_rules', '0') == true) { // GeneralHelper::checkSettings("over_time_rules") === true) {
            $this->debug('~~~Checking OverTime Rule');
            if ($this->log->user->overtime_rule) {
                if ($this->curatt->out_time) {
                    $overtimeRule = $this->log->user?->overtime_rule;

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
                if ($this->curatt->out_time && $this->log->user->category) {
                    $shiftInTime = $this->curatt->shift_in_time ? Carbon::parse($this->curatt->shift_in_time) : null;
                    $shiftOutTime = $this->curatt->shift_out_time ? Carbon::parse($this->curatt->shift_out_time) : null;
                    $punchInTime = $this->curatt->in_time ? Carbon::parse($this->curatt->in_time) : null;
                    $punchOutTime = $this->curatt->out_time ? Carbon::parse($this->curatt->out_time) : null;

                    $skipOvertimeMinutes = 0;
                    if ($this->log->user->category->skip_overtime && $this->log->user->category->skip_overtime > '00:00' || $this->log->user->category->skip_overtime > '00:00:00') {
                        // Convert hh:mm:ss to minutes
                        $skipOvertimeTime = $this->log->user->category->skip_overtime;
                        [$hours, $minutes] = explode(':', $skipOvertimeTime->format('H:i'));
                        $skipOvertimeMinutes = ($hours * 60) + $minutes;
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

                            StatusMuster::where('user_id', $this->log->user->id)
                                ->where('is_locked', false)
                                ->where('date', $this->log->datetime->format('Y-m-d'))
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

                        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
                            ->where('is_locked', false)
                            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
                        $statusMuster->status_master_id = $statusMuster->status_master_id;
                        $statusMuster->day_count = $statusMuster->day_count;
                        $statusMuster->save();

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

    private function handleOvertime($overtimerule_slab)
    {
        $this->debug('User get overtime '.$overtimerule_slab->head_value->format('H:i').' Hour');
        $this->curatt->status_master_id = StatusMaster::where('code', 'PPO')->first()->id;
        $this->curatt->is_over_time = true;
        $this->curatt->save();

        StatusMuster::where('user_id', $this->log->user->id)
            ->where('is_locked', false)
            ->where('date', $this->log->datetime->format('Y-m-d'))
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
        $this->debug('User not get overtime | not have any overtime slab for user overtime hour');
        $this->curatt->is_over_time = false;
        $this->curatt->status_master_id = $this->curatt->status_master_id;
        $this->curatt->save();

        $statusMuster = StatusMuster::where('user_id', $this->log->user->id)
            ->where('is_locked', false)
            ->where('date', $this->log->datetime->format('Y-m-d'))->first();
        $statusMuster->status_master_id = $statusMuster->status_master_id;
        $statusMuster->day_count = $statusMuster->day_count;
        $statusMuster->save();

        $processTags = $this->curatt->process_tags();
        $processTags->where('name', 'Overtime')->delete();
    }

    // TODO: Add method to check today leave or not

    public function createOrUpdateInOutMuster(): void
    {
        if (! $this->log->is_ignored) {
            $this->debug('~~~Creating or updating in out muster');
            if ($this->log->is_manual) {
                if ($this->log->is_out) {
                    $this->machine->access_direction = 'out';
                    $this->handelFixedOut();
                } else {
                    $this->machine->access_direction = 'in';
                    $this->handelFixedIn();
                }
            } elseif ($this->machine->access_direction == 'in' || $this->machine->access_direction == '1') {
                $this->handelFixedIn();
            } elseif ($this->machine->access_direction == 'out' || $this->machine->access_direction == '2') {
                $this->handelFixedOut();
            } else {
                if ($this->log->is_out) {
                    $this->handelFixedOut();
                } else {
                    $this->handelFixedIn();
                }
            }
        } else {
            $this->debug('In/Out punch is duplicate, not create or update InOut Muster');
        }
    }

    public function handelFixedIn(): void
    {

        $updating = $this->queryFixedInEntry($this->f_years);

        if ($updating) {
            if ($updating->in_time == null && $updating->area_id == $this->area->id) {
                $this->debug('Updating InOutMuster #'.$updating->id);
                $updating->in_time = $this->log->datetime;
                $updating->in_log_id = $this->log->id;
                if ($updating->check_time->lt($this->log->datetime)) {
                    $updating->check_time = $this->log->datetime;
                }
                $updating->work_time = $this->log->datetime->diffInMinutes($updating->out_time);
                $updating->save();
                $muster = $updating;
            } elseif ($updating->in_time != null && $updating->in_time->eq($this->log->datetime) && $updating->area_id == $this->area->id) {
                $this->debug('InOutMuster #'.$updating->id.' is already updated');
                $muster = $updating;
            } elseif ($updating->in_time != null && $updating->in_time->lt($this->log->datetime) && $updating->area_id == $this->area->id) {
                if ($updating->out_time != null && $updating->out_time->gt($this->log->datetime)) {

                    $out_time = $updating->out_time;
                    $out_log_id = $updating->out_log_id;
                    $updating->check_time = $updating->in_time;
                    $updating->out_time = null;
                    $updating->out_log_id = null;
                    $updating->work_time = null;
                    $updating->save();
                    $this->debug('Updating InOutMuster TO OUT BLANK FOR MOVE TO NEW. OLD IS #'.$updating->id);
                    $muster = InOutMuster::year($this->f_years)->updateOrCreate(
                        [
                            'user_id' => $this->log->user->id,
                            'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                            'area_id' => $this->area->id,
                            'in_time' => $this->log->datetime,
                            'in_log_id' => $this->log->id,
                            'is_locked' => false,
                        ],
                        [

                            'location_id' => $this->machine->location_id,
                            'company_id' => $this->machine->company_id,
                            'department_id' => $this->log->user->department_id,
                            'sub_department_id' => $this->log->user->sub_department_id,
                            'category_id' => $this->log->user->category_id,
                            'sub_category_id' => $this->log->user->sub_category_id,
                            'shift_id' => $this->shift?->id,
                            'check_time' => $out_time,
                            'out_time' => $out_time,
                            'out_log_id' => $out_log_id,
                            'work_time' => $this->log->datetime->diffInMinutes($out_time),
                            'is_locked' => false,
                            'has_error' => false,
                            'remarks' => null,
                        ]
                    );
                    $this->debug('InOutMuster created : #'.$muster->id);
                } else {
                    $this->debug('Creating new InOutMuster because of in time is less than previous in time');
                    $muster = InOutMuster::year($this->f_years)->updateOrCreate([
                        'user_id' => $this->log->user->id,
                        'area_id' => $this->area->id,
                        'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                        'in_time' => $this->log->datetime,
                        'in_log_id' => $this->log->id,
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->machine->location_id,
                        'company_id' => $this->machine->company_id,
                        'department_id' => $this->log->user->department_id,
                        'sub_department_id' => $this->log->user->sub_department_id,
                        'category_id' => $this->log->user->category_id,
                        'sub_category_id' => $this->log->user->sub_category_id,
                        'check_time' => $this->log->datetime,
                        'shift_id' => $this->shift?->id,
                        // 'out_time' => null,
                        // 'out_log_id' => null,
                        // 'work_time' => null,
                        'is_locked' => false,
                        'has_error' => false,
                        'remarks' => null,
                    ]);
                    $this->debug('InOutMuster created : '.$muster->id);
                }
            } else {
                $this->debug('Creating new InOutMuster because of in time is greater than previous in time');
                $this->debug('InOutMuster #'.$updating->id.'|IN:'.$updating->in_time.'|OUT:'.$updating->out_time.'|CHECK:'.$updating->check_time);
                $muster = InOutMuster::year($this->f_years)->updateOrCreate([
                    'user_id' => $this->log->user->id,
                    'area_id' => $this->area->id,
                    'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                    'in_time' => $this->log->datetime,
                    'in_log_id' => $this->log->id,
                    'is_locked' => false,
                ], [

                    'location_id' => $this->machine->location_id,
                    'company_id' => $this->machine->company_id,
                    'department_id' => $this->log->user->department_id,
                    'sub_department_id' => $this->log->user->sub_department_id,
                    'category_id' => $this->log->user->category_id,
                    'sub_category_id' => $this->log->user->sub_category_id,
                    'check_time' => $this->log->datetime,
                    'shift_id' => $this->shift?->id,
                    // 'out_time' => null,
                    // 'out_log_id' => null,
                    // 'work_time' => null,
                    'is_locked' => false,
                    'has_error' => false,
                    'remarks' => null,
                ]);
                $this->debug('InOutMuster created : '.$muster->id);
            }
        } else {
            $muster = InOutMuster::year($this->f_years)->updateOrCreate([
                'user_id' => $this->log->user->id,
                'area_id' => $this->area->id,
                'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                'in_time' => $this->log->datetime,
                'in_log_id' => $this->log->id,
                'is_locked' => false,
            ], [
                'location_id' => $this->machine->location_id,
                'company_id' => $this->machine->company_id,
                'department_id' => $this->log->user->department_id,
                'sub_department_id' => $this->log->user->sub_department_id,
                'category_id' => $this->log->user->category_id,
                'sub_category_id' => $this->log->user->sub_category_id,
                'shift_id' => $this->shift?->id,
                'check_time' => $this->log->datetime,
                // 'out_time' => null,
                // 'out_log_id' => null,
                // 'work_time' => null,
                'is_locked' => false,
                'has_error' => false,
                'remarks' => null,
            ]);
            $this->debug('InOutMuster created : '.$muster->id);
        }
    }

    public function queryFixedInEntry($year)
    {
        // dd($this->log->datetime);
        $inquery = InOutMuster::year($year)
            ->where('user_id', $this->log->user->id)
            ->where('is_locked', false)
            ->where('check_time', '>=', $this->log->datetime)
            // ->where(function (Builder $query) {
            //     $query->where('check_time', '>=', $this->log->datetime)
            //         ->orWhere('in_time', '<=', $this->log->datetime);
            // })
            ->where('check_time', '<=', $this->log->datetime->copy()->addDay()->endOfDay())
            ->where(function (Builder $query) {
                $query->whereNULL('in_time')->orWhere('in_time', '<=', $this->log->datetime);
            })
            ->orderBy('check_time', 'asc')
            ->limit(1)
            ->first();
        // $this->debug('Query:' . DB::getQueryLog()[0]['query'] . ' Bindings:' . json_encode(DB::getQueryLog()[0]['bindings']));
        // dd($inquery);
        $this->debug('InQueryResult : '.($inquery ? $inquery->id : 'NULL'));

        return $inquery;
    }

    public function handelFixedOut()
    {
        $updating = $this->queryFixedOutEntry($this->f_years);
        if ($updating) {
            if ($updating->out_time == null && $updating->area_id == $this->area->id) {
                $this->debug('Updating InOutMuster #'.$updating->id);
                $updating->out_time = $this->log->datetime;
                $updating->out_log_id = $this->log->id;
                if ($updating->check_time->lt($this->log->datetime)) {
                    $updating->check_time = $this->log->datetime;
                }
                $updating->work_time = $updating->in_time->diffInMinutes($this->log->datetime);
                $updating->save();
                $muster = $updating;
            } elseif ($updating->out_time != null && $updating->out_time->eq($this->log->datetime) && $updating->area_id == $this->area->id) {
                $this->debug('InOutMuster #'.$updating->id.' is already updated');
                $muster = $updating;
            } elseif ($updating->out_time != null && $updating->out_time->gt($this->log->datetime) && $updating->area_id == $this->area->id) {
                if ($updating->in_time != null && $updating->in_time->lt($this->log->datetime)) {
                    $in_time = $updating->in_time;
                    $in_log_id = $updating->in_log_id;
                    $updating->in_time = null;
                    $updating->in_log_id = null;
                    $updating->work_time = null;
                    $updating->save();
                    $this->debug('Updating InOutMuster TO IN BLANK FOR MOVE TO NEW #'.$updating->id);
                    $muster = InOutMuster::year($this->f_years)->updateOrCreate(
                        [
                            'user_id' => $this->log->user->id,
                            'area_id' => $this->area->id,
                            'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                            'out_time' => $this->log->datetime,
                            'out_log_id' => $this->log->id,
                            'is_locked' => false,
                        ],
                        [

                            'location_id' => $this->machine->location_id,
                            'company_id' => $this->machine->company_id,
                            'department_id' => $this->log->user->department_id,
                            'sub_department_id' => $this->log->user->sub_department_id,
                            'category_id' => $this->log->user->category_id,
                            'sub_category_id' => $this->log->user->sub_category_id,
                            'shift_id' => $this->shift?->id,
                            'check_time' => $this->log->datetime,
                            'in_time' => $in_time,
                            'in_log_id' => $in_log_id,
                            'work_time' => $in_time->diffInMinutes($this->log->datetime),
                            'is_locked' => false,
                            'has_error' => false,
                            'remarks' => null,
                        ]
                    );
                    $this->debug('InOutMuster created : #'.$muster->id);
                } else {
                    $this->debug('Creating new InOutMuster because of out time is greater than previous out time');
                    $muster = InOutMuster::year($this->f_years)->updateOrCreate([
                        'user_id' => $this->log->user->id,
                        'area_id' => $this->area->id,
                        'out_time' => $this->log->datetime,
                        'out_log_id' => $this->log->id,
                        'is_locked' => false,
                    ], [

                        'location_id' => $this->machine->location_id,
                        'company_id' => $this->machine->company_id,
                        'department_id' => $this->log->user->department_id,
                        'sub_department_id' => $this->log->user->sub_department_id,
                        'category_id' => $this->log->user->category_id,
                        'sub_category_id' => $this->log->user->sub_category_id,
                        'shift_id' => $this->shift?->id,
                        'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                        'check_time' => $this->log->datetime,
                        // 'in_time' => null,
                        // 'in_log_id' => null,
                        // 'work_time' => null,
                        'is_locked' => false,
                        'has_error' => false,
                        'remarks' => null,
                    ]);
                    $this->debug('InOutMuster created : '.$muster->id);
                }
            } else {
                $this->debug('Creating new InOutMuster because of out time is less than previous out time');
                $muster = InOutMuster::year($this->f_years)->updateOrCreate([
                    'user_id' => $this->log->user->id,
                    'area_id' => $this->area->id,
                    'out_time' => $this->log->datetime,
                    'out_log_id' => $this->log->id,
                    'is_locked' => false,
                ], [

                    'location_id' => $this->machine->location_id,
                    'company_id' => $this->machine->company_id,
                    'department_id' => $this->log->user->department_id,
                    'sub_department_id' => $this->log->user->sub_department_id,
                    'category_id' => $this->log->user->category_id,
                    'sub_category_id' => $this->log->user->sub_category_id,
                    'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                    'shift_id' => $this->shift?->id,
                    'check_time' => $this->log->datetime,
                    // 'in_time' => null,
                    // 'in_log_id' => null,
                    // 'work_time' => null,
                    'is_locked' => false,
                    'has_error' => false,
                    'remarks' => null,
                ]);
                $this->debug('InOutMuster created : '.$muster->id);
            }
        } else {
            $muster = InOutMuster::year($this->f_years)->updateOrCreate([
                'user_id' => $this->log->user->id,
                'area_id' => $this->area->id,
                'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
                'out_time' => $this->log->datetime,
                'out_log_id' => $this->log->id,
                'is_locked' => false,
            ], [
                'location_id' => $this->machine->location_id,
                'company_id' => $this->machine->company_id,
                'department_id' => $this->log->user->department_id,
                'category_id' => $this->log->user->category_id,
                'sub_category_id' => $this->log->user->sub_category_id,
                'shift_id' => $this->shift?->id,
                'sub_department_id' => $this->log->user->sub_department_id,
                'check_time' => $this->log->datetime,
                // 'in_time' => null,
                // 'in_log_id' => null,
                // 'work_time' => null,
                'is_locked' => false,
                'has_error' => false,
                'remarks' => null,
            ]);
            $this->debug('InOutMuster created : '.$muster->id);
        }
    }

    public function queryFixedOutEntry($year)
    {
        $this->debug('Querying InOutMuster for year '.$year);

        $outmuster = InOutMuster::year($year)
            ->where('user_id', $this->log->user->id)
            ->where('is_locked', false)
            ->where(function (Builder $query) {
                $query->where('check_time', '<=', $this->log->datetime)
                    ->orWhere('out_time', '>=', $this->log->datetime);
            })
            ->where('check_time', '>=', $this->log->datetime->copy()->subDay()->startOfDay())
            ->where(function (Builder $query) {
                $query->whereNULL('out_time')->orWhere('out_time', '<=', $this->log->datetime);
            })
            ->orderBy('check_time', 'desc')
            ->limit(1)
            ->first();
        if ($outmuster) {
            $this->debug('Querying Old InOutMuster found #'.$outmuster->id.'|'.$outmuster->in_time.'|'.$outmuster->out_time.'|'.$outmuster->check_time);
        } else {
            $this->debug('Querying Old InOutMuster not found');
        }

        return $outmuster;
    }

    // TODO:uncomment code is requried visitor entry
    // public function createOrUpdateVisitorInOutMuster(): void
    // {

    //     $this->debug('~~~Creating or updating in out muster');
    //     if ($this->log->is_manual) {
    //         if ($this->log->is_out) {
    //             $this->log->punch_type = '2';
    //             $this->handelFixedVisitorOut();
    //         } else {
    //             $this->log->punch_type = '1';
    //             $this->handelFixedVisitorIn();
    //         }
    //     } elseif ($this->log->punch_type == 'In Device' || $this->log->punch_type == '1') {
    //         $this->handelFixedVisitorIn();
    //     } elseif ($this->log->punch_type == 'Out Device' || $this->log->punch_type == '2') {
    //         $this->handelFixedVisitorOut();
    //     } else {
    //         if ($this->log->is_out) {
    //             $this->handelFixedVisitorOut();
    //         } else {
    //             $this->handelFixedVisitorIn();
    //         }
    //     }
    // }

    // public function handelFixedVisitorIn(): void
    // {
    //     $updating = $this->queryFixedVisitorInEntry($this->f_years);
    //     if ($updating) {
    //         if ($updating->in_time == null && $updating->area_id == $this->area->id) {
    //             $this->debug('Updating VisitorInOutMuster #' . $updating->id);
    //             $updating->in_time = $this->log->datetime;
    //             $updating->in_log_id = $this->log->id;
    //             if ($updating->check_time->lt($this->log->datetime)) {
    //                 $updating->check_time = $this->log->datetime;
    //             }
    //             $updating->work_time = $this->log->datetime->diffInMinutes($updating->out_time);
    //             $updating->save();
    //             $muster = $updating;
    //         } elseif ($updating->in_time != null && $updating->in_time->eq($this->log->datetime) && $updating->area_id == $this->area->id) {
    //             $this->debug('VisitorInOutMuster #' . $updating->id . ' is already updated');
    //             $muster = $updating;
    //         } elseif ($updating->in_time != null && $updating->in_time->lt($this->log->datetime) && $updating->area_id == $this->area->id) {
    //             if ($updating->out_time != null && $updating->out_time->gt($this->log->datetime)) {
    //                 $out_time = $updating->out_time;
    //                 $out_log_id = $updating->out_log_id;
    //                 $updating->check_time = $updating->in_time;
    //                 $updating->out_time = null;
    //                 $updating->out_log_id = null;
    //                 $updating->work_time = null;
    //                 $updating->save();
    //                 $this->debug('Updating VisitorInOutMuster TO OUT BLANK FOR MOVE TO NEW. OLD IS #' . $updating->id);
    //                 $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate(
    //                     [
    //                         'visitor_id' => $this->log->visitor->id,
    //                         'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
    //                         'area_id' => $this->area->id,
    //                         'in_time' => $this->log->datetime,
    //                         'in_log_id' => $this->log->id,
    //                     ],
    //                     [

    //                         'location_id' => $this->log->visitor->location_id,
    //                         'company_id' => $this->log->visitor->company_id,
    //                         'department_id' => $this->log->visitor->department_id,
    //                         'sub_department_id' => $this->log->visitor->sub_department_id,
    //                         'shift_id' => $this->shift?->id,
    //                         'check_time' => $out_time,
    //                         'out_time' => $out_time,
    //                         'out_log_id' => $out_log_id,
    //                         'work_time' => $this->log->datetime->diffInMinutes($out_time),
    //                         'is_locked' => false,
    //                         'has_error' => false,
    //                         'remarks' => null,
    //                     ]
    //                 );
    //                 $this->debug('VisitorInOutMuster created : #' . $muster->id);
    //             } else {
    //                 $this->debug('Creating new VisitorInOutMuster because of in time is less than previous in time');
    //                 $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate([
    //                     'visitor_id' => $this->log->visitor->id,
    //                     'area_id' => $this->area->id,
    //                     'date' => $this->attendance_date, // $this->log->datetime->toDateString(),
    //                     'in_time' => $this->log->datetime,
    //                     'in_log_id' => $this->log->id,
    //                 ], [
    //                     'location_id' => $this->log->visitor->location_id,
    //                     'company_id' => $this->log->visitor->company_id,
    //                     'department_id' => $this->log->visitor->department_id,
    //                     'sub_department_id' => $this->log->visitor->sub_department_id,
    //                     'check_time' => $this->log->datetime,
    //                     'shift_id' => $this->shift?->id,
    //                     // 'out_time' => null,
    //                     // 'out_log_id' => null,
    //                     // 'work_time' => null,
    //                     'is_locked' => false,
    //                     'has_error' => false,
    //                     'remarks' => null,
    //                 ]);
    //                 $this->debug('VisitorInOutMuster created : ' . $muster->id);
    //             }
    //         } else {
    //             $this->debug('Creating new VisitorInOutMuster because of in time is greater than previous in time');
    //             $this->debug('VisitorInOutMuster #' . $updating->id . '|IN:' . $updating->in_time . '|OUT:' . $updating->out_time . '|CHECK:' . $updating->check_time);
    //             $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate([
    //                 'visitor_id' => $this->log->visitor->id,
    //                 'area_id' => $this->area->id,
    //                 'date' => $this->attendance_date, //$this->log->datetime->toDateString(),
    //                 'in_time' => $this->log->datetime,
    //                 'in_log_id' => $this->log->id,
    //             ], [

    //                 'location_id' => $this->log->visitor->location_id,
    //                 'company_id' => $this->log->visitor->company_id,
    //                 'department_id' => $this->log->visitor->department_id,
    //                 'sub_department_id' => $this->log->visitor->sub_department_id,
    //                 'check_time' => $this->log->datetime,
    //                 'shift_id' => $this->shift?->id,
    //                 // 'out_time' => null,
    //                 // 'out_log_id' => null,
    //                 // 'work_time' => null,
    //                 'is_locked' => false,
    //                 'has_error' => false,
    //                 'remarks' => null,
    //             ]);
    //             $this->debug('VisitorInOutMuster created : ' . $muster->id);
    //         }
    //     } else {
    //         $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate([
    //             'visitor_id' => $this->log->visitor->id,
    //             'area_id' => $this->area->id,
    //             'date' => $this->attendance_date, //$this->log->datetime->toDateString(),
    //             'in_time' => $this->log->datetime,
    //             'in_log_id' => $this->log->id,
    //         ], [
    //             'location_id' => $this->log->visitor->location_id,
    //             'company_id' => $this->log->visitor->company_id,
    //             'department_id' => $this->log->visitor->department_id,
    //             'sub_department_id' => $this->log->visitor->sub_department_id,
    //             'shift_id' => $this->shift?->id,
    //             'check_time' => $this->log->datetime,
    //             // 'out_time' => null,
    //             // 'out_log_id' => null,
    //             // 'work_time' => null,
    //             'is_locked' => false,
    //             'has_error' => false,
    //             'remarks' => null,
    //         ]);
    //         $this->debug('VisitorInOutMuster created : ' . $muster->id);
    //     }
    // }

    // public function queryFixedVisitorInEntry($year)
    // {
    //     $inquery =  VisitorInOutMuster::year($year)
    //         ->where('visitor_id', $this->log->visitor->id)
    //         ->where(function (Builder $query) {
    //             $query->where('check_time', '>=', $this->log->datetime)
    //                 ->orWhere('in_time', '<=', $this->log->datetime);
    //         })
    //         ->where('check_time', '<=', $this->log->datetime->copy()->addDay()->endOfDay())
    //         ->where(function (Builder $query) {
    //             $query->whereNULL('in_time')->orWhere('in_time', '<=', $this->log->datetime);
    //         })
    //         ->orderBy('check_time', 'desc')
    //         ->limit(1)
    //         ->first();
    //     // $this->debug('Query:' . DB::getQueryLog()[0]['query'] . ' Bindings:' . json_encode(DB::getQueryLog()[0]['bindings']));
    //     //dd($inquery);
    //     $this->debug("InQueryResult : " . ($inquery ? $inquery->id : "NULL"));
    //     return $inquery;
    // }

    // public function handelFixedVisitorOut()
    // {
    //     $updating = $this->queryFixedVisitorOutEntry($this->f_years);
    //     if ($updating) {
    //         if ($updating->out_time == null  && $updating->area_id == $this->area->id) {
    //             $this->debug('Updating VisitorInOutMuster #' . $updating->id);
    //             $updating->out_time = $this->log->datetime;
    //             $updating->out_log_id = $this->log->id;
    //             if ($updating->check_time->lt($this->log->datetime)) {
    //                 $updating->check_time = $this->log->datetime;
    //             }
    //             $updating->work_time = $updating->in_time->diffInMinutes($this->log->datetime);
    //             $updating->save();
    //             $muster = $updating;
    //         } elseif ($updating->out_time != null && $updating->out_time->eq($this->log->datetime) && $updating->area_id == $this->area->id) {
    //             $this->debug('VisitorInOutMuster #' . $updating->id . ' is already updated');
    //             $muster = $updating;
    //         } elseif ($updating->out_time != null && $updating->out_time->gt($this->log->datetime)  && $updating->area_id == $this->area->id) {
    //             if ($updating->in_time != null && $updating->in_time->lt($this->log->datetime)) {
    //                 $in_time = $updating->in_time;
    //                 $in_log_id = $updating->in_log_id;
    //                 $updating->in_time = null;
    //                 $updating->in_log_id = null;
    //                 $updating->work_time = null;
    //                 $updating->save();
    //                 $this->debug('Updating VisitorInOutMuster TO IN BLANK FOR MOVE TO NEW #' . $updating->id);
    //                 $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate(
    //                     [
    //                         'visitor_id' => $this->log->visitor->id,
    //                         'area_id' => $this->area->id,
    //                         'date' => $this->attendance_date, //$this->log->datetime->toDateString(),
    //                         'out_time' => $this->log->datetime,
    //                         'out_log_id' => $this->log->id,
    //                     ],
    //                     [

    //                         'location_id' => $this->log->visitor->location_id,
    //                         'company_id' => $this->log->visitor->company_id,
    //                         'department_id' => $this->log->visitor->department_id,
    //                         'sub_department_id' => $this->log->visitor->sub_department_id,
    //                         'shift_id' => $this->shift?->id,
    //                         'check_time' => $this->log->datetime,
    //                         'in_time' => $in_time,
    //                         'in_log_id' => $in_log_id,
    //                         'work_time' => $in_time->diffInMinutes($this->log->datetime),
    //                         'is_locked' => false,
    //                         'has_error' => false,
    //                         'remarks' => null,
    //                     ]
    //                 );
    //                 $this->debug('VisitorInOutMuster created : #' . $muster->id);
    //             } else {
    //                 $this->debug('Creating new VisitorInOutMuster because of out time is greater than previous out time');
    //                 $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate([
    //                     'visitor_id' => $this->log->visitor->id,
    //                     'area_id' => $this->area->id,
    //                     'out_time' => $this->log->datetime,
    //                     'out_log_id' => $this->log->id,
    //                 ], [

    //                     'location_id' => $this->log->visitor->location_id,
    //                     'company_id' => $this->log->visitor->company_id,
    //                     'department_id' => $this->log->visitor->department_id,
    //                     'sub_department_id' => $this->log->visitor->sub_department_id,
    //                     'shift_id' => $this->shift?->id,
    //                     'date' => $this->attendance_date, //$this->log->datetime->toDateString(),
    //                     'check_time' => $this->log->datetime,
    //                     // 'in_time' => null,
    //                     // 'in_log_id' => null,
    //                     // 'work_time' => null,
    //                     'is_locked' => false,
    //                     'has_error' => false,
    //                     'remarks' => null,
    //                 ]);
    //                 $this->debug('VisitorInOutMuster created : ' . $muster->id);
    //             }
    //         } else {
    //             $this->debug('Creating new VisitorInOutMuster because of out time is less than previous out time');
    //             $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate([
    //                 'visitor_id' => $this->log->visitor->id,
    //                 'area_id' => $this->area->id,
    //                 'out_time' => $this->log->datetime,
    //                 'out_log_id' => $this->log->id,
    //             ], [

    //                 'location_id' => $this->log->visitor->location_id,
    //                 'company_id' => $this->log->visitor->company_id,
    //                 'department_id' => $this->log->visitor->department_id,
    //                 'sub_department_id' => $this->log->visitor->sub_department_id,
    //                 'date' => $this->attendance_date, //$this->log->datetime->toDateString(),
    //                 'shift_id' => $this->shift?->id,
    //                 'check_time' => $this->log->datetime,
    //                 // 'in_time' => null,
    //                 // 'in_log_id' => null,
    //                 // 'work_time' => null,
    //                 'is_locked' => false,
    //                 'has_error' => false,
    //                 'remarks' => null,
    //             ]);
    //             $this->debug('VisitorInOutMuster created : ' . $muster->id);
    //         }
    //     } else {
    //         $muster = VisitorInOutMuster::year($this->f_years)->updateOrCreate([
    //             'visitor_id' => $this->log->visitor->id,
    //             'area_id' => $this->area->id,
    //             'date' => $this->attendance_date, //$this->log->datetime->toDateString(),
    //             'out_time' => $this->log->datetime,
    //             'out_log_id' => $this->log->id,
    //         ], [
    //             'location_id' => $this->log->visitor->location_id,
    //             'company_id' => $this->log->visitor->company_id,
    //             'department_id' => $this->log->visitor->department_id,
    //             'shift_id' => $this->shift?->id,
    //             'sub_department_id' => $this->log->visitor->sub_department_id,
    //             'check_time' => $this->log->datetime,
    //             // 'in_time' => null,
    //             // 'in_log_id' => null,
    //             // 'work_time' => null,
    //             'is_locked' => false,
    //             'has_error' => false,
    //             'remarks' => null,
    //         ]);
    //         $this->debug('VisitorInOutMuster created : ' . $muster->id);
    //     }
    // }

    // public function queryFixedVisitorOutEntry($year)
    // {
    //     $this->debug('Querying VisitorInOutMuster for year ' . $year);

    //     $outmuster = VisitorInOutMuster::year($year)
    //         ->where('visitor_id', $this->log->visitor->id)
    //         ->where(function (Builder $query) {
    //             $query->where('check_time', '<=', $this->log->datetime)
    //                 ->orWhere('out_time', '>=', $this->log->datetime);
    //         })
    //         ->where('check_time', '>=',  $this->log->datetime->copy()->subDay()->startOfDay())
    //         ->where(function (Builder $query) {
    //             $query->whereNULL('out_time')->orWhere('out_time', '<=', $this->log->datetime);
    //         })
    //         ->orderBy('check_time', 'desc')
    //         ->limit(1)
    //         ->first();
    //     if ($outmuster) {
    //         $this->debug('Querying Old VisitorInOutMuster found #' . $outmuster->id . "|" . $outmuster->in_time . "|" . $outmuster->out_time . "|" . $outmuster->check_time);
    //     } else {
    //         $this->debug('Querying Old VisitorInOutMuster not found');
    //     }
    //     return $outmuster;
    // }
}
