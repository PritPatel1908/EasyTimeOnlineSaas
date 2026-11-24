<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\CustomReportController;
use App\Models\Tenant\CustomReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomReportPaginationTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.custom_report_pagination_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'custom_report_pagination_test',
        ]);
        DB::purge('custom_report_pagination_test');

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

        Schema::create('custom_report_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('custom_report_id');
            $table->json('custom_report_fields');
            $table->json('custom_report_filter');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_pagination_test');
        DB::purge('custom_report_pagination_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_index_loads_all_reports_for_the_orange_client_side_pagination(): void
    {
        foreach (range(1, 16) as $number) {
            CustomReport::query()->create([
                'report_name' => 'Custom Report '.$number,
            ]);
        }

        $view = app(CustomReportController::class)->index();

        $this->assertInstanceOf(Collection::class, $view->getData()['customReports']);
        $this->assertCount(16, $view->getData()['customReports']);
    }
}
