<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveGroup extends Model
{
    use SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = ['name', 'financial_year_id', 'is_monthly', 'status'];

    protected $table = 'grade_wise_leaves';

    protected $casts = [
        'financial_year_id' => 'int',
        'is_monthly' => 'bool',
        'status' => 'int',
    ];

    protected $fillable = [
        'name',
        'financial_year_id',
        'is_monthly',
        'status',
    ];

    public static function getImportUniqueFields(): array
    {
        return ['name', 'financial_year_id'];
    }

    public function financial_year()
    {
        return $this->belongsTo(FinancialYear::class);
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
