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

    public function ignoreEarlyComingMinutesValue(): int
    {
        return $this->toMinutes($this->ignore_early_come_minutes);
    }

    public function ignoreLateComingMinutesValue(): int
    {
        return $this->toMinutes($this->ignore_late_come_minutes);
    }

    public function adjustedComingMinutes(int $earlyMinutes, int $lateMinutes): array
    {
        return [
            'early' => max(0, $earlyMinutes - $this->ignoreEarlyComingMinutesValue()),
            'late' => max(0, $lateMinutes - $this->ignoreLateComingMinutesValue()),
        ];
    }

    public function qualifyingOvertimeMinutes(float $lateGoingMinutes): float
    {
        return (float) $this->calculateOvertimeMinutes(
            earlyComingMinutes: 0,
            lateComingMinutes: 0,
            lateGoingMinutes: (int) round($lateGoingMinutes),
            earlyGoingMinutes: 0,
        );
    }

    public function calculateOvertimeMinutes(
        int $earlyComingMinutes,
        int $lateComingMinutes,
        int $lateGoingMinutes,
        int $earlyGoingMinutes,
    ): int {
        $comingMinutes = $this->adjustedComingMinutes($earlyComingMinutes, $lateComingMinutes);
        $lateGoingMinutes = $this->ignore_late_going
            ? max(0, $lateGoingMinutes - (int) round((float) $this->ignore_late_going_overtime))
            : $lateGoingMinutes;

        $overtimeMinutes = max(
            0,
            $comingMinutes['early'] + $lateGoingMinutes - $comingMinutes['late'] - $earlyGoingMinutes,
        );

        return max(0, $overtimeMinutes - (int) round((float) $this->ignore_overtime));
    }

    private static function toMinutes(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return max(0, (int) round((float) $value));
        }

        $time = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2}(?:\.\d+)?)?$/', $time, $matches) !== 1) {
            return 0;
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];

        return max(0, ($hours * 60) + $minutes);
    }
}
