<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApproveLeaveApplication implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $leave_application;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    // public function uniqueId(): string
    // {
    //     return $this->user->id;
    // }

    /**
     * Create a new job instance.
     */
    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leave_application = $leaveApplication;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            $this->leave_application->user = User::find($this->leave_application->user_id);
            if ($this->leave_application->is_only_second_half) {
                if ($this->leave_application->user?->grade_wise_leave?->is_monthly) {
                    $grade_wise_detail = $this->leave_application->user?->grade_wise_leave?->grade_wise_monthly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application->leave_type_id) {
                        if ($grade_wise_detail->is_paid) {
                            StatusMuster::where('user_id', $this->leave_application->user?->id)
                                ->where('date', $this->leave_application?->from_date)
                                ->where('is_locked', false)
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->leave_type->code)->first()->id,
                                    'day_count' => '0.5',
                                ]);
                            self::createAttendance($this->leave_application, null, $this->leave_application->user, true, false);
                        } else {
                            StatusMuster::where('user_id', $this->leave_application->user?->id)
                                ->where('date', $this->leave_application?->from_date)
                                ->where('is_locked', false)
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->leave_type->code)->first()->id,
                                    'day_count' => '0.5',
                                ]);
                            self::createAttendance($this->leave_application, null, $this->leave_application->user, true, false);
                        }
                    }
                } else {
                    $grade_wise_detail = $this->leave_application->user?->grade_wise_leave?->grade_wise_yearly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application->leave_type_id) {
                        if ($grade_wise_detail->is_paid) {
                            StatusMuster::where('user_id', $this->leave_application->user?->id)
                                ->where('date', $this->leave_application?->from_date)
                                ->where('is_locked', false)
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->leave_type->code)->first()->id,
                                    'day_count' => '0.5',
                                ]);
                            self::createAttendance($this->leave_application, null, $this->leave_application->user, true, false);
                        } else {
                            StatusMuster::where('user_id', $this->leave_application->user?->id)
                                ->where('date', $this->leave_application?->from_date)
                                ->where('is_locked', false)
                                ->update([
                                    'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->leave_type->code)->first()->id,
                                    'day_count' => '0.5',
                                ]);
                            self::createAttendance($this->leave_application, null, $this->leave_application->user, true, false);
                        }
                    }
                }
            } else {
                if ($this->leave_application->user?->grade_wise_leave?->is_monthly) {
                    $grade_wise_detail = $this->leave_application->user?->grade_wise_leave?->grade_wise_monthly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application->leave_type_id) {
                        if ($grade_wise_detail->is_paid) {
                            if ($grade_wise_detail->allow_sandwich) {
                                $weekoffOrHolidayDates = StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where('is_locked', false)
                                    ->where(function ($query) {
                                        $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                    })
                                    ->pluck('date');

                                StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->whereNotIn('date', $weekoffOrHolidayDates)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '1',
                                    ]);

                                if ($weekoffOrHolidayDates->isNotEmpty()) {
                                    StatusMuster::where('user_id', $this->leave_application->user?->id)
                                        ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                        ->whereIn('date', $weekoffOrHolidayDates)
                                        ->where('is_locked', false)
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code)->first()->id,
                                            'day_count' => '1',
                                        ]);
                                }

                                self::createAttendance($this->leave_application, $weekoffOrHolidayDates->toArray(), $this->leave_application->user, true, true);
                            } else {
                                $dates = [];
                                for ($date = $this->leave_application?->from_date; $date <= $this->leave_application?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                                    if (! self::isWeekoffOrHoliday($date, $this->leave_application->user?->id)) {
                                        $dates[] = $date;
                                    }
                                }
                                StatusMuster::whereIn('user_id', [$this->leave_application->user?->id])
                                    ->whereIn('date', $dates)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '1',
                                    ]);

                                self::createAttendance($this->leave_application, $dates, $this->leave_application->user, true, false);
                            }

                            if ($this->leave_application?->is_second_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('date', $this->leave_application?->from_date)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->sandwich_leave->code)->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }

                            if ($this->leave_application?->is_first_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('date', $this->leave_application?->to_date)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code.'A')->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }
                        } else {
                            if ($grade_wise_detail->allow_sandwich) {
                                $weekoffOrHolidayDates = StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where('is_locked', false)
                                    ->where(function ($query) {
                                        $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                    })
                                    ->pluck('date');

                                StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where('is_locked', false)
                                    ->whereNotIn('date', $weekoffOrHolidayDates)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '0',
                                    ]);

                                if ($weekoffOrHolidayDates->isNotEmpty()) {
                                    StatusMuster::where('user_id', $this->leave_application->user?->id)
                                        ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                        ->where('is_locked', false)
                                        ->whereIn('date', $weekoffOrHolidayDates)
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code)->first()->id,
                                            'day_count' => '0',
                                        ]);
                                }

                                self::createAttendance($this->leave_application, $weekoffOrHolidayDates->toArray(), $this->leave_application->user, true, true);
                            } else {
                                $dates = [];
                                for ($date = $this->leave_application?->from_date; $date <= $this->leave_application?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                                    if (! self::isWeekoffOrHoliday($date, $this->leave_application->user?->id)) {
                                        $dates[] = $date;
                                    }
                                }
                                StatusMuster::whereIn('user_id', [$this->leave_application->user?->id])
                                    ->whereIn('date', $dates)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '0',
                                    ]);

                                self::createAttendance($this->leave_application, $dates, $this->leave_application->user, true, false);
                            }

                            if ($this->leave_application?->is_second_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('date', $this->leave_application?->from_date)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->sandwich_leave->code)->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }

                            if ($this->leave_application?->is_first_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('date', $this->leave_application?->to_date)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code.'A')->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }
                        }
                    }
                } else {
                    $grade_wise_detail = $this->leave_application->user?->grade_wise_leave?->grade_wise_yearly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application->leave_type_id) {
                        if ($grade_wise_detail->is_paid) {
                            if ($grade_wise_detail->allow_sandwich) {
                                $weekoffOrHolidayDates = StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where(function ($query) {
                                        $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                    })
                                    ->pluck('date');

                                StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->whereNotIn('date', $weekoffOrHolidayDates)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '1',
                                    ]);

                                if ($weekoffOrHolidayDates->isNotEmpty()) {
                                    StatusMuster::where('user_id', $this->leave_application->user?->id)
                                        ->where('is_locked', false)
                                        ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                        ->whereIn('date', $weekoffOrHolidayDates)
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code)->first()->id,
                                            'day_count' => '1',
                                        ]);
                                }

                                self::createAttendance($this->leave_application, $weekoffOrHolidayDates->toArray(), $this->leave_application->user, false, true);
                            } else {
                                $dates = [];
                                for ($date = $this->leave_application?->from_date; $date <= $this->leave_application?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                                    if (! self::isWeekoffOrHoliday($date, $this->leave_application->user?->id)) {
                                        $dates[] = $date;
                                    }
                                }
                                StatusMuster::whereIn('user_id', [$this->leave_application->user?->id])
                                    ->whereIn('date', $dates)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '1',
                                    ]);

                                self::createAttendance($this->leave_application, $dates, $this->leave_application->user, false, false);
                            }

                            if ($this->leave_application?->is_second_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('date', $this->leave_application?->from_date)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->sandwich_leave->code)->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }

                            if ($this->leave_application?->is_first_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('date', $this->leave_application?->to_date)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code.'A')->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }
                        } else {
                            if ($grade_wise_detail->allow_sandwich) {
                                $weekoffOrHolidayDates = StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->where('is_locked', false)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where(function ($query) {
                                        $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                    })
                                    ->pluck('date');

                                StatusMuster::where('user_id', $this->leave_application->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where('is_locked', false)
                                    ->whereNotIn('date', $weekoffOrHolidayDates)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '0',
                                    ]);

                                if ($weekoffOrHolidayDates->isNotEmpty()) {
                                    StatusMuster::where('user_id', $this->leave_application->user?->id)
                                        ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                        ->where('is_locked', false)
                                        ->whereIn('date', $weekoffOrHolidayDates)
                                        ->update([
                                            'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code)->first()->id,
                                            'day_count' => '0',
                                        ]);
                                }

                                self::createAttendance($this->leave_application, $weekoffOrHolidayDates->toArray(), $this->leave_application->user, false, true);
                            } else {
                                $dates = [];
                                for ($date = $this->leave_application?->from_date; $date <= $this->leave_application?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                                    if (! self::isWeekoffOrHoliday($date, $this->leave_application->user?->id)) {
                                        $dates[] = $date;
                                    }
                                }
                                StatusMuster::whereIn('user_id', [$this->leave_application->user?->id])
                                    ->whereIn('date', $dates)
                                    ->where('is_locked', false)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->leave_type->code)->first()->id,
                                        'day_count' => '0',
                                    ]);

                                self::createAttendance($this->leave_application, $dates, $this->leave_application->user, false, false);
                            }

                            if ($this->leave_application?->is_second_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('is_locked', false)
                                    ->where('date', $this->leave_application?->from_date)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', 'A'.$this->leave_application->sandwich_leave->code)->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }

                            if ($this->leave_application?->is_first_half) {
                                StatusMuster::where('user_id', [$this->leave_application->user?->id])
                                    ->where('is_locked', false)
                                    ->where('date', $this->leave_application?->to_date)
                                    ->update([
                                        'status_master_id' => StatusMaster::where('code', $this->leave_application->sandwich_leave->code.'A')->first()->id,
                                        'day_count' => '0.5',
                                    ]);
                            }
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Leave Application. '.Str::limit($e->getMessage(), 200));
            // TODO:comment this line in production
            // throw ($e);
            // return false`;
        }
    }

    public static function isWeekoffOrHoliday($date, $user_id)
    {
        $weekoff_holiday = StatusMuster::where('user_id', $user_id)
            ->where('date', $date)
            ->where('is_locked', false)
            ->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id)
            ->count();
        if ($weekoff_holiday > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function createAttendance($record, $weekoffOrHolidayDatesArray, $user, $isMonthly = true, $isSandwich = false)
    {
        if ($isMonthly) {
            if ($record->is_only_second_half) {
                $attendance = (new Attendance(year: Carbon::parse($record->from_date)?->format('Y')));
                $attendance->setDynamicTable(Carbon::parse($record->from_date)?->format('Y'));
                $attendance = $attendance->updateOrCreate([
                    'user_id' => $user?->id,
                    // 'location_id' => $user?->location_id,
                    // 'company_id' => $user?->company_id,
                    // 'department_id' => $user?->department_id,
                    // 'sub_department_id' => $user?->sub_department_id,
                    // 'category_id' => $user?->category_id,
                    // 'sub_category_id' => $user?->sub_category_id,
                    'date' => $record->from_date,
                ], [
                    'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                ]);
            } else {
                if ($isSandwich) {
                    if ($weekoffOrHolidayDatesArray != null) {
                        for ($date = $record?->from_date; $date <= $record?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                            if (in_array($date, $weekoffOrHolidayDatesArray)) {
                                $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                                $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                                if ($date == $record?->from_date && $record->is_second_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->sandwich_leave->code)->first()->id,
                                    ]);
                                } elseif ($date == $record?->to_date && $record->is_first_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->sandwich_leave->code)->first()->id.'A',
                                    ]);
                                } else {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->sandwich_leave->code)->first()->id,
                                    ]);
                                }
                            } else {
                                $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                                $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                                if ($date == $record?->from_date && $record->is_second_half) {
                                    $attendance = $attendance->updateOrCreate(
                                        [
                                            'user_id' => $user?->id,
                                            // 'location_id' => $user?->location_id,
                                            // 'company_id' => $user?->company_id,
                                            // 'department_id' => $user?->department_id,
                                            // 'sub_department_id' => $user?->sub_department_id,
                                            // 'category_id' => $user?->category_id,
                                            // 'sub_category_id' => $user?->sub_category_id,
                                            'date' => $date,
                                        ],
                                        [
                                            'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                        ]
                                    );
                                } elseif ($date == $record?->from_date && $record->is_first_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id.'A',
                                    ]);
                                } else {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                    ]);
                                }
                            }
                        }
                    } else {
                        for ($date = $record?->from_date; $date <= $record?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                            $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                            $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                            if ($date == $record?->from_date && $record->is_second_half) {
                                $attendance = $attendance->updateOrCreate([
                                    'user_id' => $user?->id,
                                    // 'location_id' => $user?->location_id,
                                    // 'company_id' => $user?->company_id,
                                    // 'department_id' => $user?->department_id,
                                    // 'sub_department_id' => $user?->sub_department_id,
                                    // 'category_id' => $user?->category_id,
                                    // 'sub_category_id' => $user?->sub_category_id,
                                    'date' => $date,
                                ], [
                                    'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                ]);
                            } elseif ($date == $record?->from_date && $record->is_first_half) {
                                $attendance = $attendance->updateOrCreate([
                                    'user_id' => $user?->id,
                                    // 'location_id' => $user?->location_id,
                                    // 'company_id' => $user?->company_id,
                                    // 'department_id' => $user?->department_id,
                                    // 'sub_department_id' => $user?->sub_department_id,
                                    // 'category_id' => $user?->category_id,
                                    // 'sub_category_id' => $user?->sub_category_id,
                                    'date' => $date,
                                ], [
                                    'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id.'A',
                                ]);
                            } else {
                                $attendance = $attendance->updateOrCreate([
                                    'user_id' => $user?->id,
                                    // 'location_id' => $user?->location_id,
                                    // 'company_id' => $user?->company_id,
                                    // 'department_id' => $user?->department_id,
                                    // 'sub_department_id' => $user?->sub_department_id,
                                    // 'category_id' => $user?->category_id,
                                    // 'sub_category_id' => $user?->sub_category_id,
                                    'date' => $date,
                                ], [
                                    'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                ]);
                            }
                        }
                    }
                } else {
                    for ($date = $record?->from_date; $date <= $record?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                        $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                        $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                        if ($date == $record?->from_date && $record->is_second_half) {
                            $attendance = $attendance->updateOrCreate([
                                'user_id' => $user?->id,
                                // 'location_id' => $user?->location_id,
                                // 'company_id' => $user?->company_id,
                                // 'department_id' => $user?->department_id,
                                // 'sub_department_id' => $user?->sub_department_id,
                                // 'category_id' => $user?->category_id,
                                // 'sub_category_id' => $user?->sub_category_id,
                                'date' => $date,
                            ], [
                                'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                            ]);
                        } elseif ($date == $record?->from_date && $record->is_first_half) {
                            $attendance = $attendance->updateOrCreate([
                                'user_id' => $user?->id,
                                // 'location_id' => $user?->location_id,
                                // 'company_id' => $user?->company_id,
                                // 'department_id' => $user?->department_id,
                                // 'sub_department_id' => $user?->sub_department_id,
                                // 'category_id' => $user?->category_id,
                                // 'sub_category_id' => $user?->sub_category_id,
                                'date' => $date,
                            ], [
                                'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id.'A',
                            ]);
                        } else {
                            $attendance = $attendance->updateOrCreate([
                                'user_id' => $user?->id,
                                // 'location_id' => $user?->location_id,
                                // 'company_id' => $user?->company_id,
                                // 'department_id' => $user?->department_id,
                                // 'sub_department_id' => $user?->sub_department_id,
                                // 'category_id' => $user?->category_id,
                                // 'sub_category_id' => $user?->sub_category_id,
                                'date' => $date,
                            ], [
                                'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_monthly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                            ]);
                        }
                    }
                }
            }
        } else {
            if ($record->is_only_second_half) {
                $attendance = (new Attendance(year: Carbon::parse($record->from_date)?->format('Y')));
                $attendance->setDynamicTable(Carbon::parse($record->from_date)?->format('Y'));
                $attendance = $attendance->updateOrCreate([
                    'user_id' => $user?->id,
                    // 'location_id' => $user?->location_id,
                    // 'company_id' => $user?->company_id,
                    // 'department_id' => $user?->department_id,
                    // 'sub_department_id' => $user?->sub_department_id,
                    // 'category_id' => $user?->category_id,
                    // 'sub_category_id' => $user?->sub_category_id,
                    'date' => $record->from_date,
                ], [
                    'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                ]);
            } else {
                if ($isSandwich) {
                    if ($weekoffOrHolidayDatesArray != null) {
                        for ($date = $record?->from_date; $date <= $record?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                            if (in_array($date, $weekoffOrHolidayDatesArray)) {
                                $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                                $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                                if ($date == $record?->from_date && $record->is_second_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->sandwich_leave->code)->first()->id,
                                    ]);
                                } elseif ($date == $record?->to_date && $record->is_first_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->sandwich_leave->code)->first()->id.'A',
                                    ]);
                                } else {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->sandwich_leave->code)->first()->id,
                                    ]);
                                }
                            } else {
                                $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                                $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                                if ($date == $record?->from_date && $record->is_second_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                    ]);
                                } elseif ($date == $record?->from_date && $record->is_first_half) {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id.'A',
                                    ]);
                                } else {
                                    $attendance = $attendance->updateOrCreate([
                                        'user_id' => $user?->id,
                                        // 'location_id' => $user?->location_id,
                                        // 'company_id' => $user?->company_id,
                                        // 'department_id' => $user?->department_id,
                                        // 'sub_department_id' => $user?->sub_department_id,
                                        // 'category_id' => $user?->category_id,
                                        // 'sub_category_id' => $user?->sub_category_id,
                                        'date' => $date,
                                    ], [
                                        'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                    ]);
                                }
                            }
                        }
                    } else {
                        for ($date = $record?->from_date; $date <= $record?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                            $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                            $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                            if ($date == $record?->from_date && $record->is_second_half) {
                                $attendance = $attendance->updateOrCreate([
                                    'user_id' => $user?->id,
                                    // 'location_id' => $user?->location_id,
                                    // 'company_id' => $user?->company_id,
                                    // 'department_id' => $user?->department_id,
                                    // 'sub_department_id' => $user?->sub_department_id,
                                    // 'category_id' => $user?->category_id,
                                    // 'sub_category_id' => $user?->sub_category_id,
                                    'date' => $date,
                                ], [
                                    'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                ]);
                            } elseif ($date == $record?->from_date && $record->is_first_half) {
                                $attendance = $attendance->updateOrCreate([
                                    'user_id' => $user?->id,
                                    // 'location_id' => $user?->location_id,
                                    // 'company_id' => $user?->company_id,
                                    // 'department_id' => $user?->department_id,
                                    // 'sub_department_id' => $user?->sub_department_id,
                                    // 'category_id' => $user?->category_id,
                                    // 'sub_category_id' => $user?->sub_category_id,
                                    'date' => $date,
                                ], [
                                    'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id.'A',
                                ]);
                            } else {
                                $attendance = $attendance->updateOrCreate([
                                    'user_id' => $user?->id,
                                    // 'location_id' => $user?->location_id,
                                    // 'company_id' => $user?->company_id,
                                    // 'department_id' => $user?->department_id,
                                    // 'sub_department_id' => $user?->sub_department_id,
                                    // 'category_id' => $user?->category_id,
                                    // 'sub_category_id' => $user?->sub_category_id,
                                    'date' => $date,
                                ], [
                                    'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                                ]);
                            }
                        }
                    }
                } else {
                    for ($date = $record?->from_date; $date <= $record?->to_date; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
                        $attendance = (new Attendance(year: Carbon::parse($date)?->format('Y')));
                        $attendance->setDynamicTable(Carbon::parse($date)?->format('Y'));
                        if ($date == $record?->from_date && $record->is_second_half) {
                            $attendance = $attendance->updateOrCreate([
                                'user_id' => $user?->id,
                                // 'location_id' => $user?->location_id,
                                // 'company_id' => $user?->company_id,
                                // 'department_id' => $user?->department_id,
                                // 'sub_department_id' => $user?->sub_department_id,
                                // 'category_id' => $user?->category_id,
                                // 'sub_category_id' => $user?->sub_category_id,
                                'date' => $date,
                            ], [
                                'status_master_id' => StatusMaster::where('code', 'A'.$user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                            ]);
                        } elseif ($date == $record?->from_date && $record->is_first_half) {
                            $attendance = $attendance->updateOrCreate([
                                'user_id' => $user?->id,
                                // 'location_id' => $user?->location_id,
                                // 'company_id' => $user?->company_id,
                                // 'department_id' => $user?->department_id,
                                // 'sub_department_id' => $user?->sub_department_id,
                                // 'category_id' => $user?->category_id,
                                // 'sub_category_id' => $user?->sub_category_id,
                                'date' => $date,
                            ], [
                                'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id.'A',
                            ]);
                        } else {
                            $attendance = $attendance->updateOrCreate([
                                'user_id' => $user?->id,
                                // 'location_id' => $user?->location_id,
                                // 'company_id' => $user?->company_id,
                                // 'department_id' => $user?->department_id,
                                // 'sub_department_id' => $user?->sub_department_id,
                                // 'category_id' => $user?->category_id,
                                // 'sub_category_id' => $user?->sub_category_id,
                                'date' => $date,
                            ], [
                                'status_master_id' => StatusMaster::where('code', $user?->grade_wise_leave?->grade_wise_yearly_leave_details?->where('leave_type_id', $record?->leave_type_id)?->first()?->leave_type->code)->first()->id,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
