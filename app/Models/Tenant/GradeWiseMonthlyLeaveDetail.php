<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GradeWiseMonthlyLeaveDetail
 *
 * @property int $id
 * @property int|null $grade_wise_leave_id
 * @property int|null $leave_type_id
 * @property int|null $sandwich_leave_id
 * @property int|null $increment
 * @property int|null $max_accumulation
 * @property bool|null $opening
 * @property int|null $allow_backdate_leave
 * @property bool|null $backdate_day_limit
 * @property int|null $allow_advance_leave
 * @property bool|null $advance_day_limit
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GradeWiseMonthlyLeaveDetail extends Model
{
    // use SoftDeletes;
    // use CUDby;
    protected $table = 'grade_wise_monthly_leave_details';

    protected $casts = [
        'grade_wise_leave_id' => 'int',
        'leave_type_id' => 'int',
        'allow_c_f' => 'bool',
        'maximum_request_in_a_month' => 'int',
        'maximum_request_in_a_year' => 'int',
        'eligible_after_days' => 'int',
        'credit_after_days' => 'int',
        'min_avail' => 'decimal:2',
        'max_avail' => 'decimal:2',
        'leave_types_not_to_be_clubbed' => 'array',
        'increment' => 'int',
        // 'proportionate' => 'int',
        // 'start_credit' => 'int',
        'credit_after_days' => 'int',
        // 'start_use_based_on' => 'int',
        'start_use_after_days' => 'int',
        'allow_sandwich' => 'bool',
        'count_weekly_off_as_present' => 'bool',
        'count_holiday_as_present' => 'bool',
        'sandwich_leave_id' => 'int',
        'allow_backdate_leave' => 'bool',
        'backdate_day_limit' => 'int',
        'allow_advance_leave' => 'bool',
        'advance_day_limit' => 'int',
        'max_accumulation' => 'int',
        'is_paid' => 'bool',
        'keeps_balance' => 'bool',
        'allow_negative_balance' => 'bool',
        'allow_leave_lapse' => 'bool',
        'negative_balance' => 'decimal:2',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'grade_wise_leave_id',
        'leave_type_id',
        'allow_c_f',
        'maximum_request_in_a_month',
        'maximum_request_in_a_year',
        'eligible_after_days',
        'credit_after_days',
        'min_avail',
        'max_avail',
        'leave_types_not_to_be_clubbed',
        'leave_credit_type',
        'increment',
        'proportionate',
        'count_weekly_off_as_present',
        'count_holiday_as_present',
        'start_credit',
        'credit_after_days',
        'start_use_based_on',
        'start_use_after_days',
        'allow_sandwich',
        'sandwich_leave_id',
        'allow_backdate_leave',
        'backdate_day_limit',
        'allow_advance_leave',
        'advance_day_limit',
        'max_accumulation',
        'is_paid',
        'keeps_balance',
        'allow_negative_balance',
        'negative_balance',
        'allow_leave_encashment',
        'allow_leave_lapse',
        'allow_leave_encashment',
        'allow_leave_lapse',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function grade_wise_leave()
    {
        return $this->belongsTo(GradeWiseLeave::class);
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function sandwich_leave()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
