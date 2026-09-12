<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WeekOffChangeApplicationUser
 *
 * @property int $id
 * @property int $week_off_change_application_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property WeekOffChangeApplication $week_off_change_application
 */
class WeekOffChangeApplicationUser extends Model
{
    protected $table = 'week_off_change_application_users';

    protected $casts = [
        'week_off_change_application_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'week_off_change_application_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function week_off_change_application()
    {
        return $this->belongsTo(WeekOffChangeApplication::class);
    }
}
