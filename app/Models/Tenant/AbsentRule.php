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
 * Class AbsentRule
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property int $work_hr_less_than_minutes
 * @property int $status
 * @property bool $allow_single_punch
 * @property int $late_coming_minutes
 * @property float|null $no_of_late
 * @property float|null $consecutive_late_coming
 * @property bool $ignore_month_end_late
 * @property int $early_going_minutes
 * @property float|null $no_of_early
 * @property float|null $consecutive_early_going
 * @property bool $ignore_month_end_early
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|User[] $users
 */
class AbsentRule extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'absent_rules';

    protected $casts = [
        'work_hr_less_than_minutes' => 'int',
        'allow_single_punch' => 'bool',
        'late_coming_minutes' => 'int',
        'no_of_late' => 'float',
        'consecutive_late_coming' => 'float',
        'ignore_month_end_late' => 'bool',
        'early_going_minutes' => 'int',
        'no_of_early' => 'float',
        'consecutive_early_going' => 'float',
        'ignore_month_end_early' => 'bool',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'work_hr_less_than_minutes',
        'allow_single_punch',
        'late_coming_minutes',
        'no_of_late',
        'consecutive_late_coming',
        'ignore_month_end_late',
        'early_going_minutes',
        'no_of_early',
        'consecutive_early_going',
        'ignore_month_end_early',
        'status',
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
