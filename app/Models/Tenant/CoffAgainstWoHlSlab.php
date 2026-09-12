<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Class c_off_against_wo_hl_slabs
 *
 * @property int $id
 * @property int $credit_days
 * @property int|null $category_id
 * @property Category|null $category
 */
class CoffAgainstWoHlSlab extends Model
{
    protected $table = 'c_off_against_wo_hl_slabs';

    protected $casts = [
        'category_id' => 'int',
        'credit_days' => 'decimal:2',
        'from_time' => 'datetime',
        'to_time' => 'datetime',
    ];

    protected $fillable = [
        'category_id',
        'credit_days',
        'from_time',
        'to_time',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
