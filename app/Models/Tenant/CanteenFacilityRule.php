<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenFacilityRule extends Model
{
    protected $table = 'canteen_facility_rules';

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
}
