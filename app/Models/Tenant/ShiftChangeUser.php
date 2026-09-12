<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShiftChangeUser
 *
 * @property int $id
 * @property int $shift_change_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ShiftChange $shift_change
 * @property User $user
 */
class ShiftChangeUser extends Model
{
    protected $table = 'shift_change_users';

    protected $casts = [
        'shift_change_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'shift_change_id',
        'user_id',
    ];

    public function shift_change()
    {
        return $this->belongsTo(ShiftChange::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
