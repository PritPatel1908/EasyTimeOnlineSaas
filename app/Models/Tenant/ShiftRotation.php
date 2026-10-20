<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShiftRotation
 *
 * @property int $id
 * @property string $code
 * @property bool $skip_days_on_month_end
 * @property bool $skip_days_on_year_end
 * @property bool $shift_status
 * @property int $status
 * @property string|null $deleted_at
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property bool $is_forever
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|RotationValue[] $rotation_values
 * @property Collection|User[] $users
 * @property Collection|ShiftChange[] $shift_change
 * @property Collection|RotationMuster[] $rotation_musters
 */
class ShiftRotation extends Model
{
    use CUDby;
    use SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = [
        'code',
        'start_date',
        'end_date',
        'is_forever',
        'skip_days_on_month_end',
        'skip_days_on_year_end',
        'shift_status',
        'status',
        'shift_code',
        'days',
    ];

    protected $table = 'shift_rotations';

    protected $casts = [
        'skip_days_on_month_end' => 'bool',
        'skip_days_on_year_end' => 'bool',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_forever' => 'bool',
        'shift_status' => 'bool',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'skip_days_on_month_end',
        'skip_days_on_year_end',
        'start_date',
        'end_date',
        'is_forever',
        'shift_status',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public static function getImportUniqueFields(): array
    {
        return ['code'];
    }

    public function rotation_values(): HasMany
    {
        return $this->hasMany(RotationValue::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function shift_change(): HasMany
    {
        return $this->hasMany(ShiftChange::class);
    }

    public function rotation_musters(): HasMany
    {
        return $this->hasMany(RotationMuster::class, 'rotation_id');
    }
}
