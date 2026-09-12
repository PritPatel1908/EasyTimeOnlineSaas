<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WeekOffMuster
 *
 * @property int $id
 * @property Carbon|null $date
 * @property int|null $user_id
 * @property string|null $week_days
 * @property string|null $week_off_type
 * @property bool $first_week
 * @property bool $second_week
 * @property bool $third_week
 * @property bool $fourth_week
 * @property bool $fifth_week
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $weekoffable_type
 * @property int|null $weekoffable_id
 * @property User|null $user
 * @property WeekOffChange|null $week_off_change
 */
class WeekOffMuster extends Model
{
    protected $table = 'week_off_musters';

    protected $casts = [
        'date' => 'datetime',
        'user_id' => 'int',
        'weekoffable_id' => 'int',
        'is_locked' => 'bool',
    ];

    protected $fillable = [
        'date',
        'user_id',
        'is_locked',
        'wo_type',
        'weekoffable_type',
        'weekoffable_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function WeekOffChange()
    {
        return $this->belongsTo(WeekOffChange::class);
    }

    public function weekoffable()
    {
        return $this->morphTo();
    }
}
