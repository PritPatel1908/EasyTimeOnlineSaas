<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LeaveCheckMuster
 *
 * @property int $id
 * @property int $user_id
 * @property bool|null $is_checked
 * @property Carbon|null $check_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class LeaveCheckMuster extends Model
{
    protected $table = 'leave_check_musters';

    protected $casts = [
        'user_id' => 'int',
        'check_date' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'check_date',
        'is_checked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
