<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReportFormat;
use App\Support\CustomReportExporter;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CustomReportExporterTest extends TestCase
{
    public function test_csv_export_contains_the_header_row_without_data_rows(): void
    {
        Storage::fake('local');
        $path = 'custom-report-exports/test/report.csv';

        app(CustomReportExporter::class)->store(
            ReportFormat::csv,
            ['Employee Code', 'Name'],
            'Test Report',
            'vertical',
            $path,
        );

        $rows = array_map('str_getcsv', explode("\n", trim(Storage::disk('local')->get($path))));
        $this->assertSame([['Employee Code', 'Name']], $rows);
    }

    public function test_xlsx_export_contains_the_header_row_without_data_rows(): void
    {
        Storage::fake('local');
        $path = 'custom-report-exports/test/report.xlsx';

        app(CustomReportExporter::class)->store(
            ReportFormat::xlsx,
            ['Employee Code', 'Name'],
            'Test Report',
            'vertical',
            $path,
        );

        $spreadsheet = IOFactory::load(Storage::disk('local')->path($path));
        $this->assertSame([['Employee Code', 'Name']], $spreadsheet->getActiveSheet()->toArray());
    }
}
