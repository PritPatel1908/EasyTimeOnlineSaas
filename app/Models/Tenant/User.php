<?php

namespace App\Models\Tenant;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\Scopes\HideSuperAdminScope;
use App\Traits\CUby;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $location_id
 * @property int|null $company_id
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property int|null $department_id
 * @property int|null $sub_department_id
 * @property int|null $shift_change_approval_flow_id
 * @property int|null $week_off_change_approval_flow_id
 * @property int|null $week_off_swap_approval_flow_id
 * @property int|null $manual_punch_approval_flow_id
 * @property int|null $manual_attendance_approval_flow_id
 * @property int|null $leave_approval_flow_id
 * @property int|null $short_leave_approval_flow_id
 * @property int|null $designation_id
 * @property int|null $grade_id
 * @property int|null $unit_id
 * @property int|null $bus_route_id
 * @property Carbon|null $rejoin_date
 * @property string|null $rejoin_reason
 * @property string|null $code
 * @property string|null $fname
 * @property string|null $mname
 * @property string|null $lname
 * @property string|null $card
 * @property string|null $number
 * @property string|null $user_type
 * @property string|null $status
 * @property bool|null $is_locked
 * @property bool|null $shift_status
 * @property string|null $profile_pic
 * @property Carbon|null $inactive_date
 * @property Carbon|null $dob
 * @property Carbon|null $join_date
 * @property Carbon|null $left_date
 * @property string|null $left_reason
 * @property int|null $last_check_id
 * @property int|null $data_policy_id
 * @property int|null $role_id
 * @property int|null $shift_id
 * @property int|null $leave_group_id
 * @property int|null $grade_wise_leave_id
 * @property int|null $late_coming_rule_id
 * @property int|null $early_going_rule_id
 * @property int|null $half_day_rule_id
 * @property int|null $absent_rule_id
 * @property int|null $overtime_rule_id
 * @property int|null $designation_id
 * @property int|null $unit_id
 * @property Carbon|null $last_active_date
 * @property Carbon|null $last_check_date
 * @property int|null $bus_route_id
 * @property int|null $grade_id
 * @property string|null $gender
 * @property int|null $password_policy_id
 * @property Collection|LeaveApplication[] $leave_applications
 * @property Collection|ShortLeaveApplication[] $short_leave_applications
 * @property Collection|Shift[] $shifts
 * @property Collection|ShiftChange[] $shift_changes
 * @property Collection|Designation[] $designation
 * @property Collection|Unit[] $unit
 * @property Collection|BusRoute[] $bus_route
 * @property Collection|Grade[] $grade
 * @property Collection|ShiftChangeShift[] $shift_change_shifts
 * @property Collection|Coff[] $coffs
 * @property ShiftMusters $shift_musters
 * @property WeekOffMusters $week_off_musters
 * @property StatusMusters $status_musters
 * @property Collection|ApprovalFlow[] $approval_flows
 * @property Collection|ApprovalFlow[] $shift_change_approval_flow
 * @property Collection|ApprovalFlow[] $week_off_change_approval_flow
 * @property Collection|ApprovalFlow[] $week_off_swap_approval_flow
 * @property Collection|ApprovalFlow[] $manual_punch_approval_flow
 * @property Collection|ApprovalFlow[] $manual_attendance_approval_flow
 * @property Collection|ApprovalFlow[] $leave_approval_flow
 * @property AbsentRule|null $absent_rule
 * @property Company|null $company
 * @property Category|null $category
 * @property SuvCategory|null $sub_category
 * @property DataPolicy|null $data_policy
 * @property Department|null $department
 * @property EarlyGoingRule|null $early_going_rule
 * @property HalfDayRule|null $half_day_rule
 * @property LateComingRule|null $late_coming_rule
 * @property LeaveGroup|null $leave_group
 * @property GradeWiseLeave|null $leave_group
 * @property Location|null $location
 * @property OvertimeRule|null $overtime_rule
 * @property Role|null $role
 * @property Shift|null $shift
 * @property SubDepartment|null $sub_department
 * @property Collection|LeaveGroup[] $leave_groups
 * @property Collection|LeaveGroup[] $grade_wise_leaves
 * @property Collection|OrganizationUser[] $organization_users
 * @property Collection|PersonalInfo[] $personal_info
 * @property Collection|ManualAttendance[] $manual_attendances
 * @property Collection|ShiftRotation[] $shift_rotations
 * @property Collection|UserWeekOff[] $user_week_offs
 * @property Collection|week_off_changes[] $week_off_changes
 * @property Collection|LeaveAccount[] $leave_account
 * @property Collection|Address[] $addresses
 * @property Collection|Document[] $documents
 * @property Collection|Education[] $education
 * @property Collection|Experience[] $experiences
 * @property Collection|FamilyDetail[] $family_details
 */
class User extends Authenticatable implements FilamentUser, HasTenants
{
    use CUby;
    use HasFactory, Notifiable;
    use HasPanelShield;
    use HasRoles;
    use SoftDeletes;

    public function getDefaultGuardName(): string
    {
        return 'tenant';
    }

    protected static function booted(): void
    {
        if (Auth::user() != null && Auth::id() != 1) {
            static::addGlobalScope(new HideSuperAdminScope);
        }
        static::addGlobalScope(new DataPolicyFilter);
    }

    public function getFilamentName(): string
    {
        return "{$this->name} ({$this->code})";
    }

    protected $table = 'users';

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_locked) {
            Notification::make()
                ->title('Login Failed')
                ->body('User is locked or not active')
                ->danger()
                ->send();

            return false;
        }

        $generalConfig = GeneralConfiguration::where('key', 'password_attempts_count')->first();
        if ($generalConfig !== null && $this->login_attempts >= $generalConfig->value) {
            Notification::make()
                ->title('Login Failed')
                ->body('User is inactive')
                ->danger()
                ->send();

            return false;
        }

        return true; // str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'location_id' => 'array',
        'company_id' => 'array',
        'category_id' => 'array',
        'sub_category_id' => 'array',
        'department_id' => 'array',
        'sub_department_id' => 'array',
        'designation_id' => 'array',
        'grade_id' => 'array',
        'unit_id' => 'array',
        'bus_route_id' => 'array',
        'is_locked' => 'bool',
        'allow_phone_login' => 'bool',
        'dob' => 'datetime',
        'join_date' => 'datetime',
        'left_date' => 'datetime',
        'data_policy_id' => 'int',
        'last_check_id' => 'int',
        // 'role_id' => 'int',
        'shift_id' => 'int',
        'rejoin_date' => 'datetime',
        'leave_group_id' => 'int',
        'grade_wise_leave_id' => 'int',
        'late_coming_rule_id' => 'int',
        'early_going_rule_id' => 'int',
        'half_day_rule_id' => 'int',
        'absent_rule_id' => 'int',
        'overtime_rule_id' => 'int',
        'is_inactive' => 'bool',
        'shift_status' => 'bool',
        'inactive_days' => 'int',
        'inactive_date' => 'date',
        'last_check_date' => 'date',
        'last_active_at' => 'datetime',
        'last_login_at' => 'datetime',
        'short_leave_minutes' => 'int',
        'password_policy_id' => 'int',
        'shift_rotation_id' => 'int',
        'approval_flow_id' => 'int',
        'shift_change_approval_flow_id' => 'int',
        'week_off_change_approval_flow_id' => 'int',
        'week_off_swap_approval_flow_id' => 'int',
        'manual_punch_approval_flow_id' => 'int',
        'manual_attendance_approval_flow_id' => 'int',
        'leave_approval_flow_id' => 'int',
        'short_leave_approval_flow_id' => 'int',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'location_id',
        'company_id',
        'category_id',
        'sub_category_id',
        'department_id',
        'sub_department_id',
        'designation_id',
        'grade_id',
        'unit_id',
        'bus_route_id',
        'code',
        'fname',
        'mname',
        'lname',
        'card',
        'number',
        'user_type',
        'status',
        'is_locked',
        'allow_phone_login',
        'profile_pic',
        'dob',
        'join_date',
        'last_check_date',
        'last_check_id',
        'left_date',
        'left_reason',
        'data_policy_id',
        // 'role_id',
        'shift_id',
        'rejoin_date',
        'rejoin_reason',
        'leave_group_id',
        'grade_wise_leave_id',
        'late_coming_rule_id',
        'early_going_rule_id',
        'half_day_rule_id',
        'absent_rule_id',
        'overtime_rule_id',
        'gender',
        'password_policy_id',
        'is_inactive',
        'inactive_days',
        'inactive_date',
        'login_attempts',
        'created_by',
        'updated_by',
        'deleted_by',
        'password_changed_at',
        'shift_type',
        'approval_flow_id',
        'shift_change_approval_flow_id',
        'week_off_change_approval_flow_id',
        'week_off_swap_approval_flow_id',
        'manual_punch_approval_flow_id',
        'manual_attendance_approval_flow_id',
        'leave_approval_flow_id',
        'short_leave_approval_flow_id',
        'shift_status',
        'short_leave_minutes',
        'aadhar_number',
        'uan_number',
        'esic_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function absent_rule()
    {
        return $this->belongsTo(AbsentRule::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'user_area', 'user_id', 'area_id')
            // return $this->belongsToMany(Area::class)
            ->withTimestamps();
    }

    public function data_policy()
    {
        return $this->belongsTo(DataPolicy::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function early_going_rule()
    {
        return $this->belongsTo(EarlyGoingRule::class);
    }

    public function half_day_rule()
    {
        return $this->belongsTo(HalfDayRule::class);
    }

    public function late_coming_rule()
    {
        return $this->belongsTo(LateComingRule::class);
    }

    public function leave_group()
    {
        return $this->belongsTo(LeaveGroup::class);
    }

    public function grade_wise_leave()
    {
        return $this->belongsTo(GradeWiseLeave::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function leave_applications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function short_leave_applications()
    {
        return $this->hasMany(ShortLeaveApplication::class);
    }

    public function manual_punches()
    {
        return $this->hasMany(ManualPunch::class);
    }

    public function manual_attendances()
    {
        return $this->hasMany(ManualAttendance::class);
    }

    public function overtime_rule()
    {
        return $this->belongsTo(OvertimeRule::class);
    }

    // public function role()
    // {
    // 	return $this->belongsTo(Role::class);
    // }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function sub_department()
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function leave_groups()
    {
        return $this->hasMany(LeaveGroup::class);
    }

    public function personal_info()
    {
        return $this->hasOne(PersonalInfo::class);
    }

    public function password_histories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function password_policy()
    {
        return $this->belongsTo(PasswordPolicy::class, 'password_policy_id');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'user_shifts', 'user_id', 'shift_id')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function shift_rotation()
    {
        return $this->belongsTo(ShiftRotation::class);
    }

    public function shift_changes()
    {
        return $this->hasMany(ShiftChange::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function bus_route()
    {
        return $this->belongsTo(BusRoute::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function shift_change_shifts()
    {
        return $this->hasMany(ShiftChangeShift::class);
    }

    public function coffs()
    {
        return $this->hasMany(Coff::class);
    }

    public function shift_musters()
    {
        return $this->hasMany(ShiftMuster::class);
    }

    // public function week_off_musters()
    // {
    //     return $this->hasMany(WeekOffMuster::class);
    // }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'authable');
    }

    public function week_off_musters(): MorphMany
    {
        return $this->morphMany(WeekOffMuster::class, 'weekoffable');
    }

    public function status_musters()
    {
        return $this->hasMany(StatusMuster::class);
    }

    public function user_week_offs()
    {
        return $this->hasMany(UserWeekOff::class);
    }

    public function week_off_changes()
    {
        return $this->hasMany(WeekOffChange::class);
    }

    public function approval_statuses()
    {
        return $this->hasMany(ApprovalStatus::class, 'approvable_id', 'id');
    }

    public function shift_change_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'shift_change_approval_flow_id', 'id');
    }

    public function week_off_change_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'week_off_change_approval_flow_id', 'id');
    }

    public function week_off_swap_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'week_off_swap_approval_flow_id', 'id');
    }

    public function manual_punch_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'manual_punch_approval_flow_id', 'id');
    }

    public function manual_attendance_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'manual_attendance_approval_flow_id', 'id');
    }

    public function leave_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'leave_approval_flow_id', 'id');
    }

    public function short_leave_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'short_leave_approval_flow_id', 'id');
    }

    public function coff_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'coff_approval_flow_id', 'id');
    }

    public function od_approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'od_approval_flow_id', 'id');
    }

    public function approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id', 'id');
    }

    public function leave_account()
    {
        return $this->hasOne(LeaveAccount::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function education()
    {
        return $this->hasMany(Education::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function family_details()
    {
        return $this->hasMany(FamilyDetail::class);
    }

    public function leave_balances()
    {
        return $this->belongsToMany(ManualLeaveBalance::class, 'manual_leave_balance_users')
            ->withPivot('id')
            ->withTimestamps();
    }
}
