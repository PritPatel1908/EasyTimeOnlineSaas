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
 * Class ShortLeaveApplication
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $reason
 * @property int $status
 * @property Carbon|null $from_date_time
 * @property Carbon|null $to_date_time
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
        'date' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
