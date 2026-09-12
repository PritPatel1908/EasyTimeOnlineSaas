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
 * Class Shift
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $location_id
 * @property int $company_id
 * @property int $status
 * @property Carbon|null $in_time
 * @property Carbon|null $out_time
 * @property Carbon|null $brack_start_time
 * @property Carbon|null $brack_end_time
 * @property Carbon|null $auto_from
 * @property Carbon|null $auto_to
 * @property bool $is_night_shift
 * @property bool $set_cutoff
 * @property int $cutoff_time
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Company $company
 * @property User|null $user
 * @property Location $location
 * @property Collection|RotationValue[] $rotation_values
 * @property Collection|RotationMuster[] $rotation_musters
 * @property Collection|Holiday[] $holidays
 * @property Collection|User[] $users
 * @property Collection|ShiftChange[] $shift_changes
 * @property Collection|ShiftChangeShift[] $shift_change_shifts
 * @property Collection|ShiftMuster[] $shift_musters
 */
class Shift extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'shifts';

    protected $casts = [
        'location_id' => 'array',
        'company_id' => 'array',
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'first_half_end_time' => 'datetime',
        'second_half_start_time' => 'datetime',
        'brack_start_time' => 'datetime',
        'brack_end_time' => 'datetime',
        'auto_from' => 'datetime',
        'auto_to' => 'datetime',
        'is_night_shift' => 'bool',
        'set_cutoff' => 'bool',
        'cutoff_time' => 'datetime',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'location_id',
        'company_id',
        'in_time',
        'out_time',
        'first_half_end_time',
        'second_half_start_time',
        'brack_start_time',
        'brack_end_time',
        'auto_from',
        'auto_to',
        'is_night_shift',
        'set_cutoff',
        'cutoff_time',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function rotation_values()
    {
        return $this->hasMany(RotationValue::class);
    }

    public function rotation_musters()
    {
        return $this->hasMany(RotationMuster::class);
    }

    public function holidays()
    {
        return $this->belongsToMany(Holiday::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function users()
    {
        // return $this->hasMany(User::class);
        return $this->belongsToMany(User::class, 'user_shifts')
            ->withPivot('id')
            ->withTimestamps();
    }

    // TODO:map relation with user table has many throw
    // public function rotational_shift_users()
    // {
    // 	// return $this->hasMany(User::class);
    //     return $this->belongsToMany(User::class, 'user_shifts')
    // 				->withPivot('id')
    // 				->withTimestamps();
    // }

    // public function shift_changes()
    // {
    // 	return $this->hasMany(ShiftChange::class);
    // }

    public function shift_change_shifts()
    {
        return $this->hasMany(ShiftChangeShift::class);
    }

    public function shift_musters()
    {
        return $this->hasMany(ShiftMuster::class);
    }
}
