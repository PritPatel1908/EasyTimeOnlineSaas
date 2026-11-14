<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class MasterSnapshot extends Model
{
    protected $fillable = [
        'master_type',
        'master_id',
        'type',
        'value',
        'from_date',
        'to_date',
        'user_id',
        'for_all',
    ];

    protected $casts = [
        'master_id' => 'integer',
        'value' => 'array',
        'from_date' => 'date',
        'to_date' => 'date',
        'user_id' => 'integer',
        'for_all' => 'boolean',
    ];
}
