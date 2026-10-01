<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class BloodGroup extends Model
{
    protected $table = 'blood_groups';

    protected $fillable = [
        'code',
        'name',
    ];
}
