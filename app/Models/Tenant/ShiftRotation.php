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
 * Class ShiftRotation
 *
 * @property int $id
 * @property string $code
 * @property bool $skip_days_on_month_end
 * @property bool $skip_days_on_year_end
 * @property bool $shift_status
 * @property int $status
 * @property string|null $deleted_at
 * @property Carbon|null $rejoin_date
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|RotationValue[] $rotation_values
 * @property Collection|User[] $users
 * @property Collection|ShiftChange[] $shift_change
 * @property Collection|RotationMuster[] $rotation_musters
 */
class ShiftRotation extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'shift_rotations';

    protected $casts = [
        'skip_days_on_month_end' => 'bool',
        'skip_days_on_year_end' => 'bool',
        'start_date' => 'datetime',
        'shift_status' => 'bool',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'skip_days_on_month_end',
        'skip_days_on_year_end',
        'start_date',
        'shift_status',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function rotation_values()
    {
        return $this->hasMany(RotationValue::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function shift_change()
    {
        return $this->hasMany(ShiftChange::class);
    }

    public function rotation_musters()
    {
        return $this->hasMany(RotationMuster::class);
    }
}
