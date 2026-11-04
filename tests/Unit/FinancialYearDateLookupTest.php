<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tenant\FinancialYear;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinancialYearDateLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('financial_years', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_it_selects_the_latest_starting_financial_year_that_contains_the_date(): void
    {
        FinancialYear::query()->create([
            'code' => 'FY-A',
            'name' => 'Earlier year',
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
        ]);
        $expected = FinancialYear::query()->create([
            'code' => 'FY-B',
            'name' => 'Current year',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
        ]);
        FinancialYear::query()->create([
            'code' => 'FY-C',
            'name' => 'Overlapping year',
            'start_date' => '2026-10-01',
            'end_date' => '2027-09-30',
        ]);

        $this->assertSame(
            $expected->id,
            FinancialYear::forDate(Carbon::parse('2026-04-04'))?->id,
        );
        $this->assertSame(
            'Overlapping year',
            FinancialYear::forDate(Carbon::parse('2026-11-04'))?->name,
        );
    }

    public function test_it_returns_null_when_no_financial_year_contains_the_date(): void
    {
        $this->assertNull(FinancialYear::forDate(Carbon::parse('2026-04-04')));
    }

    public function test_migration_removes_and_restores_the_leave_group_financial_year_column(): void
    {
        Schema::create('grade_wise_leaves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years');
        });

        /** @var Migration $migration */
        $migration = require base_path(
            'database/migrations/tenant/2027_04_04_000001_remove_financial_year_id_from_grade_wise_leaves.php',
        );
        $migration->up();
        $this->assertFalse(Schema::hasColumn('grade_wise_leaves', 'financial_year_id'));

        $migration->down();
        $this->assertTrue(Schema::hasColumn('grade_wise_leaves', 'financial_year_id'));
    }
}
