<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateManualAttendanceApplicationApprovals;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualAttendanceApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     */
    public function create(array $payload, array $userIds, int $actorId): ManualAttendanceApplication
    {
        return DB::transaction(function () use ($payload, $userIds, $actorId): ManualAttendanceApplication {
            $flowIds = $this->approvalFlowIds($userIds);
            $application = ManualAttendanceApplication::query()->create($payload + [
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            $application->Users()->sync($userIds);

            CreateManualAttendanceApplicationApprovals::dispatch(
                (int) $application->id,
                $flowIds,
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();

            return $application;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     */
    public function update(ManualAttendanceApplication $application, array $payload, array $userIds, int $actorId): void
    {
        DB::transaction(function () use ($application, $payload, $userIds, $actorId): void {
            $flowIds = $this->approvalFlowIds($userIds);
            $application->update($payload + ['updated_by' => $actorId]);
            $application->Users()->sync($userIds);

            CreateManualAttendanceApplicationApprovals::dispatch(
                (int) $application->id,
                $flowIds,
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    /**
     * @param  array<int, int>  $userIds
     * @return array<int, int>
     */
    private function approvalFlowIds(array $userIds): array
    {
        $users = User::query()
            ->with('manual_attendance_approval_flow.approval_flow_details')
            ->whereIn('id', $userIds)
            ->get();

        if ($users->count() !== count(array_unique($userIds))) {
            throw ValidationException::withMessages([
                'users' => 'One or more selected employees could not be found.',
            ]);
        }

        if ($users->contains(static fn (User $user): bool => $user->manual_attendance_approval_flow === null)) {
            throw ValidationException::withMessages([
                'users' => 'A manual attendance approval flow must be assigned to every selected employee.',
            ]);
        }

        if ($users->contains(static fn (User $user): bool => $user->manual_attendance_approval_flow->approval_flow_details->isEmpty())) {
            throw ValidationException::withMessages([
                'users' => 'The assigned manual attendance approval flow has no approval levels.',
            ]);
        }

        return $users->pluck('manual_attendance_approval_flow_id')
            ->map(static fn (mixed $flowId): int => (int) $flowId)
            ->unique()
            ->values()
            ->all();
    }
}
