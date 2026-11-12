<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'template_type',
        'email_content',
        'status',
    ];
}
