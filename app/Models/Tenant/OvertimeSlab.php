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
 * @property string|null $value_from
 * @property string|null $value_to
 * @property string|null $head_value
 * @property float|null $decimal_value
 * @property int $overtime_rule_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property OvertimeRule $overtime_rule
 */
class OvertimeSlab extends Model
{
    protected $table = 'overtime_slabs';

    protected $casts = [
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
