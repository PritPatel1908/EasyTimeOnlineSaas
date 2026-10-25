<?php

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\LeaveEncashment;
use App\Models\Tenant\LeaveLapse;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use UnexpectedValueException;

class CarryForwardLeaveBalance implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels {
        SerializesModels::__unserialize as private restoreSerializedProperties;
    }

    protected $userId;

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
        return (string) $this->userId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->userId = $user->getKey();
    }

    public function __unserialize(array $values): void
    {
        $legacyUserKey = "\0*\0user";
        if (array_key_exists($legacyUserKey, $values)) {
            $legacyUser = $values[$legacyUserKey];
            if (! $legacyUser instanceof ModelIdentifier) {
                throw new UnexpectedValueException('Invalid serialized user in CarryForwardLeaveBalance job.');
            }

            $this->userId = $legacyUser->id;
            unset($values[$legacyUserKey]);
        }

        $this->restoreSerializedProperties($values);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::query()->findOrFail($this->userId);

        DB::beginTransaction();
        try {
            // $userLeaveAccount = $user?->leave_account()->create(['user_id' => $user?->id]);
            $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $user?->id]);
            $user_leave_check_muster = LeaveCheckMuster::where('user_id', $user?->id)
                ->where('is_checked', false)
                ->orderBy('check_date', 'desc')
                ->first();
            // $year = $user_leave_check_muster?->check_date?->format('Y');

            if ($user_leave_check_muster !== null) {
                if ($user?->grade_wise_leave?->is_monthly) {
                    if ($user?->grade_wise_leave?->grade_wise_monthly_leave_details != null && count($user?->grade_wise_leave?->grade_wise_monthly_leave_details) > 0) {
                        foreach ($user?->grade_wise_leave?->grade_wise_monthly_leave_details as $grade_wise_detail) {
                            if ($grade_wise_detail->allow_c_f) {
                                GeneralHelper::handleCarryForwardLeave(
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $user?->join_date,
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    $grade_wise_detail->leave_type_id,
                                    get_class($user),
                                    $user?->id,
                                    // Carbon::today(),
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $user?->join_date,
                                    $user?->id,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is debit, due to carry forward leave balance to next month.',
                                    $user?->id,
                                    $user?->id,
                                    true,
                                    false
                                );
                            } else {
                                if ($grade_wise_detail->allow_leave_encashment) {
                                    GeneralHelper::handleCarryForwardLeave(
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $user?->join_date,
                                        get_class($userLeaveAccount),
                                        $userLeaveAccount->id,
                                        get_class($grade_wise_detail),
                                        $grade_wise_detail->id,
                                        $grade_wise_detail->leave_type_id,
                                        get_class($user),
                                        $user?->id,
                                        // Carbon::today(),
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $user?->join_date,
                                        $user?->id,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is debit, due to leave encashment.',
                                        $user?->id,
                                        $user?->id,
                                        false,
                                        false
                                    );

                                    LeaveEncashment::updateOrCreate(
                                        [
                                            'date' => $user_leave_check_muster?->check_date ? Carbon::parse($user_leave_check_muster?->check_date)?->subDay()->setTime(23, 59, 59, 000000) : Carbon::parse($user?->join_date)?->setTime(23, 59, 59, 000000),
                                            'user_id' => $user?->id,
                                            'leave_type_id' => $grade_wise_detail->leave_type_id,
                                        ],
                                        [
                                            'lapse_count' => $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        ]
                                    );

                                    // TODO: Add approval to authorize person
                                } else {
                                    GeneralHelper::handleCarryForwardLeave(
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $user?->join_date,
                                        get_class($userLeaveAccount),
                                        $userLeaveAccount->id,
                                        get_class($grade_wise_detail),
                                        $grade_wise_detail->id,
                                        $grade_wise_detail->leave_type_id,
                                        get_class($user),
                                        $user?->id,
                                        // Carbon::today(),
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $user?->join_date,
                                        $user?->id,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is debit, due to leave lapse.',
                                        $user?->id,
                                        $user?->id,
                                        false,
                                        false
                                    );

                                    LeaveLapse::updateOrCreate(
                                        [
                                            'date' => $user_leave_check_muster?->check_date ? Carbon::parse($user_leave_check_muster?->check_date)?->subDay()->setTime(23, 59, 59, 000000) : Carbon::parse($this->user?->join_date)?->setTime(23, 59, 59, 000000),
                                            'user_id' => $this->user?->id,
                                            'leave_type_id' => $grade_wise_detail->leave_type_id,
                                        ],
                                        [
                                            'lapse_count' => $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        ]
                                    );

                                    // TODO: Add approval to authorize person
                                }
                            }
                        }

                        foreach ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details as $grade_wise_detail) {
                            if ($grade_wise_detail->allow_c_f) {
                                GeneralHelper::handleCarryForwardLeave(
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    $grade_wise_detail->leave_type_id,
                                    get_class($this->user),
                                    $this->user?->id,
                                    // Carbon::today(),
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                    $this->user?->id,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is credit, due to carry forward leave balance to this month.',
                                    $this->user?->id,
                                    $this->user?->id,
                                    true,
                                    true
                                );
                            }
                        }
                    }
                } else {
                    if ($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details != null && count($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details) > 0) {
                        foreach ($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details as $grade_wise_detail) {
                            if ($grade_wise_detail->allow_c_f) {
                                GeneralHelper::handleCarryForwardLeave(
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    get_class($this->user),
                                    $grade_wise_detail->leave_type_id,
                                    $this->user?->id,
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                    $this->user?->id,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is debit, due to carry forward leave balance to next year.',
                                    $this->user?->id,
                                    $this->user?->id,
                                    true,
                                    false
                                );
                            } else {
                                if ($grade_wise_detail->allow_leave_encashment) {
                                    GeneralHelper::handleCarryForwardLeave(
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                        get_class($userLeaveAccount),
                                        $userLeaveAccount->id,
                                        get_class($grade_wise_detail),
                                        $grade_wise_detail->id,
                                        $grade_wise_detail->leave_type_id,
                                        get_class($this->user),
                                        $this->user?->id,
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                        $this->user?->id,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is debit, due to leave encashment.',
                                        $this->user?->id,
                                        $this->user?->id,
                                        false,
                                        false
                                    );

                                    LeaveEncashment::updateOrCreate(
                                        [
                                            'date' => $user_leave_check_muster?->check_date ? Carbon::parse($user_leave_check_muster?->check_date)?->subDay()->setTime(23, 59, 59, 000000) : Carbon::parse($this->user?->join_date)?->setTime(23, 59, 59, 000000),
                                            'user_id' => $this->user?->id,
                                            'leave_type_id' => $grade_wise_detail->leave_type_id,
                                        ],
                                        [
                                            'lapse_count' => $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        ]
                                    );

                                    // TODO: Add approval to authorize person
                                } else {
                                    GeneralHelper::handleCarryForwardLeave(
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                        get_class($userLeaveAccount),
                                        $userLeaveAccount->id,
                                        get_class($grade_wise_detail),
                                        $grade_wise_detail->id,
                                        $grade_wise_detail->leave_type_id,
                                        get_class($this->user),
                                        $this->user?->id,
                                        // Carbon::today(),
                                        $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                        $this->user?->id,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is debit, due to leave lapse.',
                                        $this->user?->id,
                                        $this->user?->id,
                                        false,
                                        false
                                    );

                                    LeaveLapse::updateOrCreate(
                                        [
                                            'date' => $user_leave_check_muster?->check_date ? Carbon::parse($user_leave_check_muster?->check_date)?->subDay()->setTime(23, 59, 59, 000000) : Carbon::parse($this->user?->join_date)?->setTime(23, 59, 59, 000000),
                                            'user_id' => $this->user?->id,
                                            'leave_type_id' => $grade_wise_detail->leave_type_id,
                                        ],
                                        [
                                            'lapse_count' => $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                        ]
                                    );

                                    // TODO: Add approval to authorize person
                                }
                            }
                        }

                        foreach ($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details as $grade_wise_detail) {
                            if ($grade_wise_detail->allow_c_f) {
                                GeneralHelper::handleCarryForwardLeave(
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    $grade_wise_detail->leave_type_id,
                                    get_class($this->user),
                                    $this->user?->id,
                                    $user_leave_check_muster?->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date,
                                    $this->user?->id,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance,
                                    $userLeaveAccount?->leave_account_details?->where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()?->balance.' leave is credit, due to carry forward leave balance to this year.',
                                    $this->user?->id,
                                    $this->user?->id,
                                    true,
                                    true
                                );
                            }
                        }
                    }
                }
            } else {
                Log::channel('attprocess')->debug('Not have any leave to carry forward for | '.$this->user?->name($this->user?->code));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Carry Forward Leave Balance. '.Str::limit($e->getMessage(), 200));
        }
    }
}
