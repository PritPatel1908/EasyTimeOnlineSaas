<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Enums\ReportStatus;
use App\Models\Tenant\CustomReportGeneration;
use App\Support\CustomReportDateRange;
use App\Support\CustomReportEmployeeQuery;
use App\Support\CustomReportExporter;
use App\Support\CustomReportTableBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class GenerateCustomReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $generationId,
        public string $tenantId,
    ) {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(
        CustomReportExporter $exporter,
        CustomReportEmployeeQuery $employeeQuery,
        CustomReportDateRange $dateRangeResolver,
        CustomReportTableBuilder $tableBuilder,
    ): void {
        $generation = CustomReportGeneration::query()
            ->with('custom_report.custom_report_details')
            ->findOrFail($this->generationId);
        $report = $generation->custom_report;
        $detail = $report->custom_report_details->first();
        $employees = $employeeQuery->build($detail?->custom_report_filter ?? [])
            ->with(['personalInfo', 'addresses', 'roles'])
            ->get();
        $dateRange = $dateRangeResolver->resolve(
            $report->date_range_preset ?? 'custom',
            $report->from_date,
            $report->to_date,
        );
        $table = $tableBuilder->buildForEmployees(
            $detail?->custom_report_fields ?? [],
            $dateRange['from']->toDateString(),
            $dateRange['to']->toDateString(),
            $report->report_orientation,
            $employees,
            $report->summary_orientation ?? $report->report_orientation,
        );
        $extension = $generation->report_format->value;
        $path = 'custom-report-exports/'.Str::slug($this->tenantId).'/'.Str::uuid().'.'.$extension;

        $exporter->store(
            $generation->report_format,
            $table['headers'],
            $report->report_name,
            $report->report_orientation,
            $path,
            $table['rows'],
        );

        $generation->update([
            'report_status' => ReportStatus::generated,
            'report_path' => $path,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        CustomReportGeneration::query()
            ->whereKey($this->generationId)
            ->update(['report_status' => ReportStatus::error->value]);
    }
}
