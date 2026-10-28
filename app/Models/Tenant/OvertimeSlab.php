<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Class OvertimeSlab
 *
 * @property int $id
 * @property string|null $value_from
 * @property string|null $value_to
 * @property string|null $head_value
 * @property float|null $decimal_value
 * @property int $overtime_rule_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property OvertimeRule $overtime_rule
 */
class OvertimeSlab extends Model
{
    protected $table = 'overtime_slabs';

    protected $casts = [
        'decimal_value' => 'float',
        'overtime_rule_id' => 'int',
    ];

    protected $fillable = [
        'value_from',
        'value_to',
        'head_value',
        'decimal_value',
        'overtime_rule_id',
    ];

    public function overtime_rule()
    {
        return $this->belongsTo(OvertimeRule::class);
    }

    public function containsMinutes(int $minutes): bool
    {
        $from = $this->timeValueInMinutes($this->value_from);
        $to = $this->timeValueInMinutes($this->value_to);

        return $from !== null && $to !== null && $minutes >= $from && $minutes <= $to;
    }

    public function formattedHeadValue(): string
    {
        $minutes = $this->timeValueInMinutes($this->head_value);

        return $minutes === null
            ? '00:00'
            : sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    private function timeValueInMinutes(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return ((int) $value->format('H') * 60) + (int) $value->format('i');
        }

        if (preg_match('/\A(\d{1,3}):([0-5]\d)(?::[0-5]\d)?\z/', (string) $value, $matches) !== 1) {
            throw new InvalidArgumentException('Overtime slab values must be valid HH:MM[:SS] times.');
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }
}
