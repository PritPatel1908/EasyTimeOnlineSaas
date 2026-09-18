<?php

namespace Tests\Feature;

use App\Models\Tenant\Category;
use App\Models\Tenant\User;
use App\Support\TenantPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EmployeeStructureRouteTest extends TestCase
{
    public function test_employee_structure_category_route_exists(): void
    {
        $this->assertTrue(Route::has('tenant.employee-structure.categories.index'));
    }

    public function test_super_admin_user_id_1_has_no_employee_structure_permission_restriction(): void
    {
        $user = new User();
        $user->id = 1;

        Auth::guard('tenant')->setUser($user);

        $this->assertTrue(TenantPermissions::userCan('Category', 'read'));
        $this->assertTrue(TenantPermissions::userCan('Category', 'create'));
    }

    public function test_category_form_views_render_flash_alerts(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        session(['success' => 'Category saved successfully.']);

        $category = new Category();
        $category->id = 1;
        $category->name = 'Test Category';
        $category->setRelation('c_off_against_wo_hl_slabs', collect());
        $category->setRelation('c_off_against_ot_slabs', collect());

        $addView = view('employee-structure.categories.add', [
            'category' => null,
            'companies' => collect(),
            'locations' => collect(),
            'leaveTypes' => collect(),
        ])->render();

        $editView = view('employee-structure.categories.edit', [
            'category' => $category,
            'companies' => collect(),
            'locations' => collect(),
            'leaveTypes' => collect(),
        ])->render();

        $this->assertStringContainsString('auto-dismiss-alert', $addView);
        $this->assertStringContainsString('alert-success', $editView);
    }
}
