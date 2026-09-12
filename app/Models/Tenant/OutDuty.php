<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Coff
 *
 * @property int $id
 * @property int|null $user_id
 * @property Carbon $coff_against_date
 * @property bool $only_a_half_day
 * @property Carbon $from_date
 * @property bool $is_second_half
 * @property Carbon $to_date
 * @property bool $is_first_half
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class OutDuty extends Model
{
    use SoftDeletes;

    protected $table = 'out_duties';

    protected $casts = [
        'user_id' => 'int',
        'from_date_time' => 'datetime',
        'to_date_time' => 'datetime',
        'status' => 'int',
        'location_id' => 'int',
        'company_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'from_date_time',
        'to_date_time',
        'status',
        'reason',
        'location_id',
        'company_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
