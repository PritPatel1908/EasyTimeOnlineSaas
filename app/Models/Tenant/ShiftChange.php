<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Enums\StatusEnumn;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class ShiftChange
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
class ShiftChange extends Model
{
    use CUDby;
    use HasFactory, SoftDeletes;

    protected $table = 'shift_changes';

    protected $casts = [
        // 'shift_id' => 'int',
        'shift_rotation_id' => 'int',
        'from_date' => 'datetime',
        'is_forever' => 'bool',
        'to_date' => 'datetime',
        'status' => StatusEnumn::class,
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'shift_type',
        // 'shift_id',
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

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);

        // When a ShiftChange is retrieved, make sure we also apply the data policy filter to the related users
        static::retrieved(function (ShiftChange $shiftChange) {
            if ($shiftChange->relationLoaded('Users')) {
                // Filter the users collection based on data policy
                $shiftChange->setRelation('Users', $shiftChange->Users->filter(function ($user) {
                    // Apply the same logic as in DataPolicyFilter for users
                    return $user->id === Auth::id() || (Auth::user() && Auth::user()->data_policy);
                }));
            }
        });
    }

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
        return $this->belongsToMany(Shift::class, 'shift_change_shifts')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function Users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shift_change_users')
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
}
