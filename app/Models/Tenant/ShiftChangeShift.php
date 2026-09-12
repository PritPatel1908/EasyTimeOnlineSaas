<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShiftChangeShift
 *
 * @property int $id
 * @property int $shift_change_id
 * @property int $shift_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ShiftChange $shift_change
 * @property ShiftMusters $shift_musters
 * @property Shift $shift
 */
class ShiftChangeShift extends Model
{
    protected $table = 'shift_change_shifts';

    protected $casts = [
        'shift_change_id' => 'int',
        'shift_id' => 'int',
    ];

    protected $fillable = [
        'shift_change_id',
        'shift_id',
    ];

    public function shift_change()
    {
        return $this->belongsTo(ShiftChange::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shift_musters()
    {
        return $this->hasMany(ShiftMuster::class);
    }
}
