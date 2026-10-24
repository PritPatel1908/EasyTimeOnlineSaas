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
 * Class WeekOffSwapApplication
 *
 * @property int $id
 * @property string|null $reason
 * @property Carbon|null $week_date
 * @property Carbon|null $swap_date
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|Users[] $users
 * @property ApprovalStatus|null $approval_status
 */
class WeekOffSwapApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'week_off_swap_applications';

    protected $casts = [
        'week_date' => 'date',
        'swap_date' => 'date',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'reason',
        'week_date',
        'swap_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'week_date',
        'swap_date',
        'reason',
        'approval_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'week_off_swap_application_users')
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

    public function WeekOffMuster()
    {
        return $this->belongsToMany(WeekOffMuster::class);
    }
}
