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
 * Class LeaveType
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property int|null $location_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|LeaveApplication[] $leave_applications
 * @property Location|null $location
 * @property Collection|LeaveGroup[] $leave_groups
 */
class LeaveType extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'leave_types';

    protected $casts = [
        'location_id' => 'int',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'location_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function leave_applications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leave_groups()
    {
        return $this->hasMany(LeaveGroup::class);
    }
}
