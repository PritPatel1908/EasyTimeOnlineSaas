<?php

declare(strict_types=1);

namespace App\Jobs\Central;

use App\Models\Central\Tenant;
use App\Models\Central\User;
use App\Notifications\TenantReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class DispatchTenantReadyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tenantId, public string $adminEmail) {}

    public function handle(): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);

        $admin = User::query()->where('email', $this->adminEmail)->first();

        if ($admin !== null) {
            $admin->notify(new TenantReady($tenant, true));

            return;
        }

        Notification::route('mail', $this->adminEmail)->notify(new TenantReady($tenant));
    }
}
