<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Enums\ReportStatus;
use App\Models\Tenant\CustomReportGeneration;
use App\Support\CustomReportExporter;
use App\Support\CustomReportFieldOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
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

    public function handle(CustomReportExporter $exporter): void
    {
        $generation = CustomReportGeneration::query()
            ->with('custom_report.custom_report_details')
            ->findOrFail($this->generationId);
        $report = $generation->custom_report;
        $detail = $report->custom_report_details->first();
        $fieldLabels = CustomReportFieldOptions::all();
        $headers = collect($detail?->custom_report_fields ?? [])
            ->filter(static fn (mixed $field): bool => is_array($field))
            ->map(static fn (array $field): string => $fieldLabels[$field['field_name'] ?? ''] ?? (string) ($field['field_name'] ?? ''))
            ->values()
            ->all();
        $extension = $generation->report_format->value;
        $path = 'custom-report-exports/'.Str::slug($this->tenantId).'/'.Str::uuid().'.'.$extension;

        $exporter->store(
            $generation->report_format,
            $headers,
            $report->report_name,
            $report->report_orientation,
            $path,
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
