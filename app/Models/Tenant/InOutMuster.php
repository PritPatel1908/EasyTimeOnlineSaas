<?php

namespace App\Models\Tenant;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class InOutMuster extends Model
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
        return 'in_out_musters';
    }

    protected function defineSchema(Blueprint $table, $year)
    {
        $table->id();
        $table->foreignId('user_id')->constrained();
        $table->date('date');
        $table->foreignID('shift_id')->nullable()->constrained()->noActionOnDelete();
        $table->foreignId('location_id')->nullable()->constrained();
        $table->foreignId('company_id')->nullable()->constrained();
        $table->json('department_id')->nullable();
        $table->json('sub_department_id')->nullable();
        $table->json('category_id')->nullable();
        $table->json('sub_category_id')->nullable();
        $table->foreignId('area_id')->nullable()->constrained();
        $table->datetime('check_time', $precision = 0)->nullable();
        $table->datetime('in_time', $precision = 0)->nullable();
        $table->foreignId('in_log_id')->nullable()->constrained('attendance_logs')->noActionOnDelete();
        $table->datetime('out_time', $precision = 0)->nullable();
        $table->foreignId('out_log_id')->nullable()->constrained('attendance_logs')->noActionOnDelete();
        $table->decimal('work_time')->nullable();
        $table->boolean('is_locked')->default(false);
        $table->boolean('has_error')->default(false);
        $table->string('remarks')->nullable();
        $table->timestamps();
    }

    protected $fillable = [
        'user_id',
        'shift_id',
        'location_id',
        'company_id',
        'department_id',
        'sub_department_id',
        'area_id',
        'date',
        'check_time',
        'in_time',
        'in_log_id',
        'out_time',
        'out_log_id',
        'work_time',
        'is_locked',
        'has_error',
        'remarks',
    ];

    protected $casts = [
        'user_id' => 'int',
        'shift_id' => 'int', // 'shift_id' => 'array
        'location_id' => 'int',
        'company_id' => 'int',
        'department_id' => 'array',
        'sub_department_id' => 'array',
        'category_id' => 'array',
        'sub_category_id' => 'array',
        'area_id' => 'int',
        'date' => 'date',
        'check_time' => 'datetime',
        'in_time' => 'datetime',
        'in_log_id' => 'int',
        'out_time' => 'datetime',
        'out_log_id' => 'int',
        'work_time' => 'int',
        'is_locked' => 'bool',
        'has_error' => 'bool',
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

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public static function year($year)
    {
        return new static($year);
    }
}
