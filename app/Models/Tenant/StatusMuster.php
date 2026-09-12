<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StatusMuster
 *
 * @property int $id
 * @property Carbon $date
 * @property int|null $user_id
 * @property int|null $status_master_id
 * @property float $day_count
 * @property int $year
 * @property bool $is_locked
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property StatusMaster|null $status_master
 * @property User|null $user
 */
class StatusMuster extends Model
{
    protected $table = 'status_musters';

    protected $casts = [
        'date' => 'datetime',
        'user_id' => 'int',
        'status_master_id' => 'int',
        'day_count' => 'float',
        'year' => 'int',
        'is_locked' => 'bool',
    ];

    protected $fillable = [
        'date',
        'user_id',
        'status_master_id',
        'day_count',
        'year',
        'is_locked',
    ];

    public function status_master()
    {
        return $this->belongsTo(StatusMaster::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
