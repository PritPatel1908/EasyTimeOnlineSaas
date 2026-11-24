<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\DashboardController;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
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

        Schema::create('approval_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approvable_id');
            $table->string('approvable_type');
            $table->string('approval_status');
        });

        Schema::create('approval_status_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_status_id');
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->unsignedInteger('level');
            $table->string('approval_status');
        });

        Schema::create('approval_flow_details', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('approval_flow_detail_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->unsignedBigInteger('user_id');
        });

        DB::table('users')->insert(collect(range(1, 5))->map(static fn (int $id): array => [
            'id' => $id,
            'name' => 'Employee '.$id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        Schema::create('attendances'.now()->year, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
        });

        DB::table('attendances'.now()->year)->insert([
            ['user_id' => 2, 'date' => today(), 'in_time' => now(), 'out_time' => now()],
            ['user_id' => 3, 'date' => today(), 'in_time' => now(), 'out_time' => null],
            ['user_id' => 4, 'date' => today(), 'in_time' => null, 'out_time' => now()],
            ['user_id' => 5, 'date' => today()->subDay(), 'in_time' => now(), 'out_time' => now()],
            ['user_id' => 999, 'date' => today(), 'in_time' => now(), 'out_time' => now()],
        ]);

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
        $this->assertTrue($response->getData()['canApprovePendingRequests']);
        $this->assertSame(0, $response->getData()['pendingApprovalCount']);
        $this->assertCount(9, $response->getData()['applicationModules']);
        $this->assertSame(1, $response->getData()['attendanceCount']);
        $this->assertSame(5, $response->getData()['userCount']);
        $this->assertStringNotContainsString('/tenancy/assets/', asset('build/favicon.png'));
    }
}
