<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use App\Models\Tenant\ApprovalStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WeekOffChangeApplication
 *
 * @property int $id
 * @property string|null $reason
 * @property Carbon|null $from_date
 * @property Carbon|null $to_date
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|User[] $users
 * @property Collection|WeekOffChangeApplicationDetail[] $week_off_change_details
 * @property Collection|WeekOffMuster[] $WeekOffMuster
 * @property ApprovalStatus|null $approval_status
 * @property Collection|ApprovalStatus[] $approval_statuses
 */
class WeekOffChangeApplication extends Model
{
    use SoftDeletes;

    protected $table = 'week_off_change_applications';

    protected $casts = [
        'from_date' => 'datetime',
        'is_forever' => 'bool',
        'to_date' => 'datetime',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'reason',
        'from_date',
        'is_forever',
        'to_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'from_date',
        'is_forever',
        'to_date',
        'reason',
        'week_days',
        'wo_types',
        'first_week',
        'second_week',
        'third_week',
        'fourth_week',
        'fifth_week',
        'approval_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'week_off_change_application_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function WeekOffChangeDetails()
    {
        return $this->hasMany(WeekOffChangeApplicationDetail::class);
    }

    public function WeekOffMuster(): MorphMany
    {
        return $this->morphMany(WeekOffMuster::class, 'weekoffable');
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
