<?php

namespace App\Models\Tenant;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;

// genrate attendane doc
/**
 * Class Attendance
 *
 * @property int $id
 * @property int $user_id
 * @property int $location_id
 * @property int $company_id
 * @property int $department_id
 * @property int $sub_department_id
 * @property string $date
 * @property string $shift
 * @property string $shift_in_time
 * @property string $shift_out_time
 * @property bool $is_night_shift
 * @property string $in_time
 * @property int|null $in_log_id
 * @property string $out_time
 * @property int|null $out_log_id
 * @property bool $is_locked
 * @property bool $is_holiday
 * @property bool $is_weekoff
 * @property bool $is_manual
 * @property bool $has_error
 * @property string|null $remarks
 * @property string|null $deleted_at
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property AttendanceLog|null $inLog
 * @property AttendanceLog|null $outLog
 * @property User $user
 */
class Attendance extends Model
{
    use HasDynamicTable;

    protected $table = '';

    // public function __construct(array $attributes = [], ?string $year = null)
    public function __construct(?string $year = null)
    {
        $this->syncOriginal();
        // $this->fill($attributes);
        $this->setDynamicTable($year);
    }

    protected function baseTableName()
    {
        return 'attendances';
    }

    protected function defineSchema(Blueprint $table, $year)
    {
        $table->id();
        $table->foreignId('user_id')->constrained();
        $table->date('date');
        $table->foreignId('location_id')->nullable()->constrained();
        $table->foreignId('company_id')->nullable()->constrained();
        $table->json('department_id')->nullable();
        $table->json('sub_department_id')->nullable();
        $table->json('category_id')->nullable();
        $table->json('sub_category_id')->nullable();
        $table->foreignId('shift_id')->nullable()->constrained();
        $table->foreignId('area_id')->nullable()->constrained();
        $table->string('shift_code')->nullable();
        $table->datetime('shift_in_time', $precision = 0)->nullable();
        $table->datetime('shift_out_time', $precision = 0)->nullable();
        $table->boolean('is_night_shift')->default(false);
        $table->datetime('in_time', $precision = 0)->nullable();
        $table->foreignId('in_log_id')->nullable()->constrained('attendance_logs')->noActionOnDelete();
        $table->datetime('out_time', $precision = 0)->nullable();
        $table->foreignId('out_log_id')->nullable()->constrained('attendance_logs')->noActionOnDelete();
        $table->boolean('is_locked')->default(false);
        $table->boolean('is_holiday')->default(false);
        $table->boolean('is_weekoff')->default(false);
        $table->boolean('is_late')->default(false);
        $table->boolean('is_early')->default(false);
        $table->boolean('is_half_day')->default(false);
        $table->boolean('is_absent')->default(false);
        $table->boolean('is_over_time')->default(false);
        $table->boolean('is_manual')->default(false);
        $table->boolean('has_error')->default(false);
        $table->foreignId('status_master_id')->nullable()->constrained('status_masters');
        $table->string('remarks')->nullable();
        $table->timestamps();
    }

    protected $fillable = [
        'user_id',
        'location_id',
        'company_id',
        'department_id',
        'sub_department_id',
        'category_id',
        'sub_category_id',
        'date',
        'shift_id',
        'shift_code',
        'shift_in_time',
        'shift_out_time',
        'is_night_shift',
        'in_time',
        'in_log_id',
        'out_time',
        'out_log_id',
        'is_locked',
        'is_holiday',
        'is_weekoff',
        'has_error',
        'remarks',
        'is_late',
        'is_early',
        'is_over_time',
        'is_absent',
        'is_half_day',
        'is_manual',
        'status_master_id',
    ];

    protected $casts = [
        'user_id' => 'int',
        'location_id' => 'int',
        'company_id' => 'int',
        'department_id' => 'array',
        'sub_department_id' => 'array',
        'category_id' => 'array',
        'sub_category_id' => 'array',
        'in_log_id' => 'int',
        'out_log_id' => 'int',
        'date' => 'date',
        'shift_id' => 'int',
        'shift_in_time' => 'datetime',
        'shift_out_time' => 'datetime',
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'is_night_shift' => 'boolean',
        'is_locked' => 'boolean',
        'is_holiday' => 'boolean',
        'is_weekoff' => 'boolean',
        'has_error' => 'boolean',
        'is_late' => 'boolean',
        'is_early' => 'boolean',
        'is_over_time' => 'boolean',
        'is_absent' => 'boolean',
        'is_half_day' => 'boolean',
        'is_manual' => 'boolean',
        'status_master_id' => 'int',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function inLog()
    {
        return $this->belongsTo(AttendanceLog::class, 'in_log_id');
    }

    public function outLog()
    {
        return $this->belongsTo(AttendanceLog::class, 'out_log_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sub_department()
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public static function year($year)
    {
        return new static($year);
    }

    public function process_tags(): MorphMany
    {
        return $this->morphMany(ProcessTag::class, 'taggable');
    }

    public function status_master()
    {
        return $this->belongsTo(StatusMaster::class);
    }
}
