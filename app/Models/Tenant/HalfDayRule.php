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
 * Class HalfDayRule
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property bool $single_punch_allowed
 * @property int $status
 * @property int $late_coming_minutes
 * @property int $work_hr_less_than_minutes
 * @property float|null $no_of_late
 * @property int|null $consecutive_late_coming
 * @property bool $ignore_month_end_late
 * @property int $early_going_minutes
 * @property float|null $no_of_early
 * @property float|null $consecutive_early_going
 * @property float|null $ignore_month_end_early
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|User[] $users
 */
class HalfDayRule extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'half_day_rules';

    protected $casts = [
        'single_punch_allowed' => 'bool',
        'late_coming_minutes' => 'int',
        'work_hr_less_than_minutes' => 'int',
        'no_of_late' => 'float',
        'consecutive_late_coming' => 'int',
        'status' => 'int',
        'ignore_month_end_late' => 'bool',
        'early_going_minutes' => 'int',
        'no_of_early' => 'float',
        'consecutive_early_going' => 'float',
        'ignore_month_end_early' => 'float',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'single_punch_allowed',
        'late_coming_minutes',
        'work_hr_less_than_minutes',
        'status',
        'no_of_late',
        'consecutive_late_coming',
        'ignore_month_end_late',
        'early_going_minutes',
        'no_of_early',
        'consecutive_early_going',
        'ignore_month_end_early',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
