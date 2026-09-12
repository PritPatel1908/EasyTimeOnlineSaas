<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class ShiftChangeApplicationShift
 *
 * @property int $id
 * @property int $shift_change_application_id
 * @property int $shift_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ShiftChangeApplication $shift_change
 * @property ShiftMusters $shift_musters
 * @property Shift $shift
 */
class ShiftChangeApplicationShift extends Model
{
    protected $table = 'shift_change_application_shifts';

    protected $casts = [
        'shift_change_application_id' => 'int',
        'shift_id' => 'int',
    ];

    protected $fillable = [
        'shift_change_application_id',
        'shift_id',
    ];

    public function shift_change()
    {
        return $this->belongsTo(ShiftChangeApplication::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shift_musters(): MorphMany
    {
        return $this->morphMany(ShiftMuster::class, 'shiftchangeable');
    }
}
