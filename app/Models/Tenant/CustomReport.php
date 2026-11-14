<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomReport extends Model
{
    protected $fillable = [
        'report_name',
        'from_date',
        'to_date',
        'report_orientation',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'report_orientation' => 'string',
        'status' => 'integer',
    ];

    public function custom_report_details()
    {
        return $this->hasMany(CustomReportDetail::class);
    }
}
