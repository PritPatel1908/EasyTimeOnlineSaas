<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ApprovedApplicationEffectSnapshot extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'snapshot',
        'cancellation_status',
        'cancellation_requested_by',
        'cancellation_requested_at',
        'cancellation_error',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'cancellation_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
