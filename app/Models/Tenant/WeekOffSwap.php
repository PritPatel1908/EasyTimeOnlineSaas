<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Enums\WOTypeEnumn;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WeekOffSwap
 *
 * @property int $id
 * @property string|null $reason
 * @property Carbon|null $week_date
 * @property Carbon|null $swap_date
 * @property int|null $location_id
 * @property int|null $company_id
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

    protected $casts = [
        'week_date' => 'datetime',
        'swap_date' => 'datetime',
        'location_id' => 'int',
        'company_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
        'wo_type' => WOTypeEnumn::class,
    ];

    protected $fillable = [
        'reason',
        'week_date',
        'wo_type',
        'swap_date',
        'location_id',
        'company_id',
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
        return $this->belongsToMany(User::class, 'week_off_swap_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function WeekOffMuster()
    {
        return $this->belongsToMany(WeekOffMuster::class);
    }
}
