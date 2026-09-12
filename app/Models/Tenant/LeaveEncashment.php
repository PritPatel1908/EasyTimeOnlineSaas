<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class LeaveEncashment extends Model
{
    protected $table = 'leave_encashments';

    protected $casts = [
        'date' => 'datetime',
        'user_id' => 'int',
        'leave_type_id' => 'int',
        'lapse_count' => 'decimal:2',
        'is_encashment' => 'bool',
    ];

    protected $fillable = [
        'date',
        'user_id',
        'leave_type_id',
        'lapse_count',
        'is_encashment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
