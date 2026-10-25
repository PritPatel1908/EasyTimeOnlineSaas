<?php

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DebitLeaveApplication implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $leave_application;

    protected $sandwich_leave = false;

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
        return (string) $this->leave_application->id;
    }

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
            $application = LeaveApplication::withTrashed()
                ->with(['users', 'approval_statuses'])
                ->findOrFail($this->leave_application->id);

            if ($application->trashed()
                || $application->approval_statuses->contains('approval_status', 'rejected')) {
                DB::commit();

                return;
            }

            $this->leave_application = $application;
            GeneralHelper::clearLeaveApplicationBalance(
                $application,
                $application->users->pluck('id')->map(static fn (mixed $userId): int => (int) $userId)->all(),
                $application->updated_by ?? $application->created_by,
            );

            // $userLeaveAccount = $user->leave_account()->create(['user_id' => $user->id]);
            $this->leave_application->loadMissing('users');

            foreach ($this->leave_application->users as $user) {
                $this->sandwich_leave = false;

            if ($this->leave_application?->is_only_half) {
                $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $user?->id]);
                $application_leave_count = 0.5;

                if ($user?->grade_wise_leave?->is_monthly) {
                    $grade_wise_detail = $user?->grade_wise_leave?->grade_wise_monthly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                        $adjusted_leave_count = $application_leave_count;
                        $this->sandwich_leave = false;

                        $this->makeTransaction(
                            $this->leave_application,
                            $userLeaveAccount,
                            $user,
                            $grade_wise_detail,
                            [],
                            $adjusted_leave_count,
                            $this->sandwich_leave,
                            null
                        );
                    }
                } else {
                    $grade_wise_detail = $user?->grade_wise_leave?->grade_wise_yearly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                        $adjusted_leave_count = $application_leave_count;
                        $this->sandwich_leave = false;

                        $this->makeTransaction(
                            $this->leave_application,
                            $userLeaveAccount,
                            $user,
                            $grade_wise_detail,
                            [],
                            $adjusted_leave_count,
                            $this->sandwich_leave,
                            null
                        );
                    }
                }
            } else {
                $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $user?->id]);
                $application_leave_count = $this->leave_application?->from_date->diffInDays($this->leave_application?->to_date) + 1;

                if ($this->leave_application?->is_second_half) {
                    $application_leave_count = $application_leave_count - 0.5;
                }

                if ($this->leave_application?->is_first_half) {
                    $application_leave_count = $application_leave_count - 0.5;
                }

                $weekoffOrHolidayDates = collect();
                if ($user?->grade_wise_leave?->is_monthly) {
                    $grade_wise_detail = $user?->grade_wise_leave?->grade_wise_monthly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                        if ($grade_wise_detail->allow_sandwich) {
                            if ($grade_wise_detail->leave_type_id == $grade_wise_detail->sandwich_leave_id) {
                                $adjusted_leave_count = $application_leave_count;
                                $this->sandwich_leave = false;
                            } else {
                                $weekoffOrHolidayDates = StatusMuster::where('user_id', $user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where(function ($query) {
                                        $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                    })
                                    ->pluck('date');
                                $adjusted_leave_count = $application_leave_count - $weekoffOrHolidayDates->count();
                                if ($weekoffOrHolidayDates->count()) {
                                    $this->sandwich_leave = true;
                                } else {
                                    $this->sandwich_leave = false;
                                }
                            }
                        } else {
                            $weekOffsAndHolidays = StatusMuster::where('user_id', $user?->id)
                                ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                ->where(function ($query) {
                                    $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                        ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                })
                                // ->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                // ->where('status_master_id', StatusMaster::where('code', 'HL')->first()->id)
                                ->count();

                            $adjusted_leave_count = $application_leave_count - $weekOffsAndHolidays;
                        }

                        $gradeWiseLeaveDetail = GradeWiseMonthlyLeaveDetail::where('grade_wise_leave_id', $grade_wise_detail->grade_wise_leave_id)->where('leave_type_id', $grade_wise_detail->sandwich_leave_id)->first();

                        $this->makeTransaction(
                            $this->leave_application,
                            $userLeaveAccount,
                            $user,
                            $grade_wise_detail,
                            $weekoffOrHolidayDates,
                            $adjusted_leave_count,
                            $this->sandwich_leave,
                            $gradeWiseLeaveDetail
                        );
                    }
                } else {
                    $grade_wise_detail = $user?->grade_wise_leave?->grade_wise_yearly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                        if ($grade_wise_detail->allow_sandwich) {
                            if ($grade_wise_detail->leave_type_id == $grade_wise_detail->sandwich_leave_id) {
                                $adjusted_leave_count = $application_leave_count;
                                $this->sandwich_leave = false;
                            } else {
                                $weekoffOrHolidayDates = StatusMuster::where('user_id', $user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where(function ($query) {
                                        $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                            ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                    })
                                    ->pluck('date');
                                $adjusted_leave_count = $application_leave_count - $weekoffOrHolidayDates->count();
                                if ($weekoffOrHolidayDates->count()) {
                                    $this->sandwich_leave = true;
                                } else {
                                    $this->sandwich_leave = false;
                                }
                            }
                        } else {
                            $weekOffsAndHolidays = StatusMuster::where('user_id', $user?->id)
                                ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                ->where(function ($query) {
                                    $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                        ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                })
                                // ->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                // ->where('status_master_id', StatusMaster::where('code', 'HL')->first()->id)
                                ->count();

                            $adjusted_leave_count = $application_leave_count - $weekOffsAndHolidays;
                        }

                        $gradeWiseLeaveDetail = GradeWiseYearlyLeaveDetail::where('grade_wise_leave_id', $grade_wise_detail->grade_wise_leave_id)->where('leave_type_id', $grade_wise_detail->sandwich_leave_id)->first();

                        $this->makeTransaction(
                            $this->leave_application,
                            $userLeaveAccount,
                            $user,
                            $grade_wise_detail,
                            $weekoffOrHolidayDates,
                            $adjusted_leave_count,
                            $this->sandwich_leave,
                            $gradeWiseLeaveDetail
                        );
                    }
                }
            }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Leave Application. '.Str::limit($e->getMessage(), 200));
            throw $e;
        }
    }

    public static function makeTransaction(
        $application,
        $userLeaveAccount,
        $user,
        $grade_wise_detail,
        $weekoffOrHolidayDates,
        $adjusted_leave_count,
        $sandwich_leave,
        $gradeWiseLeaveDetail
    ) {
        GeneralHelper::createOrUpdateTransaction(
            $application?->created_at?->format('Y'),
            get_class($userLeaveAccount),
            $userLeaveAccount->id,
            LeaveApplication::class,
            (int) $application->id,
            $grade_wise_detail?->leave_type_id,
            get_class($user),
            $user?->id,
            $application?->created_at,
            $user?->id,
            0,
            $adjusted_leave_count,
            'Debit #'.$grade_wise_detail?->leave_type->code.' '.$application?->created_at,
            $user->id,
            $user->id,
            false
        );

        if ($sandwich_leave) {
            GeneralHelper::createOrUpdateTransaction(
                $application?->created_at?->format('Y'),
                get_class($userLeaveAccount),
                (int) $userLeaveAccount->id,
                LeaveApplication::class,
                (int) $application->id,
                (int) $grade_wise_detail?->sandwich_leave_id,
                get_class($user),
                (int) $user->id,
                $application?->created_at,
                (int) $user->id,
                0,
                $weekoffOrHolidayDates->count(),
                'Debit #'.$grade_wise_detail->sandwich_leave->code.' '.$application?->created_at,
                (int) $user->id,
                (int) $user->id,
                false,
            );
        }
    }
}
