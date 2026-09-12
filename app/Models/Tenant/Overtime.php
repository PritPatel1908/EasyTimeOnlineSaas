<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Overtime
 *
 * @property int $id
 * @property string $employee
 * @property Carbon $date
 * @property float $overtime_durations
 * @property int $overtime_reason_id
 * @property int $status
 * @property string|null $reason_explanation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property OvertimeReason $overtime_reason
 */
class Overtime extends Model
{
    // use CUDby;
    protected $table = 'overtimes';

    protected $casts = [
        'date' => 'datetime',
        'overtime_durations' => 'float',
        'overtime_reason_id' => 'int',
        'created_by' => 'int',
        'status' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'employee',
        'date',
        'overtime_durations',
        'overtime_reason_id',
        'reason_explanation',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function overtime_reason()
    {
        return $this->belongsTo(OvertimeReason::class);
    }
}
