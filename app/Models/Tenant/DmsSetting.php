<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DmsSetting
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class DmsSetting extends Model
{
    use CUDby, SoftDeletes;

    protected $table = 'dms_settings';

    protected $casts = [
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    public static function isDmsAreaSyncRequires(): bool
    {
        $config = static::where('key', 'sync_areas')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    public static function isDmsMachineSyncRequires(): bool
    {
        $config = static::where('key', 'sync_machines')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }

    public static function isDmsEmployeeSyncRequires(): bool
    {
        $config = static::where('key', 'sync_employees')->first();
        if ($config->value == '1') {
            return true;
        }

        return false;
    }
}
