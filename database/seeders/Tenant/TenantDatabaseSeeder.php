<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Location;
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

        User::query()->withoutGlobalScopes()->updateOrCreate(
            ['code' => '1067'],
            [
                'name' => 'Prit Patel',
                'fname' => 'Prit',
                'lname' => 'Patel',
                'user_type' => 'employee',
                'status' => 1,
                'is_locked' => false,
                'is_inactive' => false,
                'password' => 'Prit1908@04',
            ],
        );
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
