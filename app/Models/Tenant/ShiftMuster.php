<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShiftMuster
 *
 * @property int $id
 * @property Carbon|null $date
 * @property int|null $user_id
 * @property bool $is_auto
 * @property int|null $shift_change_id
 * @property json|null $shift
 * @property int|null $calculated_shift
 * @property bool $is_calculated
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ShiftChange|null $shift_change
 * @property User|null $user
 */
class ShiftMuster extends Model
{
    protected $table = 'shift_musters';

    protected $casts = [
        'date' => 'datetime',
        'user_id' => 'int',
        'is_auto' => 'bool',
        // 'shift_change_id' => 'int',
        'shiftchangeable_id' => 'int',
        'shift' => 'json',
        'calculated_shift' => 'int',
        'is_calculated' => 'bool',
        'is_locked' => 'bool',
    ];

    protected $fillable = [
        'date',
        'user_id',
        'is_auto',
        // 'shift_change_id',
        'shift',
        'calculated_shift',
        'is_calculated',
        'shiftchangeable_type',
        'shiftchangeable_id',
        'is_locked',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift');
    }

    public function shift_change()
    {
        return $this->belongsTo(ShiftChange::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftchangeable()
    {
        return $this->morphTo();
    }
}
