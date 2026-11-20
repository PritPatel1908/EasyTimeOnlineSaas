<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomReport extends Model
{
    protected $fillable = [
        'report_name',
        'from_date',
        'to_date',
        'date_range_preset',
        'report_orientation',
        'summary_orientation',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'date_range_preset' => 'string',
        'report_orientation' => 'string',
        'summary_orientation' => 'string',
        'status' => 'integer',
    ];

    public function custom_report_details()
    {
        return $this->hasMany(CustomReportDetail::class);
    }

    public function generations()
    {
        return $this->hasMany(CustomReportGeneration::class);
    }
}
