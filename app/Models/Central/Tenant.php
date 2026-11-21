<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected static function booted(): void
    {
        static::created(static function (self $tenant): void {
            if (Schema::connection('central')->hasTable('tenant_cron_jobs')) {
                TenantCronJob::ensureForTenant((string) $tenant->getTenantKey());
            }
        });
    }
}
