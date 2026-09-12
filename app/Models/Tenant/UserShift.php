<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserShift
 *
 * @property int $id
 * @property int $user_id
 * @property int $shift_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Shift $shift
 * @property User $user
 */
class UserShift extends Model
{
    protected $table = 'user_shifts';

    protected $casts = [
        'user_id' => 'int',
        'shift_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'shift_id',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
