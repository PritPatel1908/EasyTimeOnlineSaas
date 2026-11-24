<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\DashboardController;
use App\Models\Tenant\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantDashboardTest extends TestCase
{
    public function test_dashboard_uses_the_correct_tenant_view(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_pic')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $user = new User();
        $user->forceFill([
            'id' => 1,
            'name' => 'Aarav Patel',
            'profile_pic' => 'employee-profiles/aarav.png',
        ]);
        Auth::guard('tenant')->setUser($user);

        $response = app(DashboardController::class)->index();

        $this->assertSame('index', $response->name());
        $this->assertArrayHasKey('tenantId', $response->getData());
        $this->assertArrayHasKey('domain', $response->getData());
        $this->assertArrayHasKey('recentUsers', $response->getData());
        $this->assertSame('Aarav Patel', $response->getData()['currentUserName']);
        $this->assertSame('employee-profiles/aarav.png', $response->getData()['currentUserProfilePic']);
        $this->assertStringNotContainsString('/tenancy/assets/', asset('build/favicon.png'));
    }
}
