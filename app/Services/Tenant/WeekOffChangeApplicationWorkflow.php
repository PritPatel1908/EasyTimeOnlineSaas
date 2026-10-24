<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateWeekOffChangeApplicationApprovals;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChangeApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeekOffChangeApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     */
    public function create(array $payload, array $userIds, int $actorId): WeekOffChangeApplication
    {
        return DB::transaction(function () use ($payload, $userIds, $actorId): WeekOffChangeApplication {
            $flowIds = $this->approvalFlowIds($userIds);
            $details = $payload['details'];
            unset($payload['details']);
            $application = WeekOffChangeApplication::query()->create($payload + [
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            $application->Users()->sync($userIds);
            $application->WeekOffChangeDetails()->createMany($details);

            CreateWeekOffChangeApplicationApprovals::dispatch(
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
    public function update(WeekOffChangeApplication $application, array $payload, array $userIds, int $actorId): void
    {
        DB::transaction(function () use ($application, $payload, $userIds, $actorId): void {
            $flowIds = $this->approvalFlowIds($userIds);
            $details = $payload['details'];
            unset($payload['details']);

            $application->update($payload + ['updated_by' => $actorId]);
            $application->Users()->sync($userIds);
            $application->WeekOffChangeDetails()->delete();
            $application->WeekOffChangeDetails()->createMany($details);

            CreateWeekOffChangeApplicationApprovals::dispatch(
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
            ->with('week_off_change_approval_flow.approval_flow_details')
            ->whereIn('id', $userIds)
            ->get();

        if ($users->count() !== count(array_unique($userIds))) {
            throw ValidationException::withMessages([
                'users' => 'One or more selected employees could not be found.',
            ]);
        }

        if ($users->contains(static fn (User $user): bool => $user->week_off_change_approval_flow === null)) {
            throw ValidationException::withMessages([
                'users' => 'A week-off change approval flow must be assigned to every selected employee.',
            ]);
        }

        if ($users->contains(static fn (User $user): bool => $user->week_off_change_approval_flow->approval_flow_details->isEmpty())) {
            throw ValidationException::withMessages([
                'users' => 'The assigned week-off change approval flow has no approval levels.',
            ]);
        }

        return $users->pluck('week_off_change_approval_flow_id')
            ->unique()
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }
}
