<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\WeekOffSwapController;
use App\Http\Requests\Tenant\StoreWeekOffSwapRequest;
use App\Jobs\Tenant\CreateWeekOffSwap;
use App\Jobs\Tenant\ProcessWeekOffSwapImport;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwap;
use App\Models\Tenant\WeekOffSwapUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class TenantWeekOffSwapModuleTest extends TestCase
{
    public function test_swap_jobs_can_be_queued_again_for_an_edited_record(): void
    {
        $this->assertFalse(
            (new \ReflectionClass(CreateWeekOffSwap::class))
                ->implementsInterface(\Illuminate\Contracts\Queue\ShouldBeUnique::class),
        );
    }

    public function test_week_off_swap_request_validates_employees_dates_and_type(): void
    {
        $rules = (new StoreWeekOffSwapRequest)->rules();

        $this->assertArrayHasKey('users.*', $rules);
        $this->assertArrayHasKey('employee_filters.department_id.*', $rules);
        $this->assertContains('required', $rules['week_date']);
        $this->assertContains('different:week_date', $rules['swap_date']);
        $this->assertArrayNotHasKey('wo_type', $rules);
    }

    public function test_week_off_swap_model_uses_existing_tables_and_employee_relationship(): void
    {
        $swap = new WeekOffSwap;

        $this->assertSame('week_off_swaps', $swap->getTable());
        $this->assertSame('week_off_swap_users', (new WeekOffSwapUser)->getTable());
        $this->assertSame(['employee_codes', 'week_date', 'swap_date', 'reason'], WeekOffSwap::IMPORT_EXPORT_COLUMNS);
        $this->assertNotContains('wo_type', $swap->getFillable());
        $this->assertArrayNotHasKey('wo_type', $swap->getCasts());
        $this->assertTrue(method_exists($swap, 'Users'));
    }

    public function test_week_off_swap_migration_removes_and_can_restore_type_column(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        Schema::create('week_off_swaps', function (Blueprint $table): void {
            $table->id();
            $table->tinyInteger('wo_type')->nullable();
        });

        $migration = require database_path('migrations/tenant/2026_10_24_120700_remove_wo_type_from_week_off_swaps_table.php');
        $migration->up();
        $this->assertFalse(Schema::hasColumn('week_off_swaps', 'wo_type'));

        $migration->down();
        $this->assertTrue(Schema::hasColumn('week_off_swaps', 'wo_type'));

        Schema::dropIfExists('week_off_swaps');
    }

    public function test_week_off_swap_form_renders_dates_and_employee_select_without_week_off_type(): void
    {
        view()->share('errors', new ViewErrorBag);
        $swap = new WeekOffSwap;
        $employee = new User;
        $employee->setRawAttributes(['id' => 17, 'name' => 'Test Employee', 'code' => 'EMP017']);
        $swap->setRelation('Users', collect([$employee]));

        $html = view('time-management.week-off-swaps.partials.form-fields', [
            'weekOffSwap' => $swap,
            'users' => collect([$employee]),
            'locations' => collect(),
            'companies' => collect(),
            'departments' => collect(),
            'subDepartments' => collect(),
            'categories' => collect(),
            'subCategories' => collect(),
            'designations' => collect(),
            'grades' => collect(),
            'units' => collect(),
            'busRoutes' => collect(),
        ])->render();

        $this->assertStringContainsString('name="week_date"', $html);
        $this->assertStringContainsString('name="swap_date"', $html);
        $this->assertStringNotContainsString('name="wo_type"', $html);
        $this->assertStringContainsString('name="users[]"', $html);
        $this->assertStringContainsString('<option value="17"', $html);
    }

    public function test_week_off_check_reports_selected_employees_without_a_week_off(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'code' => 'ADM001'],
            ['id' => 2, 'name' => 'Weekly Off Employee', 'code' => 'EMP002'],
            ['id' => 3, 'name' => 'Working Employee', 'code' => 'EMP003'],
        ]);
        DB::table('week_off_musters')->insert([
            ['date' => '2026-10-25', 'user_id' => 2, 'wo_type' => 1],
            ['date' => '2026-10-25', 'user_id' => 3, 'wo_type' => 0],
        ]);

        $admin = new User;
        $admin->setRawAttributes(['id' => 1, 'name' => 'Admin']);
        Auth::guard('tenant')->setUser($admin);

        $response = app(WeekOffSwapController::class)->checkWeekOff(new Request([
            'week_date' => '2026-10-25',
            'users' => [2, 3],
        ]));

        $this->assertFalse($response->getData(true)['has_week_off']);
        $this->assertSame(
            [['id' => 3, 'name' => 'Working Employee', 'code' => 'EMP003']],
            $response->getData(true)['employees_without_week_off'],
        );

        Schema::dropIfExists('week_off_musters');
        Schema::dropIfExists('users');
        Auth::forgetGuards();
    }

    public function test_week_off_check_rejects_a_source_date_after_its_week_off_was_swapped_away(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swaps', function (Blueprint $table): void {
            $table->id();
            $table->date('week_date');
            $table->date('swap_date');
            $table->string('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->string('weekoffable_type')->nullable();
            $table->unsignedBigInteger('weekoffable_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'code' => 'ADM001'],
            ['id' => 2, 'name' => 'Swap Employee', 'code' => 'EMP002'],
        ]);
        DB::table('week_off_swaps')->insert([
            'id' => 8,
            'week_date' => '2026-10-25',
            'swap_date' => '2026-10-26',
        ]);
        DB::table('week_off_musters')->insert([
            [
                'date' => '2026-10-25',
                'user_id' => 2,
                'weekoffable_type' => WeekOffSwap::class,
                'weekoffable_id' => 8,
                'is_locked' => false,
                'wo_type' => 0,
            ],
            [
                'date' => '2026-10-26',
                'user_id' => 2,
                'weekoffable_type' => WeekOffSwap::class,
                'weekoffable_id' => 8,
                'is_locked' => false,
                'wo_type' => 2,
            ],
            [
                'date' => '2026-10-27',
                'user_id' => 2,
                'weekoffable_type' => null,
                'weekoffable_id' => null,
                'is_locked' => false,
                'wo_type' => 0,
            ],
        ]);

        $admin = new User;
        $admin->setRawAttributes(['id' => 1, 'name' => 'Admin']);
        Auth::guard('tenant')->setUser($admin);

        $swappedSourceDate = app(WeekOffSwapController::class)->checkWeekOff(new Request([
            'week_date' => '2026-10-25',
            'users' => [2],
        ]));
        $invalidEditDate = app(WeekOffSwapController::class)->checkWeekOff(new Request([
            'week_date' => '2026-10-27',
            'users' => [2],
        ]));

        $this->assertFalse($swappedSourceDate->getData(true)['has_week_off']);
        $this->assertSame(2, $swappedSourceDate->getData(true)['employees_without_week_off'][0]['id']);
        $this->assertFalse($invalidEditDate->getData(true)['has_week_off']);
        $this->assertSame(2, $invalidEditDate->getData(true)['employees_without_week_off'][0]['id']);

        Schema::dropIfExists('week_off_musters');
        Schema::dropIfExists('week_off_swaps');
        Schema::dropIfExists('users');
        Auth::forgetGuards();
    }

    public function test_edit_week_date_does_not_pass_when_only_an_unrelated_muster_exists(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swaps', function (Blueprint $table): void {
            $table->id();
            $table->date('week_date');
            $table->date('swap_date');
            $table->string('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->string('weekoffable_type')->nullable();
            $table->unsignedBigInteger('weekoffable_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'code' => 'ADM001'],
            ['id' => 2, 'name' => 'Swap Employee', 'code' => 'EMP002'],
        ]);
        DB::table('week_off_swaps')->insert([
            'id' => 8,
            'week_date' => '2026-10-25',
            'swap_date' => '2026-10-18',
        ]);
        DB::table('week_off_musters')->insert([
            [
                'date' => '2026-10-18',
                'user_id' => 2,
                'weekoffable_type' => WeekOffSwap::class,
                'weekoffable_id' => 8,
                'is_locked' => false,
                'wo_type' => 2,
            ],
            [
                'date' => '2026-10-24',
                'user_id' => 2,
                'weekoffable_type' => \App\Models\Tenant\WeekOffChange::class,
                'weekoffable_id' => 2,
                'is_locked' => false,
                'wo_type' => 0,
            ],
        ]);

        $admin = new User;
        $admin->setRawAttributes(['id' => 1, 'name' => 'Admin']);
        Auth::guard('tenant')->setUser($admin);

        $response = app(WeekOffSwapController::class)->checkWeekOff(new Request([
            'week_date' => '2026-10-24',
            'users' => [2],
            'week_off_swap_id' => 8,
        ]));

        $this->assertFalse($response->getData(true)['has_week_off']);
        $this->assertSame(2, $response->getData(true)['employees_without_week_off'][0]['id']);

        Schema::dropIfExists('week_off_musters');
        Schema::dropIfExists('week_off_swaps');
        Schema::dropIfExists('users');
        Auth::forgetGuards();
    }

    public function test_week_off_swap_import_rejects_employees_without_a_week_off_on_week_date(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Working Employee',
            'code' => 'EMP002',
        ]);
        DB::table('week_off_musters')->insert([
            'date' => '2026-10-25',
            'user_id' => 2,
            'is_locked' => false,
            'wo_type' => 0,
        ]);

        $job = new ProcessWeekOffSwapImport(1, 'unused.csv');
        $resolveImportRow = new \ReflectionMethod(ProcessWeekOffSwapImport::class, 'resolveImportRow');
        $result = $resolveImportRow->invoke($job, [
            'employee_codes' => 'EMP002',
            'week_date' => '2026-10-25',
            'swap_date' => '2026-10-26',
            'reason' => '',
        ]);

        $this->assertContains('week_date is not a week-off for: Working Employee (EMP002)', $result[2]);

        Schema::dropIfExists('week_off_musters');
        Schema::dropIfExists('users');
    }

    public function test_week_off_swap_import_applies_muster_logic_directly(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        Storage::fake('local');
        Notification::fake();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swaps', function (Blueprint $table): void {
            $table->id();
            $table->date('week_date');
            $table->date('swap_date');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swap_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_swap_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->string('weekoffable_type')->nullable();
            $table->unsignedBigInteger('weekoffable_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'code' => 'ADM001'],
            ['id' => 2, 'name' => 'Swap Employee', 'code' => 'EMP002'],
        ]);
        DB::table('week_off_musters')->insert([
            ['date' => '2026-10-25', 'user_id' => 2, 'is_locked' => false, 'wo_type' => 2],
            ['date' => '2026-10-26', 'user_id' => 2, 'is_locked' => false, 'wo_type' => 0],
        ]);
        Storage::disk('local')->put(
            'week-off-swap-imports/test.csv',
            "employee_codes,week_date,swap_date,reason\nEMP002,2026-10-25,2026-10-26,Import test\n",
        );

        (new ProcessWeekOffSwapImport(1, 'week-off-swap-imports/test.csv'))->handle();

        $musters = DB::table('week_off_musters')->orderBy('date')->get();
        $this->assertSame([0, 2], $musters->pluck('wo_type')->map(static fn ($type): int => (int) $type)->all());
        $this->assertFalse((bool) $musters[1]->is_locked);
        $this->assertDatabaseCount('week_off_swaps', 1);

        Schema::dropIfExists('week_off_musters');
        Schema::dropIfExists('week_off_swap_users');
        Schema::dropIfExists('week_off_swaps');
        Schema::dropIfExists('users');
        Auth::forgetGuards();
    }

    public function test_swap_job_moves_existing_week_off_type_without_a_swap_type_field(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swaps', function (Blueprint $table): void {
            $table->id();
            $table->date('week_date');
            $table->date('swap_date');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swap_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_swap_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->string('weekoffable_type')->nullable();
            $table->unsignedBigInteger('weekoffable_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin', 'code' => 'ADM001'],
            ['id' => 2, 'name' => 'Swap Employee', 'code' => 'EMP002'],
        ]);
        DB::table('week_off_swaps')->insert([
            'id' => 8,
            'week_date' => '2026-10-25',
            'swap_date' => '2026-10-26',
        ]);
        DB::table('week_off_swap_users')->insert([
            'week_off_swap_id' => 8,
            'user_id' => 2,
        ]);
        DB::table('week_off_musters')->insert([
            ['date' => '2026-10-25', 'user_id' => 2, 'is_locked' => false, 'wo_type' => 2],
            ['date' => '2026-10-26', 'user_id' => 2, 'is_locked' => false, 'wo_type' => 0],
            ['date' => '2026-10-27', 'user_id' => 2, 'is_locked' => false, 'wo_type' => 0],
            ['date' => '2026-10-28', 'user_id' => 2, 'is_locked' => false, 'wo_type' => 0],
        ]);

        $admin = new User;
        $admin->setRawAttributes(['id' => 1, 'name' => 'Admin']);
        Auth::guard('tenant')->setUser($admin);

        $swap = WeekOffSwap::query()->findOrFail(8);
        (new CreateWeekOffSwap($swap))->handle();

        $musters = DB::table('week_off_musters')->orderBy('date')->get();
        $this->assertSame(0, (int) $musters[0]->wo_type);
        $this->assertSame(2, (int) $musters[1]->wo_type);
        $this->assertFalse((bool) $musters[1]->is_locked);

        $swap->swap_date = '2026-10-27';
        $swap->save();
        (new CreateWeekOffSwap($swap->refresh()))->handle();

        $musters = DB::table('week_off_musters')->orderBy('date')->get();
        $this->assertSame([0, 0, 2, 0], $musters->pluck('wo_type')->map(static fn ($type): int => (int) $type)->all());
        $this->assertFalse((bool) $musters[1]->is_locked);
        $this->assertFalse((bool) $musters[2]->is_locked);

        (new CreateWeekOffSwap($swap->refresh()))->handle();
        $this->assertSame(
            [0, 0, 2, 0],
            DB::table('week_off_musters')->orderBy('date')->pluck('wo_type')->map(static fn ($type): int => (int) $type)->all(),
        );

        $swap->week_date = '2026-10-27';
        $swap->swap_date = '2026-10-28';
        $swap->save();
        (new CreateWeekOffSwap($swap->refresh()))->handle();
        $this->assertSame(
            [0, 0, 0, 2],
            DB::table('week_off_musters')->orderBy('date')->pluck('wo_type')->map(static fn ($type): int => (int) $type)->all(),
        );
        $this->assertFalse((bool) DB::table('week_off_musters')->whereDate('date', '2026-10-28')->value('is_locked'));

        Schema::dropIfExists('week_off_musters');
        Schema::dropIfExists('week_off_swap_users');
        Schema::dropIfExists('week_off_swaps');
        Schema::dropIfExists('users');
        Auth::forgetGuards();
    }
}
