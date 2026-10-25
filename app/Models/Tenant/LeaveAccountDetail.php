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
 * @property int|null $leave_account_id
 * @property int|null $leave_type_id
 * @property string|null $type
 * @property int|null $type_id
 * @property string|null $transaction_year
 * @property int|null $transaction_id
 * @property float $balance
 * @property string $credit_balance
 * @property string $debit_balance
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
        'type_id' => 'int',
        'transaction_id' => 'int',
        'credit_balance' => 'decimal:2',
        'debit_balance' => 'decimal:2',
    ];

    protected $fillable = [
        'balance',
        'leave_account_id',
        'leave_type_id',
        'type',
        'type_id',
        'transaction_year',
        'transaction_id',
        'credit_balance',
        'debit_balance',
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
