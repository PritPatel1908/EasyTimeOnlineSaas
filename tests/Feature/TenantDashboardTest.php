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
            $table->string('name');
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('user_type')->nullable();
            $table->date('dob')->nullable();
            $table->date('join_date')->nullable();
            $table->date('left_date')->nullable();
            $table->boolean('is_inactive')->default(false);
            $table->json('department_id')->nullable();
            $table->json('company_id')->nullable();
            $table->json('location_id')->nullable();
            $table->json('sub_department_id')->nullable();
            $table->json('category_id')->nullable();
            $table->json('sub_category_id')->nullable();
            $table->json('designation_id')->nullable();
            $table->json('grade_id')->nullable();
            $table->json('unit_id')->nullable();
            $table->json('bus_route_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('canteen_facility_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        foreach ([
            'departments',
            'sub_departments',
            'sub_categories',
            'units',
            'grades',
            'bus_routes',
            'teams',
            'canteen_facilities',
            'areas',
            'machines',
            'categories',
            'designations',
            'approval_flows',
        ] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->softDeletes();
            });
        }

        Schema::create('user_area', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('area_id');
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

        Schema::create('status_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('alias');
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('status_musters', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('status_master_id')->nullable();
            $table->float('day_count');
            $table->integer('year');
            $table->boolean('is_locked');
            $table->timestamps();
        });

        DB::table('companies')->insert([
            ['id' => 1, 'name' => 'Alpha Co'],
            ['id' => 2, 'name' => 'Beta Co'],
        ]);
        DB::table('locations')->insert([
            ['id' => 1, 'name' => 'Head Office'],
            ['id' => 2, 'name' => 'Branch'],
        ]);
        DB::table('departments')->insert([
            ['id' => 1, 'name' => 'HR'],
            ['id' => 2, 'name' => 'Engineering'],
        ]);
        foreach ([
            'sub_departments',
            'sub_categories',
            'categories',
            'designations',
            'grades',
            'units',
            'bus_routes',
            'teams',
            'canteen_facilities',
            'areas',
        ] as $tableName) {
            DB::table($tableName)->insert(['id' => 1, 'name' => ucfirst(str_replace('_', ' ', $tableName))]);
        }

        DB::table('users')->insert(collect(range(1, 8))->map(static fn (int $id): array => [
            'id' => $id,
            'name' => 'Employee '.$id,
            'user_type' => $id === 1 ? 'admin' : 'employee',
            'dob' => $id === 2 ? today()->toDateString() : ($id === 3 || $id === 5 ? today()->copy()->addDay()->toDateString() : ($id === 4 ? today()->toDateString() : null)),
            'left_date' => $id === 5 ? today()->toDateString() : null,
            'is_inactive' => $id === 4,
            'department_id' => in_array($id, [2, 5, 6, 8], true) ? '[1]' : (in_array($id, [3, 4, 7], true) ? '[2]' : null),
            'company_id' => $id === 2 ? '[1]' : ($id === 3 ? '[1,2]' : null),
            'location_id' => $id === 2 ? '[1]' : ($id === 3 ? '[1,2]' : null),
            'sub_department_id' => $id === 2 ? '[1]' : null,
            'category_id' => $id === 2 ? '[1]' : null,
            'sub_category_id' => $id === 2 ? '[1]' : null,
            'designation_id' => $id === 2 ? '[1]' : null,
            'grade_id' => $id === 2 ? '[1]' : null,
            'unit_id' => $id === 2 ? '[1]' : null,
            'bus_route_id' => $id === 2 ? '[1]' : null,
            'team_id' => $id === 2 ? 1 : null,
            'canteen_facility_id' => $id === 2 ? 1 : null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
        DB::table('user_area')->insert([
            ['user_id' => 2, 'area_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $statusMasterIds = [];
        foreach (['AA', 'AP', 'PP', 'CL', 'HL', 'WO'] as $code) {
            $statusMasterIds[$code] = DB::table('status_masters')->insertGetId([
                'code' => $code,
                'alias' => $code,
                'description' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('status_musters')->insert(collect([
            2 => 'PP',
            3 => 'AA',
            6 => 'CL',
            7 => 'HL',
            8 => 'WO',
        ])->map(static fn (string $code, int $userId): array => [
            'date' => today()->toDateString(),
            'user_id' => $userId,
            'status_master_id' => $statusMasterIds[$code],
            'day_count' => 0,
            'year' => today()->year,
            'is_locked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all());

        Schema::create('attendances'.now()->year, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_weekoff')->default(false);
        });

        DB::table('attendances'.now()->year)->insert([
            ['user_id' => 2, 'date' => today(), 'in_time' => now(), 'out_time' => now(), 'is_holiday' => false, 'is_weekoff' => false],
            ['user_id' => 4, 'date' => today(), 'in_time' => null, 'out_time' => now(), 'is_holiday' => false, 'is_weekoff' => false],
            ['user_id' => 7, 'date' => today(), 'in_time' => now(), 'out_time' => now(), 'is_holiday' => true, 'is_weekoff' => false],
            ['user_id' => 8, 'date' => today(), 'in_time' => now(), 'out_time' => now(), 'is_holiday' => false, 'is_weekoff' => true],
            ['user_id' => 5, 'date' => today()->subDay(), 'in_time' => now(), 'out_time' => now(), 'is_holiday' => false, 'is_weekoff' => false],
            ['user_id' => 999, 'date' => today(), 'in_time' => now(), 'out_time' => now(), 'is_holiday' => false, 'is_weekoff' => false],
        ]);

        $user = new User();
        $user->forceFill([
            'id' => 1,
            'name' => 'Aarav Patel',
            'profile_pic' => 'employee-profiles/aarav.png',
            'dob' => '1990-03-10',
            'join_date' => '2020-06-15',
        ]);
        Auth::guard('tenant')->setUser($user);

        $response = app(DashboardController::class)->index();

        $this->assertSame('index', $response->name());
        $this->assertArrayHasKey('tenantId', $response->getData());
        $this->assertArrayHasKey('domain', $response->getData());
        $this->assertArrayHasKey('recentUsers', $response->getData());
        $this->assertSame('Aarav Patel', $response->getData()['currentUserName']);
        $this->assertSame('employee-profiles/aarav.png', $response->getData()['currentUserProfilePic']);
        $this->assertSame('1990-03-10', $response->getData()['currentUserDob']->toDateString());
        $this->assertSame('2020-06-15', $response->getData()['currentUserJoinDate']->toDateString());
        $this->assertSame(['Employee 2'], $response->getData()['birthdaysToday']->pluck('name')->all());
        $this->assertSame(['Employee 3'], $response->getData()['birthdaysTomorrow']->pluck('name')->all());
        $this->assertTrue($response->getData()['canApprovePendingRequests']);
        $this->assertSame(0, $response->getData()['pendingApprovalCount']);
        $this->assertCount(9, $response->getData()['applicationModules']);
        $this->assertSame(3, $response->getData()['attendanceCount']);
        $this->assertSame(8, $response->getData()['userCount']);
        $this->assertCount(2, $response->getData()['structureSections']);
        $this->assertCount(9, $response->getData()['structureSections'][0]['cards']);
        $this->assertCount(4, $response->getData()['structureSections'][1]['cards']);
        $this->assertSame(7, $response->getData()['structureSections'][1]['cards'][2]['count']);
        $this->assertSame(['HR', 'Engineering'], $response->getData()['employeeGroupings']['department']['categories']);
        $this->assertSame([3, 2], $response->getData()['employeeGroupings']['department']['data']);
        $this->assertSame([2, 1], $response->getData()['employeeGroupings']['department']['present']);
        $this->assertSame([0, 1], $response->getData()['employeeGroupings']['department']['absent']);
        $this->assertSame(['Alpha Co', 'Beta Co'], $response->getData()['employeeGroupings']['company']['categories']);
        $this->assertSame([2, 1], $response->getData()['employeeGroupings']['company']['data']);
        $this->assertSame(['Head Office', 'Branch'], $response->getData()['employeeGroupings']['location']['categories']);
        $this->assertSame([2, 1], $response->getData()['employeeGroupings']['location']['data']);
        $this->assertSame(5, $response->getData()['employeeGroupings']['department']['summaryCount']);
        $this->assertSame(3, $response->getData()['employeeGroupings']['department']['summaryPresentCount']);
        $this->assertSame(1, $response->getData()['employeeGroupings']['department']['summaryAbsentCount']);
        $this->assertCount(13, $response->getData()['employeeGroupings']);
        $this->assertSame([1], $response->getData()['employeeGroupings']['area']['data']);
        $this->assertSame([1], $response->getData()['employeeGroupings']['canteen_facility']['data']);
        $this->assertStringNotContainsString('/tenancy/assets/', asset('build/favicon.png'));
    }
}
