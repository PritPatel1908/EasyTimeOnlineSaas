<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LeaveAccountDetail
 *
 * @property int $id
 * @property int|null $leave_balance_id
 * @property int|null $leave_type_id
 * @property float|null $total_balance
 * @property float|null $total_used
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property LeaveAccount|null $leave_account
 * @property LeaveType|null $leave_type
 */
class LeaveAccountDetail extends Model
{
    protected $table = 'leave_account_details';

    protected $casts = [
        'balance' => 'float',
        'leave_account_id' => 'int',
        'leave_type_id' => 'int',
    ];

    protected $fillable = [
        'balance',
        'leave_account_id',
        'leave_type_id',
    ];

    public function leave_account()
    {
        return $this->belongsTo(LeaveAccount::class);
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
