<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CanteenFacilityRule extends Model
{
    protected array $auditLogOldSnapshot = [];

    protected $table = 'canteen_facility_rules';

    protected static function booted(): void
    {
        static::created(function (self $rule): void {
            $rule->audit('created', [], $rule->getAttributes());
        });
        static::updating(function (self $rule): void {
            $rule->auditLogOldSnapshot = $rule->getOriginal();
        });
        static::updated(function (self $rule): void {
            $rule->audit('updated', $rule->auditLogOldSnapshot, $rule->getAttributes());
        });
    }

    protected $fillable = [
        'canteen_facility_id',
        'total_absent_days',
        'company_contribution_in_percentage_wise',
        'company_allowance_contribution_in_fixed',
        'company_allowance_contribution_in_percentage',
    ];

    protected $casts = [
        'canteen_facility_id' => 'integer',
        'total_absent_days' => 'integer',
        'company_contribution_in_percentage_wise' => 'boolean',
    ];

    public function canteenFacility(): BelongsTo
    {
        return $this->belongsTo(CanteenFacility::class);
    }

    private function audit(string $event, array $old, array $new): void
    {
        ActivityLogger::log(Auth::guard('tenant')->user(), $this, $old, $new, $event);
    }
}
