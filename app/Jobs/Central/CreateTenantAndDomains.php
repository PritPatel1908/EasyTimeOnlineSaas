<?php

declare(strict_types=1);

namespace App\Jobs\Central;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenantAndDomains implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @param array<int, string> $domains */
    public function __construct(
        public string $tenantId,
        public string $companyName,
        public int $companyId,
        public array $domains,
        public array $database,
    ) {}

    public function handle(): void
    {
        DB::transaction(function (): void {
            $tenant = Tenant::firstOrNew(['id' => $this->tenantId]);
            $tenant->company_id = $this->companyId;
            $tenant->name = $this->companyName;
            $tenant->setInternal('db_connection', $this->database['connection']);
            $tenant->setInternal('db_host', $this->database['host']);
            $tenant->setInternal('db_port', $this->database['port']);
            $tenant->setInternal('db_name', $this->database['database']);
            $tenant->setInternal('db_username', $this->database['username']);
            $tenant->setInternal('db_password', $this->database['password']);
            $tenant->save();

            foreach ($this->domains as $domain) {
                Domain::firstOrCreate(['domain' => $domain], ['tenant_id' => $tenant->id]);
            }
        });
    }
}
