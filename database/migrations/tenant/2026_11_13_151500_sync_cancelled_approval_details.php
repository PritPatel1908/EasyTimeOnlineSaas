<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cancelledStatuses = DB::table('approval_statuses as statuses')
            ->join('approved_application_effect_snapshots as snapshots', function ($join): void {
                $join->on('snapshots.approvable_type', '=', 'statuses.approvable_type')
                    ->on('snapshots.approvable_id', '=', 'statuses.approvable_id');
            })
            ->where('statuses.approval_status', 'cancelled')
            ->where('snapshots.cancellation_status', 'completed')
            ->get(['statuses.id', 'statuses.updated_by']);

        foreach ($cancelledStatuses as $status) {
            DB::table('approval_status_details')
                ->where('approval_status_id', $status->id)
                ->where('approval_status', '!=', 'cancelled')
                ->update([
                    'approval_status' => 'cancelled',
                    'updated_by' => $status->updated_by,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Historical approval details cannot be safely restored to their prior statuses.
    }
};
