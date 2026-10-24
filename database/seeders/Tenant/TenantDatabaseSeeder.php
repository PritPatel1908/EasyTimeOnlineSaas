<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Helpers\AfterUserImportProcessor;
use App\Models\Tenant\AbsentRule;
use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\EarlyGoingRule;
use App\Models\Tenant\Grade;
use App\Models\Tenant\HalfDayRule;
use App\Models\Tenant\LateComingRule;
use App\Models\Tenant\LegacyLeaveGroup as LeaveGroup;
use App\Models\Tenant\Location;
use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\PasswordPolicy;
use App\Models\Tenant\Shift;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->withoutGlobalScopes()->first();
        $shouldSeedTenantData = ! Location::query()->exists();

        if ($user === null) {
            $user = User::query()->withoutGlobalScopes()->create();
        }

        Auth::setUser($user);

        if ($shouldSeedTenantData) {
            $this->logCreatedActivity($user, $user);

            Event::listen('eloquent.created: *', function (mixed ...$arguments) use ($user): void {
                $payload = $arguments[1] ?? [];

                if (! is_array($payload)) {
                    return;
                }

                $model = $payload[0] ?? null;

                if (! $model instanceof Model || ! str_starts_with($model::class, 'App\\Models\\Tenant\\')) {
                    return;
                }

                $this->logCreatedActivity($model, $user);
            });

            try {
                $this->call([
                    LocationSeeder::class,
                    CompanySeeder::class,
                    DepartmentSeeder::class,
                    SubDepartmentSeeder::class,
                    CategorySeeder::class,
                    SubCategorySeeder::class,
                    DataPolicySeeder::class,
                    PasswordPolicySeeder::class,
                    DesignationSeeder::class,
                    GradeSeeder::class,
                    UnitSeeder::class,
                    BusRouteSeeder::class,
                    SettingSeeder::class,
                    LeaveTypeSeeder::class,
                    ShiftSeeder::class,
                    AbsentRuleSeeder::class,
                    OverTimeRuleSeeder::class,
                    LateComingRuleSeeder::class,
                    HalfDayRuleSeeder::class,
                    EarlyGoingRuleSeeder::class,
                    LeaveGroupSeeder::class,
                    PasswordHistorySeeder::class,
                    LeaveReasonSeeder::class,
                    AreaSeeder::class,
                    HolidaySeeder::class,
                    ApprovalFlowSeeder::class,
                    DocumentTypeMasterSeeder::class,
                    FinancialYearSeeder::class,
                    GradeWiseLeaveSeeder::class,
                ]);
            } finally {
                Event::forget('eloquent.created: *');
            }
        }

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'reference_user_id' => $user->id,
            ])
            ->event('seeded')
            ->log('Tenant data seeded');

        $user->update([
            'name' => 'superadmin',
            'fname' => 'super',
            'lname' => 'admin',
            'code' => 'superadmin',
            'user_type' => 'employee',
            'status' => 1,
            'is_locked' => false,
            'is_inactive' => false,
            'password' => 'indian@super',
        ]);

        $locations = Location::query()->orderBy('id')->pluck('id')->all();
        $companies = Company::query()->orderBy('id')->pluck('id')->all();
        $departments = Department::query()->orderBy('id')->pluck('id')->all();
        $subDepartments = SubDepartment::query()->orderBy('id')->pluck('id')->all();
        $category = Category::query()->first();
        $subCategories = SubCategory::query()->orderBy('id')->pluck('id')->all();
        $designation = Designation::query()->first();
        $grades = Grade::query()->orderBy('id')->pluck('id')->all();
        $units = Unit::query()->orderBy('id')->pluck('id')->all();
        $busRoutes = BusRoute::query()->orderBy('id')->pluck('id')->all();
        $shifts = Shift::query()->where(fn ($query) => $query->where('status', 1)->orWhereNull('status'))->orderBy('id')->pluck('id')->all();
        $leaveGroup = LeaveGroup::query()->first();
        $team = Team::query()->first();
        $canteenFacility = CanteenFacility::query()->first();
        $dataPolicy = DataPolicy::query()->first();
        $passwordPolicy = PasswordPolicy::query()->first();
        $approvalFlow = ApprovalFlow::query()->first();
        $lateComingRule = LateComingRule::query()->first();
        $earlyGoingRule = EarlyGoingRule::query()->first();
        $halfDayRule = HalfDayRule::query()->first();
        $absentRule = AbsentRule::query()->first();
        $overtimeRule = OvertimeRule::query()->first();
        $bloodGroup = BloodGroup::query()->first();

        $employee = User::query()->withoutGlobalScopes()->updateOrCreate(
            ['code' => '1067'],
            [
                'name' => 'Prit Patel',
                'fname' => 'Prit',
                'mname' => 'Kumar',
                'lname' => 'Patel',
                'card' => 'EMP-1067',
                'email' => 'prit.patel.1067@example.test',
                'number' => '0000001067',
                'gender' => 'male',
                'dob' => '1998-04-19',
                'blood_group_id' => $bloodGroup?->id,
                'join_date' => '2022-06-01',
                'user_type' => 'employee',
                'status' => 1,
                'is_locked' => false,
                'is_inactive' => false,
                'allow_mobile_login' => true,
                'allow_mobile_punch' => true,
                'password' => 'Prit1908@04',
                'aadhar_number' => 'TEST-AADHAR-1067',
                'uan_number' => 'TEST-UAN-1067',
                'esic_number' => 'TEST-ESIC-1067',
                'pan_number' => 'TEST-PAN-1067',
                'bank_name' => 'HDFC Bank',
                'bank_account_number' => '000000001067',
                'bank_ifsc_code' => 'TEST00001067',
                'bank_account_branch_name' => 'Test Branch 1067',
                'location_id' => $locations,
                'company_id' => $companies,
                'department_id' => $departments,
                'sub_department_id' => $subDepartments,
                'category_id' => $category ? [$category->id] : [],
                'sub_category_id' => $subCategories,
                'designation_id' => $designation ? [$designation->id] : [],
                'grade_id' => $grades,
                'unit_id' => $units,
                'bus_route_id' => $busRoutes,
                'leave_group_id' => $leaveGroup?->id,
                'team_id' => $team?->id,
                'canteen_facility_id' => $canteenFacility?->id,
                'data_policy_id' => $dataPolicy?->id,
                'password_policy_id' => $passwordPolicy?->id,
                'shift_type' => 'auto',
                'shift_rotation_id' => null,
                'late_coming_rule_id' => $lateComingRule?->id,
                'early_going_rule_id' => $earlyGoingRule?->id,
                'half_day_rule_id' => $halfDayRule?->id,
                'absent_rule_id' => $absentRule?->id,
                'overtime_rule_id' => $overtimeRule?->id,
                'shift_change_approval_flow_id' => $approvalFlow?->id,
                'week_off_change_approval_flow_id' => $approvalFlow?->id,
                'week_off_swap_approval_flow_id' => $approvalFlow?->id,
                'manual_punch_approval_flow_id' => $approvalFlow?->id,
                'manual_attendance_approval_flow_id' => $approvalFlow?->id,
                'leave_approval_flow_id' => $approvalFlow?->id,
                'short_leave_approval_flow_id' => $approvalFlow?->id,
                'coff_approval_flow_id' => $approvalFlow?->id,
                'od_approval_flow_id' => $approvalFlow?->id,
            ],
        );

        $employee->shifts()->sync($shifts);

        $employee->personalInfo()->updateOrCreate([], [
            'height' => 178,
            'weight' => 72,
            'languages' => ['Gujarati', 'Hindi', 'English'],
            'hobbies' => ['Cricket', 'Reading', 'Travel'],
            'emergency_name' => json_encode(['Sample Patel'], JSON_THROW_ON_ERROR),
            'emergency_number' => json_encode(['0000002067'], JSON_THROW_ON_ERROR),
            'emergency_address' => json_encode(['Thaltej, Ahmedabad, Gujarat'], JSON_THROW_ON_ERROR),
        ]);

        $employee->addresses()->updateOrCreate(['is_current_address' => true], [
            'flat_building' => 'Sample Heights',
            'house_no' => 'A-204',
            'flore' => '2nd Floor',
            'street' => 'SG Highway',
            'landmark' => 'Near Thaltej Cross Road',
            'country' => 'India',
            'state' => 'Gujarat',
            'city' => 'Ahmedabad',
            'pincode' => '380054',
            'current_flat_building' => 'Sample Heights',
            'current_house_no' => 'A-204',
            'current_flore' => '2nd Floor',
            'current_street' => 'SG Highway',
            'current_landmark' => 'Near Thaltej Cross Road',
            'current_country' => 'India',
            'current_state' => 'Gujarat',
            'current_city' => 'Ahmedabad',
            'current_pincode' => '380054',
        ]);

        $employee->education()->delete();
        $employee->education()->create([
            'education' => 'Bachelor of Technology',
            'year' => 2019,
            'university_name' => 'Gujarat Technological University',
        ]);

        $employee->experiences()->delete();
        $employee->experiences()->create([
            'previous_employeer' => 'Sample Systems Pvt. Ltd.',
            'designation' => 'Junior Software Engineer',
            'join_date' => '2020-01-01',
            'left_date' => '2022-05-15',
            'ctc' => 450000,
        ]);

        $employee->family_details()->delete();
        $employee->family_details()->createMany([
            [
                'relation' => 'Father',
                'name' => 'Sample Patel',
                'dob' => '1968-03-12',
                'is_nominee' => false,
                'occupation' => 'Business',
            ],
            [
                'relation' => 'Mother',
                'name' => 'Demo Patel',
                'dob' => '1972-08-24',
                'is_nominee' => true,
                'occupation' => 'Homemaker',
            ],
        ]);

        $employee->user_week_offs()->delete();
        $employee->user_week_offs()->create([
            'week_days' => 0,
            'wo_type' => 1,
            'first_week' => true,
            'second_week' => true,
            'third_week' => true,
            'fourth_week' => true,
            'fifth_week' => true,
        ]);

        AfterUserImportProcessor::processAfterImportUsersSynchronously([$employee->code]);
    }

    private function logCreatedActivity(Model $model, User $user): void
    {
        activity()
            ->causedBy($user)
            ->performedOn($model)
            ->withProperties([
                'attributes' => $model->getAttributes(),
            ])
            ->event('created')
            ->log('created');
    }
}
