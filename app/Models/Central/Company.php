<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address', 'status'];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(CompanyLicense::class)->latest();
    }
}
