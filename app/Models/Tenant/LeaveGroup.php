<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LeaveGroup
 *
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property int $location_id
 * @property int|null $user_id
 * @property int|null $leave_type_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property LeaveType|null $leave_type
 * @property Location $location
 * @property Collection|User[] $users
 */
class LeaveGroup extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'leave_groups';

    protected $casts = [
        'location_id' => 'int',
        'user_id' => 'int',
        'leave_type_id' => 'int',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'location_id',
        'user_id',
        'leave_type_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
