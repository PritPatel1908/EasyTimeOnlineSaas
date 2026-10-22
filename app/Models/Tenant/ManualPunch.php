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
 * Class ManualPunch
 *
 * @property int $id
 * @property string $punch_type
 * @property Carbon $punch_date
 * @property Carbon $punch_time
 * @property string $reason
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ManualPunch extends Model
{
    use CUDby;
    use SoftDeletes;

    public const PUNCH_TYPES = [
        '1' => 'In Punch',
        '2' => 'Out Punch',
        '3' => 'Break Start',
        '4' => 'Break End',
    ];

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'punch_type',
        'punch_date',
        'punch_time',
        'reason',
    ];

    protected $table = 'manual_punches';

    // protected static function booted(): void
    // {
    // 	static::addGlobalScope(new DataPolicyFilter);
    // }

    protected $casts = [
        'punch_date' => 'date',
        'punch_time' => 'datetime',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'punch_type',
        'punch_date',
        'punch_time',
        'reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static function normalizePunchType(mixed $punchType): string
    {
        $value = strtolower(trim((string) $punchType));

        return match ($value) {
            'in', 'in punch' => '1',
            'out', 'out punch' => '2',
            'break start' => '3',
            'break end' => '4',
            default => (string) $punchType,
        };
    }

    public static function punchTypeLabel(mixed $punchType): string
    {
        $value = (string) $punchType;
        $normalized = self::normalizePunchType($punchType);

        return self::PUNCH_TYPES[$normalized] ?? $value;
    }

    public static function isOutDirection(mixed $punchType): bool
    {
        return in_array(self::normalizePunchType($punchType), ['2', '4'], true);
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'manual_punch_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
