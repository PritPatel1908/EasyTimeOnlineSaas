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
 * @property int $manual_punch_application_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ManualPunchApplication $manual_punch_application
 * @property User $user
 */
class ManualPunchApplicationUser extends Model
{
    protected $table = 'manual_punch_application_users';

    protected $casts = [
        'manual_punch_application_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'manual_punch_application_id',
        'user_id',
    ];

    public function manual_punch_application()
    {
        return $this->belongsTo(ManualPunchApplication::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
