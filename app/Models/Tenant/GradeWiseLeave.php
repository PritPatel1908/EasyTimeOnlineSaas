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
 * Class GradeWiseLeave
 *
 * @property int $id
 * @property string $name
 * @property int $financial_year_id
 * @property bool|null $is_monthly
 * @property bool|null $allow_leave_encashment
 * @property bool|null $allow_leave_lapse
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GradeWiseLeave extends Model
{
    use SoftDeletes;

    // use CUDby;
    protected $table = 'grade_wise_leaves';

    protected $casts = [
        'financial_year_id' => 'int',
        'is_monthly' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'financial_year_id',
        'is_monthly',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function grade_wise_yearly_leave_details()
    {
        return $this->hasMany(GradeWiseYearlyLeaveDetail::class);
    }

    public function grade_wise_monthly_leave_details()
    {
        return $this->hasMany(GradeWiseMonthlyLeaveDetail::class);
    }

    public function financial_year()
    {
        return $this->belongsTo(FinancialYear::class);
    }
}
