<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class WeekOffSwap
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
class WeekOffSwap extends Model
{
    use SoftDeletes;

    protected $table = 'week_off_swaps';

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'week_date',
        'swap_date',
        'reason',
    ];

    protected $casts = [
        'week_date' => 'datetime',
        'swap_date' => 'datetime',
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

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);

        static::retrieved(function (WeekOffSwap $weekOffSwap): void {
            if ($weekOffSwap->relationLoaded('Users')) {
                $weekOffSwap->setRelation('Users', $weekOffSwap->Users->filter(
                    static fn (User $user): bool => $user->id === Auth::id() || (Auth::user() && Auth::user()->data_policy)
                ));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'week_off_swap_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }

    public function WeekOffMuster()
    {
        return $this->belongsToMany(WeekOffMuster::class);
    }
}
