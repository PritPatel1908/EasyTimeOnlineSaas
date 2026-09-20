<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class c_off_against_ot_slabs
 *
 * @property int $id
 * @property int|null $category_id
 * @property float|null $credit_days
 * @property float|null $from_hours
 * @property float|null $to_hours
 * @property Category|null $category
 */
class CoffAgainstOtSlab extends Model
{
    protected array $auditLogOldSnapshot = [];

    protected $table = 'c_off_against_ot_slabs';

    protected static function booted(): void
    {
        static::created(function (self $slab): void {
            $slab->audit('created', [], $slab->getAttributes());
        });
        static::updating(function (self $slab): void {
            $slab->auditLogOldSnapshot = $slab->getOriginal();
        });
        static::updated(function (self $slab): void {
            $slab->audit('updated', $slab->auditLogOldSnapshot, $slab->getAttributes());
        });
    }

    protected $casts = [
        'category_id' => 'int',
        'credit_days' => 'decimal:2',
        'from_hours' => 'decimal:2',
        'to_hours' => 'decimal:2',
    ];

    protected $fillable = [
        'category_id',
        'credit_days',
        'from_hours',
        'to_hours',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    private function audit(string $event, array $old, array $new): void
    {
        ActivityLogger::log(Auth::guard('tenant')->user(), $this, $old, $new, $event);
    }
}
