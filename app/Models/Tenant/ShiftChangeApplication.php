<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShiftChangeApplication
 *
 * @property int $id
 * @property string|null $shift_type
 * @property int|null $shift_rotation_id
 * @property Carbon|null $from_date
 * @property bool $is_forever
 * @property Carbon|null $to_date
 * @property int $status
 * @property string|null $reason
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Shift|null $shift
 * @property ShiftRotation|null $shift_rotation
 * @property Collection|Shift[] $shifts
 * @property Collection|Users[] $users
 * @property Collection|ShiftMuster[] $shift_musters
 */
class ShiftChangeApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'shift_change_applications';

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'shift_type',
        'shifts',
        'shift_rotation_code',
        'from_date',
        'is_forever',
        'to_date',
        'reason',
        'approval_status',
    ];

    protected $casts = [
        'shift_rotation_id' => 'int',
        'from_date' => 'date',
        'is_forever' => 'bool',
        'to_date' => 'date',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'shift_type',
        'shift_rotation_id',
        'from_date',
        'is_forever',
        'status',
        'to_date',
        'reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shift_rotation()
    {
        return $this->belongsTo(ShiftRotation::class);
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_change_application_shifts')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'shift_change_application_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function shift_musters(): MorphMany
    {
        return $this->morphMany(ShiftMuster::class, 'shiftchangeable');
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
