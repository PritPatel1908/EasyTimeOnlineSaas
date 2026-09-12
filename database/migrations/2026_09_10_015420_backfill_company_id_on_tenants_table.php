<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')
            ->whereNull('company_id')
            ->select(['id', 'data'])
            ->get()
            ->each(function (object $tenant): void {
                $data = is_string($tenant->data) ? json_decode($tenant->data, true) : $tenant->data;
                $companyId = is_array($data) ? ($data['company_id'] ?? null) : null;

                if (is_numeric($companyId)) {
                    DB::table('tenants')->where('id', $tenant->id)->update([
                        'company_id' => (int) $companyId,
                    ]);
                }
            });
    }

    public function down(): void
    {
        /**
         * Backfilled links must remain intact when rolling back later migrations.
         */
    }
};
