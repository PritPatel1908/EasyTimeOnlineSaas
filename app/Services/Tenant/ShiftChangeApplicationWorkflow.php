<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateShiftChangeApplicationApprovals;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftChangeApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @param  array<int, int>  $shiftIds
     * @return Collection<int, ShiftChangeApplication>
     */
    public function create(array $payload, array $userIds, array $shiftIds, int $actorId): Collection
    {
        return DB::transaction(function () use ($payload, $userIds, $shiftIds, $actorId): Collection {
            $flowIds = $this->approvalFlowIds($userIds);
            $application = ShiftChangeApplication::query()->create($payload + [
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            $application->Users()->sync($userIds);
            $application->shifts()->sync($shiftIds);

            CreateShiftChangeApplicationApprovals::dispatch(
                (int) $application->id,
                $flowIds,
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();

            return new Collection([$application]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @param  array<int, int>  $shiftIds
     */
    public function update(
        ShiftChangeApplication $application,
        array $payload,
        array $userIds,
        array $shiftIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $payload, $userIds, $shiftIds, $actorId): void {
            $flowIds = $this->approvalFlowIds($userIds);
            $application->update($payload + ['updated_by' => $actorId]);
            $application->Users()->sync($userIds);
            $application->shifts()->sync($shiftIds);

            CreateShiftChangeApplicationApprovals::dispatch(
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
            ->with('shift_change_approval_flow.approval_flow_details')
            ->whereIn('id', $userIds)
            ->get();

        if ($users->count() !== count(array_unique($userIds))) {
            throw ValidationException::withMessages([
                'users' => 'One or more selected employees could not be found.',
            ]);
        }

        if ($users->contains(static fn (User $user): bool => $user->shift_change_approval_flow === null)) {
            throw ValidationException::withMessages([
                'users' => 'A shift change approval flow must be assigned to every selected employee.',
            ]);
        }

        if ($users->contains(
            static fn (User $user): bool => $user->shift_change_approval_flow->approval_flow_details->isEmpty(),
        )) {
            throw ValidationException::withMessages([
                'users' => 'The assigned shift change approval flow has no approval levels.',
            ]);
        }

        return $users->pluck('shift_change_approval_flow_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
