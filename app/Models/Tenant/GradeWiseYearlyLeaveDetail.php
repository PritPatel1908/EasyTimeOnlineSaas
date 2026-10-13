<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class GradeWiseYearlyLeaveDetail
 *
 * @property int $id
 * @property int|null $min_utilization
 * @property int $based_on
 * @property int|null $grade_wise_leave_id
 * @property int|null $leave_type_id
 * @property int|null $sandwich_leave_id
 * @property int|null $eligible
 * @property int|null $max_accumulation
 * @property bool|null $allow_c_f
 * @property bool|null $opening
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GradeWiseYearlyLeaveDetail extends Model
{
    // use SoftDeletes;
    // use CUDby;
    protected $table = 'grade_wise_yearly_leave_details';

    protected $casts = [
        'grade_wise_leave_id' => 'int',
        'leave_type_id' => 'int',
        'eligible' => 'int',
        // 'allow_c_f' => 'int',
        'allow_c_f' => 'bool',
        'maximum_request_in_a_month' => 'int',
        'maximum_request_in_a_year' => 'int',
        'eligible_after_days' => 'int',
        'credit_after_days' => 'int',
        // 'based_on' => 'int',
        'allow_sandwich' => 'bool',
        'sandwich_leave_id' => 'int',
        'min_utilization' => 'int',
        'max_accumulation' => 'int',
        'is_paid' => 'bool',
        'keeps_balance' => 'bool',
        'allow_negative_balance' => 'bool',
        'negative_balance' => 'decimal:2',
        'min_avail' => 'decimal:2',
        'max_avail' => 'decimal:2',
        'leave_types_not_to_be_clubbed' => 'array',
        'allow_leave_encashment' => 'bool',
        'allow_leave_lapse' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'grade_wise_leave_id',
        'leave_type_id',
        'eligible',
        'allow_c_f',
        'maximum_request_in_a_month',
        'maximum_request_in_a_year',
        'eligible_after_days',
        'credit_after_days',
        'opening',
        'based_on',
        'allow_sandwich',
        'sandwich_leave_id',
        'min_utilization',
        'max_accumulation',
        'is_paid',
        'keeps_balance',
        'allow_negative_balance',
        'negative_balance',
        'min_avail',
        'max_avail',
        'leave_types_not_to_be_clubbed',
        'leave_credit_type',
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
