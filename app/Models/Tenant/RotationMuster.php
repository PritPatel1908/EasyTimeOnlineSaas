<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RotationMuster
 *
 * @property int $id
 * @property int|null $rotation_id
 * @property Carbon $date
 * @property int|null $shift_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ShiftRotation|null $shift_rotation
 * @property Shift|null $shift
 */
class RotationMuster extends Model
{
    protected $table = 'rotation_musters';

    protected $casts = [
        'rotation_id' => 'int',
        'date' => 'datetime',
        'shift_id' => 'int',
    ];

    protected $fillable = [
        'rotation_id',
        'date',
        'shift_id',
    ];

    public function shift_rotation()
    {
        return $this->belongsTo(ShiftRotation::class, 'rotation_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
