<?php

namespace App\Models\Tenant;

use App\Support\ActivityLogger;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use App\Traits\HasRelatedRecords;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected array $auditLogOldSnapshot = [];
    public const IMPORT_EXPORT_COLUMNS = [
        'name',
        'code',
        'email',
        'status',
        'company',
        'location',
        'canteen_break_limit',
        'need_approval_for_overtime',
        'regular_ot_on_wo',
        'bypass_timing_rule',
        'ignore_before_after_shift_punch',
        'fix_work_hours',
        'fix_work_hours_as_per_shift',
        'fix_work_hours_value',
        'ignore_break_in_attendance',
        'reset_halfday_rule_cycle',
        'give_double_ot_in_public_holiday',
        'give_double_coff_in_public_holiday',
        'is_week_off_paid',
        'is_holiday_paid',
        'single_punch_allowed_present',
        'single_punch_allowed_half_day',
        'max_short_leave_minutes_per_month',
        'max_short_leave_minutes_per_application',
        'max_occurance_of_short_leave_in_month',
        'advance_short_leave_application',
        'is_eligible_for_c_off',
        'c_off_lapse_in_days',
        'allow_halfday_c_off',
        'allow_backdated_leave',
        'backdated_day_limit',
        'advance_day_limit',
        'maximum_accumulation',
        'maximum_request_in_a_month',
        'maximum_request_in_a_year',
        'leave_type',
        'min_avail',
        'max_avail',
        'skip_overtime',
    ];
    protected $table = 'categories';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
        static::created(fn(Category $category) => $category->dispatchAuditLog([], $category->getAttributes(), 'created'));
        static::updating(function (Category $category): void {
            $category->auditLogOldSnapshot = $category->getOriginal();
        });
        static::updated(fn(Category $category) => $category->dispatchAuditLog($category->auditLogOldSnapshot, $category->getAttributes(), 'updated'));
        static::deleting(function (Category $category): void {
            $category->auditLogOldSnapshot = $category->getAttributes();
        });
        static::deleted(fn(Category $category) => $category->dispatchAuditLog($category->auditLogOldSnapshot, $category->getAttributes(), 'deleted'));
    }

    protected $casts = [
        'location_id' => 'array',
        'company_id' => 'array',
        'status' => 'int',
        'need_approval_for_overtime' => 'bool',
        'regular_ot_on_wo' => 'bool',
        'bypass_timing_rule' => 'bool',
        'ignore_before_after_shift_punch' => 'bool',
        'fix_work_hours' => 'bool',
        'fix_work_hours_as_per_shift' => 'bool',
        'ignore_break_in_attendance' => 'bool',
        'reset_halfday_rule_cycle' => 'bool',
        'give_double_ot_in_public_holiday' => 'bool',
        'give_double_coff_in_public_holiday' => 'bool',
        'is_week_off_paid' => 'bool',
        'is_holiday_paid' => 'bool',
        'single_punch_allowed_present' => 'bool',
        'single_punch_allowed_half_day' => 'bool',
        'max_short_leave_minutes_per_month' => 'int',
        'max_short_leave_minutes_per_application' => 'int',
        'max_occurance_of_short_leave_in_month' => 'int',
        'advance_short_leave_application' => 'int',
        'is_eligible_for_c_off' => 'bool',
        'c_off_lapse_in_days' => 'int',
        'backdated_day_limit' => 'int',
        'advance_day_limit' => 'int',
        'maximum_accumulation' => 'int',
        'maximum_request_in_a_month' => 'int',
        'maximum_request_in_a_year' => 'int',
        'leave_type_id' => 'array',
        'min_avail' => 'decimal:2',
        'max_avail' => 'decimal:2',
        'allow_halfday_c_off' => 'bool',
        'allow_backdated_leave' => 'bool',
        'skip_overtime' => 'datetime',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'location_id',
        'company_id',
        'canteen_break_limit',
        'need_approval_for_overtime',
        'regular_ot_on_wo',
        'bypass_timing_rule',
        'ignore_before_after_shift_punch',
        'fix_work_hours',
        'fix_work_hours_as_per_shift',
        'fix_work_hours_value',
        'ignore_break_in_attendance',
        'reset_halfday_rule_cycle',
        'give_double_ot_in_public_holiday',
        'give_double_coff_in_public_holiday',
        'is_week_off_paid',
        'is_holiday_paid',
        'single_punch_allowed_present',
        'single_punch_allowed_half_day',
        'max_short_leave_minutes_per_month',
        'max_short_leave_minutes_per_application',
        'max_occurance_of_short_leave_in_month',
        'advance_short_leave_application',
        'is_eligible_for_c_off',
        'c_off_lapse_in_days',
        'allow_halfday_c_off',
        'allow_backdated_leave',
        'backdated_day_limit',
        'advance_day_limit',
        'maximum_accumulation',
        'maximum_request_in_a_month',
        'maximum_request_in_a_year',
        'leave_type_id',
        'min_avail',
        'max_avail',
        'status',
        'skip_overtime',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class, 'data_policy_category')->withPivot('id')->withTimestamps();
    }
    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class);
    }
    public function holidays()
    {
        return $this->belongsToMany(Holiday::class)->withPivot('id')->withTimestamps();
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function c_off_against_wo_hl_slabs()
    {
        return $this->hasMany(CoffAgainstWoHlSlab::class);
    }
    public function c_off_against_ot_slabs()
    {
        return $this->hasMany(CoffAgainstOtSlab::class);
    }
    public function getLocationsAttribute(): Collection
    {
        return Location::query()->whereIn('id', $this->idsFrom($this->getRawOriginal('location_id')))->orderBy('name')->get();
    }
    public function getCompaniesAttribute(): Collection
    {
        return Company::query()->whereIn('id', $this->idsFrom($this->getRawOriginal('company_id')))->orderBy('name')->get();
    }
    public function locations(): Collection
    {
        return $this->getLocationsAttribute();
    }
    public function companies(): Collection
    {
        return $this->getCompaniesAttribute();
    }
    public static function getImportUniqueFields(): array
    {
        return ['code', 'name'];
    }
    private function dispatchAuditLog(array $old, array $new, string $event): void
    {
        ActivityLogger::log(Auth::guard('tenant')->user(), $this, $old, $new, $event);
    }
    private function idsFrom(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }
        return array_values(array_filter((array) $value, static fn($id): bool => is_int($id) || ctype_digit((string) $id)));
    }
}
