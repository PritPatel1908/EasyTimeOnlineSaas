<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\User;
use App\Support\LeaveRuleCadence;
use App\Support\YearlyLeaveCredit;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateLeaveCheckMusterBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(public ?int $financialYearId = null)
    {
    }

    public function handle(): void
    {
        $financialYear = $this->financialYearId === null
            ? null
            : FinancialYear::query()->findOrFail($this->financialYearId);

        User::query()
            ->where('id', '!=', 1)
            ->whereNull('left_date')
            ->where('is_inactive', false)
            ->chunkById(100, function ($users) use ($financialYear): void {
                foreach ($users as $user) {
                    (new CreateLeaveCheckMuster($user))->handle();

                    if ($financialYear !== null) {
                        $this->createYearlyMusterForFinancialYear($user, $financialYear);
                    }
                }
            });
    }

    private function createYearlyMusterForFinancialYear(User $user, FinancialYear $financialYear): void
    {
        $gradeWiseLeave = $user->grade_wise_leave;
        if ($gradeWiseLeave === null) {
            return;
        }

        if ($gradeWiseLeave->is_monthly) {
            $hasYearlyRules = LeaveRuleCadence::hasYearlyRules(
                $gradeWiseLeave->grade_wise_monthly_leave_details,
            );
        } else {
            $hasYearlyRules = $gradeWiseLeave->grade_wise_yearly_leave_details->isNotEmpty();
        }

        if (! $hasYearlyRules) {
            return;
        }

        $configuredDay = GeneralConfiguration::query()
            ->where('key', 'yearly_leave_check_date')
            ->value('value');
        $joinDate = $user->join_date === null
            ? null
            : Carbon::parse($user->join_date)->startOfDay();

        if ($configuredDay !== null) {
            $checkDate = YearlyLeaveCredit::getScheduledCreditDate(
                $financialYear->end_date->copy(),
                $financialYear,
                $configuredDay,
                $joinDate,
            );
        } else {
            $lastCheckedMuster = LeaveCheckMuster::query()
                ->where('user_id', $user->id)
                ->where('is_checked', true)
                ->orderByDesc('check_date')
                ->first();

            $checkDate = $lastCheckedMuster?->check_date?->copy()->addYear()
                ?? $joinDate
                ?? $financialYear->start_date->copy();
        }

        if ($checkDate === null) {
            Log::channel('attprocess')->warning(
                'Yearly leave muster skipped: invalid yearly leave check date for user '.$user->id
                .' and Financial Year '.$financialYear->id
            );

            return;
        }

        if (LeaveCheckMuster::query()
            ->where('user_id', $user->id)
            ->whereDate('check_date', $checkDate->toDateString())
            ->exists()) {
            return;
        }

        LeaveCheckMuster::query()->create([
            'user_id' => $user->id,
            'check_date' => $checkDate->toDateString(),
            'is_checked' => false,
        ]);

        Log::channel('attprocess')->info(
            'Created yearly leave muster for | '.$user->name.'('.$user->code.') on '
            .$checkDate->toDateString().' for Financial Year '.$financialYear->id
        );
    }
}
