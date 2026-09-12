<?php

declare(strict_types=1);

namespace App\Jobs\Central;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SeedTenantData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tenantId) {}

    public function handle(): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);

        $tenant->run(function (): void {
            Artisan::call('db:seed', array_merge(
                config('tenancy.seeder_parameters'),
                [
                    '--force' => true,
                ],
            ));
        });
    }
}
