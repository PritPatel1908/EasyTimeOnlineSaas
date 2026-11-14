<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomReportGeneration extends Model
{
    protected $fillable = [
        'custom_report_id',
        'report_format',
        'report_status',
        'report_path',
    ];

    protected $casts = [
        'custom_report_id' => 'integer',
        'report_format' => ReportFormat::class,
        'report_status' => ReportStatus::class,
    ];

    public function custom_report(): BelongsTo
    {
        return $this->belongsTo(CustomReport::class);
    }
}
