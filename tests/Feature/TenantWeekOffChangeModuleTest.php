<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\WOTypeEnumn;
use App\Http\Requests\Tenant\StoreWeekOffChangeRequest;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffChangeDetail;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class TenantWeekOffChangeModuleTest extends TestCase
{
    public function test_week_off_change_request_validates_dates_employee_filters_and_details(): void
    {
        $rules = (new StoreWeekOffChangeRequest)->rules();

        $this->assertArrayHasKey('users.*', $rules);
        $this->assertArrayHasKey('employee_filters.department_id.*', $rules);
        $this->assertArrayHasKey('employee_filters.bus_route_id.*', $rules);
        $this->assertContains('after_or_equal:from_date', $rules['to_date']);
        $this->assertSame(['required', 'array', 'min:1'], $rules['details']);
        $this->assertContains('between:0,6', $rules['details.*.week_days']);
        $this->assertContains('required', $rules['details.*.wo_type']);
        $this->assertArrayHasKey('details.*.fifth_week', $rules);
    }

    public function test_each_week_off_detail_requires_at_least_one_applicable_week(): void
    {
        $request = StoreWeekOffChangeRequest::create('/', 'POST', [
            'details' => [
                ['first_week' => '0', 'second_week' => '0', 'third_week' => '0', 'fourth_week' => '0', 'fifth_week' => '0'],
            ],
        ]);
        $rule = $request->rules()['details.*.week_days'][3];
        $errors = [];
        $fail = static function (string $message) use (&$errors): void {
            $errors[] = $message;
        };

        $rule('details.0.week_days', 0, $fail);
        $this->assertSame(['Select at least one applicable week for each weekly-off detail.'], $errors);

        $request->merge(['details' => [['first_week' => '1']]]);
        $errors = [];
        $rule('details.0.week_days', 0, $fail);
        $this->assertSame([], $errors);
    }

    public function test_week_off_change_model_uses_existing_tables_and_relationships(): void
    {
        $weekOffChange = new WeekOffChange;

        $this->assertSame('week_off_changes', $weekOffChange->getTable());
        $this->assertContains('reason', $weekOffChange->getFillable());
        $this->assertSame('week_off_change_details', (new WeekOffChangeDetail)->getTable());
        $this->assertSame(WOTypeEnumn::FullDay, WOTypeEnumn::from(1));
        $this->assertTrue(method_exists($weekOffChange, 'Users'));
        $this->assertTrue(method_exists($weekOffChange, 'WeekOffChangeDetails'));
    }

    public function test_edit_form_disables_employee_selection_and_preserves_selected_employees(): void
    {
        view()->share('errors', new ViewErrorBag);

        $weekOffChange = new WeekOffChange;
        $employee = new User;
        $employee->setRawAttributes(['id' => 7, 'name' => 'Test Employee', 'code' => 'EMP007']);
        $weekOffChange->setRelation('Users', collect([$employee]));
        $weekOffChange->setRelation('WeekOffChangeDetails', collect());

        $html = view('time-management.week-off-changes.partials.form-fields', [
            'weekOffChange' => $weekOffChange,
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
            'weekDays' => [0 => 'Sunday'],
            'weekOffTypes' => [1 => 'FullDay'],
        ])->render();

        $this->assertStringContainsString('name="users[]" value="7"', $html);
        $this->assertStringContainsString('id="week_off_change_users"', $html);
        $this->assertStringContainsString('name="details[0][week_days]"', $html);
        $this->assertStringContainsString('id="add-week-off-detail"', $html);
        $this->assertMatchesRegularExpression('/<fieldset[^>]*disabled>/', $html);
    }
}
