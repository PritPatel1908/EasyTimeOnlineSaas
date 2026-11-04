<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveGroup extends Model
{
    use SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = ['name', 'status'];

    protected $table = 'grade_wise_leaves';

    protected $attributes = [
        'is_monthly' => true,
    ];

    protected $casts = [
        'is_monthly' => 'bool',
        'status' => 'int',
    ];

    protected $fillable = [
        'name',
        'is_monthly',
        'status',
    ];

    public static function getImportUniqueFields(): array
    {
        return ['name'];
    }

    public function grade_wise_monthly_leave_details()
    {
        return $this->hasMany(GradeWiseMonthlyLeaveDetail::class, 'grade_wise_leave_id');
    }

    public function grade_wise_yearly_leave_details()
    {
        return $this->hasMany(GradeWiseYearlyLeaveDetail::class, 'grade_wise_leave_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'grade_wise_leave_id');
    }
}
