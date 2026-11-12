<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplateAssignment extends Model
{
    protected $fillable = [
        'application_type',
        'application_action',
        'email_template_id',
        'status',
        'send_to_approver',
        'send_to_another_approver',
        'send_to_employee',
    ];

    protected $casts = [
        'send_to_approver' => 'boolean',
        'send_to_another_approver' => 'boolean',
        'send_to_employee' => 'boolean',
    ];

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }
}
