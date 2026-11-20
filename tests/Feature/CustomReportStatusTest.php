<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\CustomReportController;
use App\Models\Tenant\CustomReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomReportStatusTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.custom_report_status_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'custom_report_status_test',
        ]);
        DB::purge('custom_report_status_test');

        Schema::create('custom_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('report_name');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('date_range_preset')->default('custom');
            $table->string('report_orientation')->default('vertical');
            $table->string('summary_orientation')->default('vertical');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });

        Route::post('/test/custom-reports/{custom_report}/toggle-status', [
            CustomReportController::class,
            'toggleStatus',
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_status_test');
        DB::purge('custom_report_status_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_it_can_close_and_reopen_a_custom_report(): void
    {
        $report = CustomReport::query()->create([
            'report_name' => 'Monthly Performance Report',
            'status' => 1,
        ]);

        $this->post('/test/custom-reports/'.$report->id.'/toggle-status')
            ->assertRedirect(url('employee-structure/custom-reports'));
        $this->assertDatabaseHas('custom_reports', [
            'id' => $report->id,
            'status' => 0,
        ]);

        $this->post('/test/custom-reports/'.$report->id.'/toggle-status')
            ->assertRedirect(url('employee-structure/custom-reports'));
        $this->assertDatabaseHas('custom_reports', [
            'id' => $report->id,
            'status' => 1,
        ]);
    }
}
