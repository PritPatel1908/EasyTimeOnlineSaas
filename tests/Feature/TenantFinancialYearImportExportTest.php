<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\FinancialYearController;
use App\Http\Requests\Tenant\UpdateFinancialYearRequest;
use App\Jobs\Tenant\GenerateFinancialYearExport;
use App\Models\Tenant\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
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

    public function test_only_the_started_year_can_be_stopped_and_a_stopped_year_cannot_be_reopened(): void
    {
        $this->createFinancialYearsTable();
        DB::table('financial_years')->insert([
            ['id' => 1, 'is_started' => true, 'is_closed' => false],
            ['id' => 2, 'is_started' => false, 'is_closed' => false],
        ]);

        $blockedStart = $this->updateFinancialYearFlags(2, 1, 0);
        $this->assertSame(302, $blockedStart->getStatusCode());
        $this->assertDatabaseHas('financial_years', ['id' => 2, 'is_started' => false]);

        $stop = $this->updateFinancialYearFlags(1, 0, 1);
        $this->assertSame(302, $stop->getStatusCode());
        $this->assertDatabaseHas('financial_years', ['id' => 1, 'is_started' => false, 'is_closed' => true]);

        $reopen = $this->updateFinancialYearFlags(1, 1, 0);
        $this->assertSame(302, $reopen->getStatusCode());
        $this->assertDatabaseHas('financial_years', ['id' => 1, 'is_started' => false, 'is_closed' => true]);

        $startNext = $this->updateFinancialYearFlags(2, 1, 0);
        $this->assertSame(302, $startNext->getStatusCode());
        $this->assertDatabaseHas('financial_years', ['id' => 2, 'is_started' => true, 'is_closed' => false]);
    }

    public function test_started_financial_year_dates_and_status_are_kept_unchanged_during_update(): void
    {
        $this->createFinancialYearsTable();
        DB::table('financial_years')->insert([
            'id' => 1,
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'status' => 1,
            'is_started' => true,
            'is_closed' => false,
        ]);

        $request = UpdateFinancialYearRequest::create('/time-management/financial-years/1', 'PUT', [
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
            'status' => 0,
        ]);
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('parameter')->with('financial_year', null)->andReturn(1);
        $request->setRouteResolver(static fn () => $route);

        $prepare = new \ReflectionMethod(UpdateFinancialYearRequest::class, 'prepareForValidation');
        $prepare->setAccessible(true);
        $prepare->invoke($request);

        $this->assertSame('2026-04-01', $request->input('start_date'));
        $this->assertSame('2027-03-31', $request->input('end_date'));
        $this->assertSame(1, $request->input('status'));
    }

    private function createFinancialYearsTable(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('financial_years', function ($table): void {
            $table->id();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->boolean('is_started')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->softDeletes();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    private function updateFinancialYearFlags(int $id, int $isStarted, int $isClosed): \Illuminate\Http\RedirectResponse
    {
        $request = Request::create('/time-management/financial-years/'.$id.'/flags', 'POST', [
            'is_started' => $isStarted,
            'is_closed' => $isClosed,
        ]);
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('parameter')->with('financial_year', null)->andReturn($id);
        $request->setRouteResolver(static fn () => $route);

        return app(FinancialYearController::class)->updateFlags($request);
    }
}
