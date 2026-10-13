<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegacyLeaveGroup extends Model
{
    use SoftDeletes;

    protected $table = 'leave_groups';

    protected $casts = [
        'location_id' => 'int',
        'user_id' => 'int',
        'leave_type_id' => 'int',
        'status' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'location_id',
        'user_id',
        'leave_type_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'leave_group_id');
    }
}
