<?php

namespace App\Jobs\Tenant\ReportJobs;

use App\Enums\MonthlyReportTypes;
use App\Helpers\MonthlyReportHelpers\AbsentReportGenerator as MonthlyAbsentReportGenerator;
use App\Helpers\MonthlyReportHelpers\ArrivalReportGenerator as MonthlyArrivalReportGenerator;
use App\Helpers\MonthlyReportHelpers\EarlyGoingReportGenerator as MonthlyEarlyGoingReportGenerator;
use App\Helpers\MonthlyReportHelpers\InOutReportGenerator as MonthlyInOutReportGenerator;
use App\Helpers\MonthlyReportHelpers\IrregularReportGenerator as MonthlyIrregularReportGenerator;
use App\Helpers\MonthlyReportHelpers\LateComingReportGenerator as MonthlyLateComingReportGenerator;
use App\Helpers\MonthlyReportHelpers\LeaveBalanceReportGenerator as MonthlyLeaveBalanceReportGenerator;
use App\Helpers\MonthlyReportHelpers\LeaveInfoReportGenerator as MonthlyLeaveInfoReportGenerator;
use App\Helpers\MonthlyReportHelpers\ManualPunchReportGenerator as MonthlyManualPunchReportGenerator;
use App\Helpers\MonthlyReportHelpers\OvertimeReportGenerator as MonthlyOvertimeReportGenerator;
use App\Helpers\MonthlyReportHelpers\PerformanceReportGenerator as MonthlyPerformanceReportGenerator;
use App\Helpers\MonthlyReportHelpers\PresentReportGenerator as MonthlyPresentReportGenerator;
use App\Helpers\MonthlyReportHelpers\SinglePunchReportGenerator as MonthlySinglePunchReportGenerator;
use App\Helpers\MonthlyReportHelpers\SummaryReportGenerator as MonthlySummaryReportGenerator;
use App\Helpers\MonthlyReportHelpers\WorkHrsReportGenerator as MonthlyWorkHrsReportGenerator;
use App\Models\Tenant\MonthlyReport;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMonthlyReport implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The general report instance.
     *
     * @var MonthlyReport
     */
    public $monthlyReport;

    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(MonthlyReport $monthlyReport)
    {
        $this->monthlyReport = $monthlyReport;
    }

    public function uniqueId()
    {
        return $this->monthlyReport->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate the general report...
        if ($this->monthlyReport->report_type === MonthlyReportTypes::performanceReport) {
            // Generate performance report
            $performanceReport = new MonthlyPerformanceReportGenerator($this->monthlyReport);
            $performanceReport->generatePerformanceReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::inOutReport) {
            // Generate in out report
            $inOutReport = new MonthlyInOutReportGenerator($this->monthlyReport);
            $inOutReport->generateInOutMusterReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::arrivalReport) {
            // Generate arrival report
            $arrivalReport = new MonthlyArrivalReportGenerator($this->monthlyReport);
            $arrivalReport->generateArrivalReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::lateComingReport) {
            // Generate late coming report
            $lateComingReport = new MonthlyLateComingReportGenerator($this->monthlyReport);
            $lateComingReport->generateLateComingReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::absentReport) {
            // Generate absent report
            $absentReport = new MonthlyAbsentReportGenerator($this->monthlyReport);
            $absentReport->generateAbsentReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::earlyGoingReport) {
            // Generate early going report
            $earlyGoingReport = new MonthlyEarlyGoingReportGenerator($this->monthlyReport);
            $earlyGoingReport->generateEarlyGoingReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::irregularReport) {
            // Generate irregular report
            $irregularReport = new MonthlyIrregularReportGenerator($this->monthlyReport);
            $irregularReport->generateIrregularReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::overtimeReport) {
            // Generate irregular report
            $overtimeReport = new MonthlyOvertimeReportGenerator($this->monthlyReport);
            $overtimeReport->generateOvertimeReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::presentReport) {
            // Generate irregular report
            $presentReport = new MonthlyPresentReportGenerator($this->monthlyReport);
            $presentReport->generatePresentReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::singlepunchReport) {
            // Generate irregular report
            $singlePunchReport = new MonthlySinglePunchReportGenerator($this->monthlyReport);
            $singlePunchReport->generateSinglePunchReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::workhrsReport) {
            // Generate irregular report
            $workHrsReport = new MonthlyWorkHrsReportGenerator($this->monthlyReport);
            $workHrsReport->generateWorkHrsReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::manualpunchReport) {
            // Generate irregular report
            $manualpunchReport = new MonthlyManualPunchReportGenerator($this->monthlyReport);
            $manualpunchReport->generateManualPunchReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::summaryReport) {
            $summaryReport = new MonthlySummaryReportGenerator($this->monthlyReport);
            $summaryReport->generateSummaryReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::leaveBalanceReport) {
            $leaveBalanceReport = new MonthlyLeaveBalanceReportGenerator($this->monthlyReport);
            $leaveBalanceReport->generateLeaveBalanceReport();
        } elseif ($this->monthlyReport->report_type === MonthlyReportTypes::leaveInfoReport) {
            $leaveInfoReport = new MonthlyLeaveInfoReportGenerator($this->monthlyReport);
            $leaveInfoReport->generateLeaveInfoReport();
        } else {
        }

        // dd($this->monthlyReport);
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->monthlyReport->created_by) {
            // Notify the user that the report generation has failed
            $this->monthlyReport->created_by_user->notify(
                Notification::make()
                    ->title('Error While Generating Report')
                    ->danger()
                    ->icon('heroicon-o-information-circle')
                    ->body('Error'.$exception->getMessage())
                    ->toDatabase(),
            );
        }
    }
}
