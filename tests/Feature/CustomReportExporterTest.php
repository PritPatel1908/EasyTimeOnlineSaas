<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReportFormat;
use App\Models\Tenant\User;
use App\Support\CustomReportExporter;
use App\Support\CustomReportTableBuilder;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CustomReportExporterTest extends TestCase
{
    public function test_employee_report_includes_each_selected_employee_and_each_date_vertically(): void
    {
        $employee = new User(['code' => 'E001', 'name' => 'Employee One']);
        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date', 'date_time_format' => 'd/m/Y'],
                ['field_name' => 'name'],
            ],
            '2026-11-14',
            '2026-11-15',
            'vertical',
            [$employee],
        );

        $this->assertSame(['Emp Code', 'Date', 'Name'], $table['headers']);
        $this->assertSame([
            ['E001', '14/11/2026', 'Employee One'],
            ['E001', '15/11/2026', 'Employee One'],
        ], $table['rows']);
    }

    public function test_employee_report_uses_dates_as_columns_horizontally(): void
    {
        $employee = new User(['code' => 'E001', 'name' => 'Employee One']);
        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date'],
                ['field_name' => 'name'],
            ],
            '2026-11-14',
            '2026-11-15',
            'horizontal',
            [$employee],
        );

        $this->assertSame(['Emp Code', 'Name', '2026-11-14', '2026-11-15'], $table['headers']);
        $this->assertSame([['E001', 'Employee One', '', '']], $table['rows']);
    }

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

    public function test_csv_export_contains_data_rows(): void
    {
        Storage::fake('local');
        $path = 'custom-report-exports/test/report-with-rows.csv';

        app(CustomReportExporter::class)->store(
            ReportFormat::csv,
            ['Date'],
            'Test Report',
            'vertical',
            $path,
            [['2026-11-14'], ['2026-11-15']],
        );

        $rows = array_map('str_getcsv', explode("\n", trim(Storage::disk('local')->get($path))));
        $this->assertSame([['Date'], ['2026-11-14'], ['2026-11-15']], $rows);
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
