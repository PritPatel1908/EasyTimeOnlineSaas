<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class AttendanceLog
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_code
 * @property string|null $dms_user_id
 * @property Carbon|null $datetime
 * @property string|null $punch_type
 * @property string|null $verify_type
 * @property string|null $area_name
 * @property string|null $device_id
 * @property string|null $device_sn
 * @property bool $is_out
 * @property bool $debug_me
 * @property bool $is_calculated
 * @property bool $is_staged
 * @property bool $is_manual
 * @property bool $is_sanctioned
 * @property bool $is_ignored
 * @property bool $has_error
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class AttendanceLog extends Model
{
    use CUDby;
    use HasFactory, SoftDeletes;

    protected $table = 'attendance_logs';

    protected $casts = [
        'user_id' => 'int',
        'dms_log_id' => 'int',
        'dms_user_id' => 'int',
        'dms_area_id' => 'int',
        'datetime' => 'datetime',
        'punch_type' => 'int',
        'verify_type' => 'int',
        'is_out' => 'bool',
        'debug_me' => 'bool',
        'is_calculated' => 'bool',
        'is_staged' => 'bool',
        'is_manual' => 'bool',
        'is_sanctioned' => 'bool',
        'is_ignored' => 'bool',
        'has_error' => 'bool',
        'is_locked' => 'bool',
    ];

    protected $fillable = [
        'user_id',
        'user_code',
        'dms_log_id',
        'dms_user_id',
        'dms_area_id',
        'dms_device_id',
        'datetime',
        'punch_type',
        'verify_type',
        'area_name',
        'is_out',
        'debug_me',
        'is_calculated',
        'is_staged',
        'is_manual',
        'is_sanctioned',
        'is_ignored',
        'has_error',
        'is_locked',
    ];

    // public function isVisitor(): bool
    // {
    //     return Str::startsWith($this->user_code, 'VST') && strlen($this->user_code) == 16;
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_code', 'code');
    }

    // public function visitor()
    // {
    //     return $this->belongsTo(Visitor::class, "user_code", "visitor_code");
    // }

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'dms_device_id', 'dms_device_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'dms_area_id', 'dms_area_id');
    }

    public function process_tags(): MorphMany
    {
        return $this->morphMany(ProcessTag::class, 'taggable');
    }
}
