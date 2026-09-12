<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ManualAttendanceUser extends Model
{
    protected $table = 'manual_attendance_users';

    protected $casts = [
        'manual_attendance_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'manual_attendance_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manual_attendance()
    {
        return $this->belongsTo(ManualAttendance::class);
    }
}
