<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShiftChangeApplicationUser
 *
 * @property int $id
 * @property int $shift_change_application_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ShiftChangeApplication $shift_change
 * @property User $user
 */
class ShiftChangeApplicationUser extends Model
{
    protected $table = 'shift_change_application_users';

    protected $casts = [
        'shift_change_application_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'shift_change_application_id',
        'user_id',
    ];

    public function shift_change()
    {
        return $this->belongsTo(ShiftChangeApplication::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
