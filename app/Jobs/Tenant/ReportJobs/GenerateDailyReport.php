<?php

namespace App\Jobs\Tenant\ReportJobs;

use App\Enums\DailyReportTypes;
use App\Helpers\ReportHelpers\AbsentReportGenerator;
use App\Helpers\ReportHelpers\ArrivalReportGenerator;
use App\Helpers\ReportHelpers\EarlyGoingReportGenerator;
use App\Helpers\ReportHelpers\InOutReportGenerator;
use App\Helpers\ReportHelpers\IrregularReportGenerator;
use App\Helpers\ReportHelpers\LateComingReportGenerator;
use App\Helpers\ReportHelpers\LeaveBalanceReportGenerator;
use App\Helpers\ReportHelpers\OvertimeReportGenerator;
use App\Helpers\ReportHelpers\PerformanceReportGenerator;
use App\Helpers\ReportHelpers\PresentReportGenerator;
use App\Helpers\ReportHelpers\SinglePunchReportGenerator;
use App\Helpers\ReportHelpers\SummaryReportGenerator;
use App\Helpers\ReportHelpers\WorkHrsReportGenerator;
use App\Models\Tenant\DailyReport;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDailyReport implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The general report instance.
     *
     * @var DailyReport
     */
    public $dailyReport;

    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(DailyReport $dailyReport)
    {
        $this->dailyReport = $dailyReport;
    }

    public function uniqueId()
    {
        return $this->dailyReport->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate the general report...
        if ($this->dailyReport->report_type === DailyReportTypes::performanceReport) {
            // Generate performance report
            $performanceReport = new PerformanceReportGenerator($this->dailyReport);
            $performanceReport->generatePerformanceReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::inOutReport) {
            // Generate in out report
            $inOutReport = new InOutReportGenerator($this->dailyReport);
            $inOutReport->generateInOutMusterReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::arrivalReport) {
            // Generate arrival report
            $arrivalReport = new ArrivalReportGenerator($this->dailyReport);
            $arrivalReport->generateArrivalReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::lateComingReport) {
            // Generate late coming report
            $lateComingReport = new LateComingReportGenerator($this->dailyReport);
            $lateComingReport->generateLateComingReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::absentReport) {
            // Generate absent report
            $absentReport = new AbsentReportGenerator($this->dailyReport);
            $absentReport->generateAbsentReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::earlyGoingReport) {
            // Generate early going report
            $earlyGoingReport = new EarlyGoingReportGenerator($this->dailyReport);
            $earlyGoingReport->generateEarlyGoingReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::irregularReport) {
            // Generate irregular report
            $irregularReport = new IrregularReportGenerator($this->dailyReport);
            $irregularReport->generateIrregularReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::overtimeReport) {
            // Generate irregular report
            $overtimeReport = new OvertimeReportGenerator($this->dailyReport);
            $overtimeReport->generateOvertimeReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::presentReport) {
            // Generate irregular report
            $presentReport = new PresentReportGenerator($this->dailyReport);
            $presentReport->generatePresentReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::singlepunchReport) {
            // Generate irregular report
            $singlePunchReport = new SinglePunchReportGenerator($this->dailyReport);
            $singlePunchReport->generateSinglePunchReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::workhrsReport) {
            // Generate irregular report
            $workHrsReport = new WorkHrsReportGenerator($this->dailyReport);
            $workHrsReport->generateWorkHrsReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::summaryReport) {
            // Generate summary report
            $summaryReport = new SummaryReportGenerator($this->dailyReport);
            $summaryReport->generateSummaryReport();
        } elseif ($this->dailyReport->report_type === DailyReportTypes::leaveBalanceReport) {
            // Generate summary report
            $leaveBalanceReport = new LeaveBalanceReportGenerator($this->dailyReport);
            $leaveBalanceReport->generateLeaveBalanceReport();
        } else {
        }

        // dd($this->dailyReport);
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->dailyReport->created_by) {
            // Notify the user that the report generation has failed
            $this->dailyReport->created_by_user->notify(
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
