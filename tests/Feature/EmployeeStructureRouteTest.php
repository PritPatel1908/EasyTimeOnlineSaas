<?php

namespace Tests\Feature;

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
}
