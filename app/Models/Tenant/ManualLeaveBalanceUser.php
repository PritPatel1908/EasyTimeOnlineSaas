<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ManualLeaveBalanceUser extends Model
{
    protected $table = 'manual_leave_balance_users';

    protected $casts = [
        'manual_leave_balance_id' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'manual_leave_balance_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manual_leave_balance()
    {
        return $this->belongsTo(ManualLeaveBalance::class);
    }
}
