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
 * Class ManualPunch
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $punch_type
 * @property Carbon $punch_date
 * @property Carbon $punch_time
 * @property string $reason
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class ManualPunch extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'manual_punches';

    // protected static function booted(): void
    // {
    // 	static::addGlobalScope(new DataPolicyFilter);
    // }

    protected $casts = [
        // 'user_id' => 'int',
        'location_id' => 'array',
        'company_id' => 'array',
        'punch_date' => 'date',
        'punch_time' => 'datetime',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        // 'user_id',
        'location_id',
        'company_id',
        'punch_type',
        'punch_date',
        'punch_time',
        'reason',
        'status',
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
        return $this->belongsToMany(User::class, 'manual_punch_users')
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

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
