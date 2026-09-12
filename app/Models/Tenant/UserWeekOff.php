<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserWeekOff
 *
 * @property int $id
 * @property int $user_id
 * @property int $week_days
 * @property bool $is_half_day
 * @property bool $first_week
 * @property bool $second_week
 * @property bool $third_week
 * @property bool $fourth_week
 * @property bool $fifth_week
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 */
class UserWeekOff extends Model
{
    protected $table = 'user_week_offs';

    protected $casts = [
        'user_id' => 'int',
        'week_days' => 'int',
        'is_half_day' => 'bool',
        'first_week' => 'bool',
        'second_week' => 'bool',
        'third_week' => 'bool',
        'fourth_week' => 'bool',
        'fifth_week' => 'bool',
    ];

    protected $fillable = [
        'user_id',
        'week_days',
        'wo_type',
        'first_week',
        'second_week',
        'third_week',
        'fourth_week',
        'fifth_week',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
