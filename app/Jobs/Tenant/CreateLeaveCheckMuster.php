<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\User;
use App\Support\LeaveRuleCadence;
use App\Support\MonthlyLeaveCheckDate;
use App\Support\YearlyLeaveCredit;
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
use Throwable;
use UnexpectedValueException;

class CreateLeaveCheckMuster implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels {
        SerializesModels::__unserialize as private restoreSerializedProperties;
    }

    protected $userId;

    protected ?string $tenantId = null;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 7200;

    public $timeout = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return ($this->tenantId ?? 'central').':'.($this->userId ?? 'all-employees');
    }

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->userId = $user->getKey();
        $this->tenantId = tenant()?->getTenantKey();
    }

    public function __unserialize(array $values): void
    {
        $legacyUserKey = "\0*\0user";
        if (array_key_exists($legacyUserKey, $values)) {
            $legacyUser = $values[$legacyUserKey];
            if (! $legacyUser instanceof ModelIdentifier) {
                throw new UnexpectedValueException('Invalid serialized user in CreateLeaveCheckMuster job.');
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
        $this->processUser(User::query()->findOrFail($this->userId));
    }

    private function processUser(User $user): void
    {
        DB::beginTransaction();
        try {
            if ($user?->last_check_date != null && $user?->last_check_id != null) {
                $pendingMuster = LeaveCheckMuster::where('user_id', $user->id)
                    ->where('is_checked', false)
                    ->orderBy('check_date', 'desc')
                    ->first();

                if ($pendingMuster !== null) {
                    $gradeWiseLeave = $user->grade_wise_leave;
                    $hasYearlyCreditRule = $gradeWiseLeave !== null
                        && (! $gradeWiseLeave->is_monthly
                            || LeaveRuleCadence::hasYearlyRules($gradeWiseLeave->grade_wise_monthly_leave_details));
                    $monthlyRules = $gradeWiseLeave?->is_monthly
                        ? $gradeWiseLeave->grade_wise_monthly_leave_details
                        : collect();
                    $hasMonthlyCreditRules = LeaveRuleCadence::hasMonthlyRules($monthlyRules);
                    $yearlyCheckDateConfig = GeneralConfiguration::where('key', 'yearly_leave_check_date')->value('value');
                    $currentFinancialYear = FinancialYear::forDate(Carbon::today());
                    $initialCheckDate = YearlyLeaveCredit::getInitialMusterDate(
                        Carbon::today(),
                        $currentFinancialYear,
                        $yearlyCheckDateConfig,
                        $user->join_date,
                        $hasYearlyCreditRule,
                        $hasMonthlyCreditRules,
                    );

                    if ($hasYearlyCreditRule && YearlyLeaveCredit::shouldCorrectInitialMusterDate(
                        $pendingMuster->check_date,
                        $user->join_date,
                        $initialCheckDate,
                    )) {
                        $pendingMuster->check_date = $initialCheckDate;
                        $pendingMuster->save();
                        Log::channel('attprocess')->debug(
                            'Corrected pending yearly leave muster date for | '.$user->name.'('.$user->code.') to '
                            .$initialCheckDate->format('Y-m-d')
                        );
                    }

                }

                if ($pendingMuster === null) {
                    $user_leave_check_muster = LeaveCheckMuster::where('user_id', $user?->id)
                        ->where('is_checked', true)
                        ->orderBy('check_date', 'desc')
                        ->first();
                    if ($user?->grade_wise_leave?->is_monthly) {
                        if ($user_leave_check_muster) {
                            $monthlyRules = $user->grade_wise_leave->grade_wise_monthly_leave_details;
                            $hasMonthlyRules = LeaveRuleCadence::hasMonthlyRules($monthlyRules);
                            $today = Carbon::today();
                            $monthlyCheckDateConfig = GeneralConfiguration::where('key', 'monthly_leave_check_date')->value('value');
                            $monthlyCheckDate = $hasMonthlyRules
                                ? MonthlyLeaveCheckDate::getNextScheduledDate(
                                    $user_leave_check_muster->check_date,
                                    $today,
                                    $monthlyCheckDateConfig,
                                )
                                : null;
                            if ($monthlyCheckDate !== null) {
                                LeaveCheckMuster::create([
                                    'user_id' => $user->id,
                                    'check_date' => $monthlyCheckDate->format('Y-m-d'),
                                ]);
                                Log::channel('attprocess')->debug('Create monthly leave muster for | '.$user->name.'('.$user->code.')');
                            }
                        }
                    } else {
                        if ($user_leave_check_muster) {
                            $yearlyCheckDateConfig = GeneralConfiguration::where('key', 'yearly_leave_check_date')->value('value');
                            if ($yearlyCheckDateConfig === null) {
                                $new_check_date = $user_leave_check_muster->check_date->modify('+1 year')->format('Y-m-d');
                                LeaveCheckMuster::create([
                                    'user_id' => $user?->id,
                                    'check_date' => $new_check_date,
                                ]);
                                Log::channel('attprocess')->debug('Crete Leave Muster For | '.$user?->name.'('.$user?->code.')');
                            }
                        }
                    }
                }
            } else {
                $gradeWiseLeave = $user->grade_wise_leave;
                $hasYearlyCreditRule = $gradeWiseLeave !== null
                    && (! $gradeWiseLeave->is_monthly
                        || LeaveRuleCadence::hasYearlyRules($gradeWiseLeave->grade_wise_monthly_leave_details));
                $monthlyRules = $gradeWiseLeave?->is_monthly
                    ? $gradeWiseLeave->grade_wise_monthly_leave_details
                    : collect();
                $hasMonthlyCreditRules = LeaveRuleCadence::hasMonthlyRules($monthlyRules);
                $yearlyCheckDate = GeneralConfiguration::where('key', 'yearly_leave_check_date')->value('value');
                $initialCheckDate = YearlyLeaveCredit::getInitialMusterDate(
                    Carbon::today(),
                    FinancialYear::forDate(Carbon::today()),
                    $yearlyCheckDate,
                    $user->join_date,
                    $hasYearlyCreditRule,
                    $hasMonthlyCreditRules,
                );
                $pendingMuster = LeaveCheckMuster::where('user_id', $user->id)
                    ->where('is_checked', false)
                    ->orderBy('check_date', 'desc')
                    ->first();

                if ($pendingMuster === null) {
                    LeaveCheckMuster::create([
                        'user_id' => $user->id,
                        'check_date' => $initialCheckDate,
                    ]);
                } elseif ($pendingMuster->check_date?->format('Y-m-d') !== $initialCheckDate?->format('Y-m-d')) {
                    $pendingMuster->check_date = $initialCheckDate;
                    $pendingMuster->save();
                }

                Log::channel('attprocess')->debug(
                    ($hasYearlyCreditRule && $initialCheckDate?->format('Y-m-d') !== $user->join_date?->format('Y-m-d')
                        ? 'Create or correct initial yearly leave muster for | '
                        : 'Create or correct initial leave muster for | ')
                    .$user->name.'('.$user->code.') on '.$initialCheckDate?->format('Y-m-d')
                );
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::channel('attprocess')->error(
                'Error creating leave muster for user '.$user->id.'. '.Str::limit($e->getMessage(), 200),
                ['exception' => $e],
            );
            throw $e;
        }
    }
}
