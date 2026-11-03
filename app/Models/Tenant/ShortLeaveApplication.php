<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShortLeaveApplication
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $reason
 * @property Carbon|null $date
 * @property Carbon|null $from_time
 * @property Carbon|null $to_time
 * @property int|null $minutes
 * @property int|null $short_leave_type
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class ShortLeaveApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'short_leave_applications';

    // protected static function booted(): void
    // {
    // 	static::addGlobalScope(new DataPolicyFilter);
    // }

    protected $casts = [
        'user_id' => 'int',
        'date' => 'date',
        'from_time' => 'datetime',
        'to_time' => 'datetime',
        'minutes' => 'int',
        'short_leave_type' => 'int',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'reason',
        'date',
        'from_time',
        'to_time',
        'minutes',
        'short_leave_type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_code',
        'reason',
        'date',
        'from_time',
        'to_time',
        'minutes',
        'short_leave_type',
        'approval_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }

    public function approval_statuses()
    {
        return $this->morphMany(ApprovalStatus::class, 'approvable');
    }

    public function creditMinutes(): int
    {
        if ((int) $this->short_leave_type === 3 && $this->from_time !== null && $this->to_time !== null) {
            return max(0, (int) Carbon::parse($this->from_time)->diffInMinutes(Carbon::parse($this->to_time)));
        }

        return in_array((int) $this->short_leave_type, [1, 2], true)
            ? max(0, (int) $this->minutes)
            : 0;
    }

    public function scopeApplicableToAttendance(Builder $query): Builder
    {
        return $query
            ->where('status', 1)
            ->whereDoesntHave('approval_statuses', static function (Builder $query): void {
                $query->where(static function (Builder $query): void {
                    $query->whereNull('approval_status')
                        ->orWhere('approval_status', '!=', 'approved');
                });
            });
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
