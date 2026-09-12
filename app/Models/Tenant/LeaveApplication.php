<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LeaveApplication
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $leave_type_id
 * @property int|null $leave_reason_id
 * @property string|null $reason_explanation
 * @property bool $is_half_day
 * @property Carbon|null $from_date
 * @property bool $is_second_half
 * @property Carbon|null $to_date
 * @property bool $is_first_half
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property LeaveReason|null $leave_reason
 * @property LeaveType|null $leave_type
 */
class LeaveApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'leave_applications';

    // protected static function booted(): void
    // {
    // 	static::addGlobalScope(new DataPolicyFilter);
    // }

    protected $casts = [
        'user_id' => 'int',
        'leave_type_id' => 'int',
        'leave_reason_id' => 'int',
        'is_only_second_half' => 'bool',
        'application_date' => 'datetime',
        'from_date' => 'datetime',
        'is_second_half' => 'bool',
        'to_date' => 'datetime',
        'is_first_half' => 'bool',
        'leave_count' => 'decimal:2',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'leave_reason_id',
        'reason_explanation',
        'is_only_second_half',
        'application_date',
        'from_date',
        'is_second_half',
        'to_date',
        'is_first_half',
        'leave_count',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function Users()
    // {
    //     return $this->belongsToMany(User::class, 'leave_application_users')
    //         ->withPivot('id')
    //         ->withTimestamps();
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave_reason()
    {
        return $this->belongsTo(LeaveReason::class);
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
