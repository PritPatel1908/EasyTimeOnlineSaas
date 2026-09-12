<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Enums\WOTypeEnumn;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WeekOffChangeDetail
 *
 * @property int $id
 * @property int $week_off_change_id
 * @property int $week_days
 * @property string|null $week_off_type
 * @property bool $first_week
 * @property bool $second_week
 * @property bool $third_week
 * @property bool $fourth_week
 * @property bool $fifth_week
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property WeekOffChange $week_off_change
 */
class WeekOffChangeDetail extends Model
{
    protected $table = 'week_off_change_details';

    protected $casts = [
        'week_off_change_id' => 'int',
        'week_days' => 'int',
        'first_week' => 'bool',
        'second_week' => 'bool',
        'third_week' => 'bool',
        'fourth_week' => 'bool',
        'fifth_week' => 'bool',
        'wo_type' => WOTypeEnumn::class,
    ];

    protected $fillable = [
        'week_off_change_id',
        'week_days',
        'wo_type',
        'first_week',
        'second_week',
        'third_week',
        'fourth_week',
        'fifth_week',
    ];

    public function week_off_change()
    {
        return $this->belongsTo(WeekOffChange::class);
    }
}
