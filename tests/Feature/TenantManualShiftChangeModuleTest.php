<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualShiftChangeController;
use App\Http\Requests\Tenant\StoreManualShiftChangeRequest;
use App\Jobs\Tenant\CreateShiftMuster;
use App\Jobs\Tenant\ProcessManualShiftChangeImport;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rules\In;
use Mockery;
use Tests\TestCase;

class TenantManualShiftChangeModuleTest extends TestCase
{
    public function test_manual_shift_change_request_validates_manual_shift_fields_and_employee_filters(): void
    {
        $rules = (new StoreManualShiftChangeRequest)->rules();

        $this->assertSame('required', $rules['shift_type'][0]);
        $this->assertInstanceOf(In::class, $rules['shift_type'][1]);
        $this->assertArrayHasKey('shift_ids.*', $rules);
        $this->assertArrayHasKey('shift_rotation_id', $rules);
        $this->assertArrayHasKey('employee_filters.department_id.*', $rules);
        $this->assertArrayHasKey('employee_filters.bus_route_id.*', $rules);
        $this->assertContains('after_or_equal:from_date', $rules['to_date']);
        $this->assertSame(['nullable', 'boolean'], $rules['is_forever']);
    }

    public function test_fixed_shift_change_accepts_only_one_shift(): void
    {
        $request = StoreManualShiftChangeRequest::create('/', 'POST', ['shift_type' => 'fixed']);
        $rule = $request->rules()['shift_ids'][4];
        $errors = [];
        $fail = static function (string $message) use (&$errors): void {
            $errors[] = $message;
        };

        $rule('shift_ids', [1], $fail);
        $this->assertSame([], $errors);

        $rule('shift_ids', [1, 2], $fail);
        $this->assertSame(['The fixed shift type can only have one shift.'], $errors);
    }

    public function test_manual_shift_change_uses_existing_shift_change_storage_and_relations(): void
    {
        $shiftChange = new ShiftChange;

        $this->assertSame('shift_changes', $shiftChange->getTable());
        $this->assertContains('shift_type', $shiftChange->getFillable());
        $this->assertNotContains('location_id', $shiftChange->getFillable());
        $this->assertNotContains('company_id', $shiftChange->getFillable());
        $this->assertArrayNotHasKey('location_id', $shiftChange->getCasts());
        $this->assertArrayNotHasKey('company_id', $shiftChange->getCasts());
        $this->assertFalse(method_exists($shiftChange, 'location'));
        $this->assertFalse(method_exists($shiftChange, 'company'));
    }

    public function test_shift_muster_jobs_are_not_globally_deduplicated(): void
    {
        $this->assertFalse(is_subclass_of(CreateShiftMuster::class, ShouldBeUnique::class));
    }

    public function test_manual_shift_change_import_dispatches_an_import_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);
        $file = UploadedFile::fake()->createWithContent(
            'manual_shift_changes_import_sample.csv',
            implode(',', ShiftChange::IMPORT_EXPORT_COLUMNS) . "\nE001,fixed,DAY,,2026-10-20,0,2026-10-21,Correction\n",
        );
        $request = Request::create(
            '/time-management/manual-shift-changes/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(ManualShiftChangeController::class)->import($request);

        $this->assertStringContainsString('Manual shift change import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessManualShiftChangeImport::class, static function (ProcessManualShiftChangeImport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'import'
                && str_contains($job->filePath, 'manual-shift-change-imports/');
        });
        Bus::assertNotDispatched(CreateShiftMuster::class);
    }

    public function test_manual_shift_change_edit_disables_employee_filtration_and_preserves_selected_users(): void
    {
        view()->share('errors', new ViewErrorBag);

        $shiftChange = new ShiftChange;
        $user = new User;
        $user->setRawAttributes(['id' => 7, 'name' => 'Test Employee', 'code' => 'EMP007']);
        $shiftChange->setRelation('Users', collect([$user]));

        $html = view('time-management.manual-shift-changes.partials.form-fields', [
            'shiftChange' => $shiftChange,
            'users' => collect(),
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
            'shifts' => collect(),
            'shiftRotations' => collect(),
        ])->render();

        $this->assertStringContainsString(
            '<fieldset class="manual-shift-change-employee-fields border-0 p-0 m-0 w-100" disabled>',
            $html,
        );
        $this->assertStringContainsString('name="users[]" value="7"', $html);
        $this->assertMatchesRegularExpression('/<select[^>]*id="manual_shift_change_users"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/<select[^>]*id="manual_shift_change_employee_filter_department_id"[^>]*disabled/', $html);
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }
}
