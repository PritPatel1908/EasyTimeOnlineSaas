<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ManualAttendanceApplicationUser
 *
 * @property int $id
 * @property int $manual_attendance_application_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property ManualAttendanceApplication $manual_attendance_application
 */
class ManualAttendanceApplicationUser extends Model
{
    protected $table = 'manual_attendance_application_users';

    protected $casts = [
        'manual_attendance_application_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'manual_attendance_application_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manual_attendance_application()
    {
        return $this->belongsTo(ManualAttendanceApplication::class);
    }
}
