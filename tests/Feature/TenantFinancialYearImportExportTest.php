<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\FinancialYearController;
use App\Jobs\Tenant\GenerateFinancialYearExport;
use App\Models\Tenant\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantFinancialYearImportExportTest extends TestCase
{
    public function test_financial_year_import_export_columns_match_the_schema(): void
    {
        $this->assertSame([
            'code',
            'name',
            'start_date',
            'end_date',
            'location',
            'company',
            'is_started',
            'is_closed',
            'status',
        ], FinancialYear::IMPORT_EXPORT_COLUMNS);
    }

    public function test_export_dispatches_tenant_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(FinancialYearController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Financial Year export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateFinancialYearExport::class, function (GenerateFinancialYearExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Afinancial_years_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_sample_has_schema_fields_and_hint_row(): void
    {
        $response = app(FinancialYearController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('financial_years_import_sample.csv', $response->headers->get('Content-Disposition', ''));

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(FinancialYear::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertTrue(\App\Support\CsvImportTemplate::isHintRow($rows[1]));
        $this->assertCount(count(FinancialYear::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_download_export_requires_an_existing_export_file(): void
    {
        Storage::fake('local');
        $fileName = 'financial_years_20261019_123456.csv';
        Storage::disk('local')->put('financial-year-exports/' . $fileName, "code,name\nFY26-27,Financial Year 2026-27\n");

        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(FinancialYearController::class)->downloadExport($fileName);

        $this->assertStringContainsString('financial_years.csv', $response->headers->get('Content-Disposition', ''));
    }
}
