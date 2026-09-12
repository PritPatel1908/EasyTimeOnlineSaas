<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RotationValue
 *
 * @property int $id
 * @property int $shift_rotation_id
 * @property int $shift_id
 * @property int $days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Shift $shift
 * @property ShiftRotation $shift_rotation
 */
class RotationValue extends Model
{
    protected $table = 'rotation_values';

    protected $casts = [
        'shift_rotation_id' => 'int',
        'shift_id' => 'int',
        'days' => 'int',
    ];

    protected $fillable = [
        'shift_rotation_id',
        'shift_id',
        'days',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shift_rotation()
    {
        return $this->belongsTo(ShiftRotation::class);
    }
}
