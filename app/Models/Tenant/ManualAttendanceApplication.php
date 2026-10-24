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

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'in_time',
        'out_time',
        'reason',
        'approval_status',
    ];

    protected $table = 'manual_attendance_applications';

    protected $casts = [
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        // 'user_id',
        'in_time',
        'out_time',
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

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }

    public function approval_statuses()
    {
        return $this->morphMany(ApprovalStatus::class, 'approvable');
    }

    public function overallApprovalStatus(): string
    {
        $statuses = $this->relationLoaded('approval_statuses')
            ? $this->approval_statuses
            : $this->approval_statuses()->get();

        if ($statuses->isEmpty()) {
            return 'unknown';
        }

        if ($statuses->contains('approval_status', 'rejected')) {
            return 'rejected';
        }

        if ($statuses->contains(static fn (ApprovalStatus $status): bool => in_array(
            $status->approval_status,
            ['draft', 'pending'],
            true,
        ))) {
            return 'pending';
        }

        return $statuses->every(
            static fn (ApprovalStatus $status): bool => $status->approval_status === 'approved',
        ) ? 'approved' : 'unknown';
    }
}
