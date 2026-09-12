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
 * Class ManualAttendance
 *
 * @property int $id
 * @property int $user_id
 * @property int $shift_rotation_id
 * @property int $shift_id
 * @property int $shift_type
 * @property Carbon $in_time
 * @property Carbon $out_time
 * @property string|null $reason
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class ManualAttendanceApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'manual_attendance_applications';

    protected $casts = [
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'status' => 'int',
        'location_id' => 'int',
        'company_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        // 'user_id',
        'shift_rotation_id',
        'shift_type',
        'shift_id',
        'in_time',
        'out_time',
        'location_id',
        'company_id',
        'status',
        'reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'manual_attendance_application_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shift_rotation()
    {
        return $this->belongsTo(ShiftRotation::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
