<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OvertimeSlab
 *
 * @property int $id
 * @property Carbon|null $value_from
 * @property Carbon|null $value_to
 * @property Carbon|null $head_value
 * @property float|null $decimal_value
 * @property int $overtime_rule_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property OvertimeRules $overtime_rule
 */
class OvertimeSlab extends Model
{
    protected $table = 'overtime_slabs';

    protected $casts = [
        'value_from' => 'datetime',
        'value_to' => 'datetime',
        'head_value' => 'datetime',
        'decimal_value' => 'float',
        'overtime_rule_id' => 'int',
    ];

    protected $fillable = [
        'value_from',
        'value_to',
        'head_value',
        'decimal_value',
        'overtime_rule_id',
    ];

    public function overtime_rule()
    {
        return $this->belongsTo(OvertimeRule::class);
    }
}
