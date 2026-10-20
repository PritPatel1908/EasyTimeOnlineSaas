<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OvertimeRule
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property string|null $ignore_early_come_minutes
 * @property string|null $ignore_late_come_minutes
 * @property bool $ignore_late_going
 * @property float $ignore_overtime
 * @property float $ignore_late_going_overtime
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|User[] $users
 * @property Collection|OvertimeSlab[] $overtime_slabs
 */
class OvertimeRule extends Model
{
    use CUDby;
    use SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = [
        'code',
        'description',
        'ignore_early_come_minutes',
        'ignore_late_come_minutes',
        'ignore_late_going',
        'ignore_overtime',
        'ignore_late_going_overtime',
        'status',
        'slab_value_from',
        'slab_value_to',
        'slab_head_value',
        'slab_decimal_value',
    ];

    protected $table = 'overtime_rules';

    protected $casts = [
        'ignore_late_going' => 'bool',
        'ignore_overtime' => 'decimal:2',
        'ignore_late_going_overtime' => 'decimal:2',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'ignore_early_come_minutes',
        'ignore_late_come_minutes',
        'ignore_late_going',
        'ignore_overtime',
        'ignore_late_going_overtime',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static function getImportUniqueFields(): array
    {
        return ['code'];
    }

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function overtime_slabs()
    {
        return $this->hasMany(OvertimeSlab::class);
    }

    public function qualifyingOvertimeMinutes(float $lateGoingMinutes): float
    {
        $minimumMinutes = $this->ignore_late_going
            ? (float) $this->ignore_late_going_overtime
            : (float) $this->ignore_overtime;

        return $lateGoingMinutes > $minimumMinutes ? $lateGoingMinutes : 0.0;
    }
}
