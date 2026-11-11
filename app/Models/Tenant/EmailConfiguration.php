<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class EmailConfiguration
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class EmailConfiguration extends Model
{
    use SoftDeletes;

    // use CUDby;
    protected $table = 'email_configurations';

    protected $casts = [
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * @return array<string, string|null>
     */
    public static function settings(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }

    /**
     * @param array<string, string|null> $settings
     */
    public static function saveSettings(array $settings): void
    {
        DB::transaction(static function () use ($settings): void {
            foreach ($settings as $key => $value) {
                static::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $value],
                );
            }
        });
    }

    public static function deleteSettings(): void
    {
        static::query()->forceDelete();
    }
}
