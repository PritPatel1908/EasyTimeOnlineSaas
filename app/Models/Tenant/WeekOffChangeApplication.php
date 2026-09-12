<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
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
 * @property Collection|Users[] $users
 * @property Collection|WeekOffChangeDetail[] $week_off_change_details
 * @property Collection|WeekOffMuster[] $WeekOffMuster
 * @property ApprovalStatus|null $approval_status
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
}
