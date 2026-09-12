<?php

namespace App\Jobs\Tenant\ReportJobs;

use App\Enums\YearlyReportTypes;
use App\Helpers\YearlyReportHelpers\AbsentReportGenerator as YearlyAbsentReportGenerator;
use App\Helpers\YearlyReportHelpers\ArrivalReportGenerator as YearlyArrivalReportGenerator;
use App\Helpers\YearlyReportHelpers\EarlyGoingReportGenerator as YearlyEarlyGoingReportGenerator;
use App\Helpers\YearlyReportHelpers\InOutReportGenerator as YearlyInOutReportGenerator;
use App\Helpers\YearlyReportHelpers\IrregularReportGenerator as YearlyIrregularReportGenerator;
use App\Helpers\YearlyReportHelpers\LateComingReportGenerator as YearlyLateComingReportGenerator;
use App\Helpers\YearlyReportHelpers\LeaveBalanceReportGenerator as YearlyLeaveBalanceReportGenerator;
use App\Helpers\YearlyReportHelpers\LeaveInfoReportGenerator as YearlyLeaveInfoReportGenerator;
use App\Helpers\YearlyReportHelpers\ManualPunchReportGenerator as YearlyManualPunchReportGenerator;
use App\Helpers\YearlyReportHelpers\OvertimeReportGenerator as YearlyOvertimeReportGenerator;
use App\Helpers\YearlyReportHelpers\PerformanceReportGenerator as YearlyPerformanceReportGenerator;
use App\Helpers\YearlyReportHelpers\PresentReportGenerator as YearlyPresentReportGenerator;
use App\Helpers\YearlyReportHelpers\SinglePunchReportGenerator as YearlySinglePunchReportGenerator;
use App\Helpers\YearlyReportHelpers\SummaryReportGenerator as YearlySummaryReportGenerator;
use App\Helpers\YearlyReportHelpers\WorkHrsReportGenerator as YearlyWorkHrsReportGenerator;
use App\Models\Tenant\YearlyReport;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateYearlyReport implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The general report instance.
     *
     * @var YearlyReport
     */
    public $YearlyReport;

    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(YearlyReport $YearlyReport)
    {
        $this->YearlyReport = $YearlyReport;
    }

    public function uniqueId()
    {
        return $this->YearlyReport->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate the general report...
        if ($this->YearlyReport->report_type === YearlyReportTypes::performanceReport) {
            // Generate performance report
            $performanceReport = new YearlyPerformanceReportGenerator($this->YearlyReport);
            $performanceReport->generatePerformanceReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::inOutReport) {
            // Generate in out report
            $inOutReport = new YearlyInOutReportGenerator($this->YearlyReport);
            $inOutReport->generateInOutMusterReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::arrivalReport) {
            // Generate arrival report
            $arrivalReport = new YearlyArrivalReportGenerator($this->YearlyReport);
            $arrivalReport->generateArrivalReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::lateComingReport) {
            // Generate late coming report
            $lateComingReport = new YearlyLateComingReportGenerator($this->YearlyReport);
            $lateComingReport->generateLateComingReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::absentReport) {
            // Generate absent report
            $absentReport = new YearlyAbsentReportGenerator($this->YearlyReport);
            $absentReport->generateAbsentReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::earlyGoingReport) {
            // Generate early going report
            $earlyGoingReport = new YearlyEarlyGoingReportGenerator($this->YearlyReport);
            $earlyGoingReport->generateEarlyGoingReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::irregularReport) {
            // Generate irregular report
            $irregularReport = new YearlyIrregularReportGenerator($this->YearlyReport);
            $irregularReport->generateIrregularReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::overtimeReport) {
            // Generate irregular report
            $overtimeReport = new YearlyOvertimeReportGenerator($this->YearlyReport);
            $overtimeReport->generateOvertimeReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::presentReport) {
            // Generate irregular report
            $presentReport = new YearlyPresentReportGenerator($this->YearlyReport);
            $presentReport->generatePresentReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::singlepunchReport) {
            // Generate irregular report
            $singlePunchReport = new YearlySinglePunchReportGenerator($this->YearlyReport);
            $singlePunchReport->generateSinglePunchReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::workhrsReport) {
            // Generate irregular report
            $workHrsReport = new YearlyWorkHrsReportGenerator($this->YearlyReport);
            $workHrsReport->generateWorkHrsReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::manualpunchReport) {
            // Generate irregular report
            $manualpunchReport = new YearlyManualPunchReportGenerator($this->YearlyReport);
            $manualpunchReport->generateManualPunchReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::summaryReport) {
            $summaryReport = new YearlySummaryReportGenerator($this->YearlyReport);
            $summaryReport->generateSummaryReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::leaveBalanceReport) {
            $leaveBalanceReport = new YearlyLeaveBalanceReportGenerator($this->YearlyReport);
            $leaveBalanceReport->generateLeaveBalanceReport();
        } elseif ($this->YearlyReport->report_type === YearlyReportTypes::leaveInfoReport) {
            $leaveInfoReport = new YearlyLeaveInfoReportGenerator($this->YearlyReport);
            $leaveInfoReport->generateLeaveInfoReport();
        } else {
        }

        // dd($this->YearlyReport);
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->YearlyReport->created_by) {
            // Notify the user that the report generation has failed
            $this->YearlyReport->created_by_user->notify(
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
