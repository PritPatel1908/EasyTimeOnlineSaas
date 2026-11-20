<?php

use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\User;
use App\Services\Tenant\MasterSnapshotService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $snapshotService = app(MasterSnapshotService::class);

        User::query()
            ->where('user_type', 'employee')
            ->orderBy('id')
            ->chunkById(200, function ($employees) use ($snapshotService): void {
                foreach ($employees as $employee) {
                    $snapshotService->captureApproversForEmployee($employee);
                }
            });
    }

    public function down(): void
    {
        MasterSnapshot::query()
            ->where('master_type', MasterSnapshot::APPROVERS_SNAPSHOT_TYPE)
            ->delete();
    }
};
