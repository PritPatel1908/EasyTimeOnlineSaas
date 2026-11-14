<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomReportDetail extends Model
{
    protected $fillable = [
        'custom_report_id',
        'custom_report_fields',
        'custom_report_filter',
    ];

    protected $casts = [
        'custom_report_id' => 'integer',
        'custom_report_fields' => 'array',
        'custom_report_filter' => 'array',
    ];

    public function custom_report()
    {
        return $this->belongsTo(CustomReport::class);
    }
}
