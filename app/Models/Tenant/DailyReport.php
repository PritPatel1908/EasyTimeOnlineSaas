<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Enums\DailyReportTypes;
use App\Enums\DataFormat;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Traits\CUby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DailyReport
 *
 * @property int $id
 * @property string|null $report_title
 * @property int|null $location_id
 * @property string|null $range
 * @property Carbon|null $from_datetime
 * @property Carbon|null $to_datetime
 * @property string $report_type
 * @property string $report_format
 * @property int $user_type
 * @property array|null $selected_columns
 * @property array|null $group_by_columns
 * @property array|null $sort_by_columns
 * @property string $report_status
 * @property string|null $report_path
 * @property bool|null $print_filter_header
 * @property string|null $report_orientation
 * @property string|null $report_size
 * @property int|null $font_size
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Location|null $location
 * @property Collection|Category[] $categories
 * @property Collection|Company[] $companies
 * @property Collection|Department[] $departments
 * @property Collection|SubCategory[] $sub_categories
 * @property Collection|SubDepartment[] $sub_departments
 */
class DailyReport extends Model
{
    use CUby;

    protected $table = 'daily_reports';

    protected $casts = [
        'location_id' => 'int',
        'from_datetime' => 'datetime',
        'to_datetime' => 'datetime',
        'user_type' => 'int',
        'print_filter_header' => 'bool',
        'report_type' => DailyReportTypes::class,
        'report_status' => ReportStatus::class,
        'report_format' => ReportFormat::class,
        'data_format' => DataFormat::class,
        'selected_columns' => 'array',
        'group_by_columns' => 'array',
        'sort_by_columns' => 'array',
        'font_size' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
    ];

    protected $fillable = [
        'report_title',
        'location_id',
        'range',
        'from_datetime',
        'to_datetime',
        'report_type',
        'report_format',
        'data_format',
        'user_type',
        'selected_columns',
        'group_by_columns',
        'sort_by_columns',
        'report_status',
        'report_path',
        'print_filter_header',
        'report_orientation',
        'report_size',
        'font_size',
        'created_by',
        'updated_by',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'daily_report_category')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'daily_report_company')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'daily_report_department')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function sub_categories()
    {
        return $this->belongsToMany(SubCategory::class, 'daily_report_sub_category')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function sub_departments()
    {
        return $this->belongsToMany(SubDepartment::class, 'daily_report_sub_department')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'daily_report_area')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'daily_report_user')
            ->withPivot('id')
            ->withTimestamps();
    }
}
