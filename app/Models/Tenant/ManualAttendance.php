<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualAttendance extends Model
{
    use SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'location',
        'company',
        'in_time',
        'out_time',
        'reason',
    ];

    protected $table = 'manual_attendances';

    protected $casts = [
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'location_id' => 'int',
        'company_id' => 'int',
        'location_ids' => 'array',
        'company_ids' => 'array',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'location_id',
        'company_id',
        'location_ids',
        'company_ids',
        'in_time',
        'out_time',
        'reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'manual_attendance_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
