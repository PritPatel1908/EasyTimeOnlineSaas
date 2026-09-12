<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 *
 * @property int $id
 * @property string $key
 * @property string $value
 */
class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function isWeekOffChangeApplicationRequiresApproval(): bool
    {
        $config = static::where('key', 'week_off_change_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    public static function isWeekOffSwapApplicationRequiresApproval(): bool
    {
        $config = static::where('key', 'week_off_swap_application_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isShiftChangeApplicationRequiresApproval(): bool
    {
        $config = static::where('key', 'shift_change_application_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isManualPunchRequiresApproval(): bool
    {
        $config = static::where('key', 'manual_punch_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isManualAttendanceRequiresApproval(): bool
    {
        $config = static::where('key', 'manual_attendance_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isLeaveApplicationRequiresApproval(): bool
    {
        $config = static::where('key', 'leave_application_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isShortLeaveRequiresApproval(): bool
    {
        $config = static::where('key', 'short_leave_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isCoffRequiresApproval(): bool
    {
        $config = static::where('key', 'coff_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    // TODO:all requests one setting / seprate all setting
    public static function isOdRequiresApproval(): bool
    {
        $config = static::where('key', 'od_requires_approval')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }
}
