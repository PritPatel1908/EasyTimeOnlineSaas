<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Company
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $logo_path
 * @property User|null $user
 * @property LeaveAccountDetail|null $leave_account_details
 */
class LeaveAccount extends Model
{
    protected $table = 'leave_accounts';

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave_account_details()
    {
        return $this->hasMany(LeaveAccountDetail::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'accountable');
    }
}
