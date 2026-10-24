<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateWeekOffSwapApplicationApprovals;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwapApplication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeekOffSwapApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @return Collection<int, WeekOffSwapApplication>
     */
    public function create(array $payload, array $userIds, int $actorId): Collection
    {
        return DB::transaction(function () use ($payload, $userIds, $actorId): Collection {
            $groups = $this->usersByApprovalFlow($userIds);
            $application = WeekOffSwapApplication::query()->create($payload + [
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            $application->Users()->sync($userIds);
            CreateWeekOffSwapApplicationApprovals::dispatch(
                (int) $application->id,
                array_map('intval', array_keys($groups)),
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();

            return new Collection([$application]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     */
    public function update(WeekOffSwapApplication $application, array $payload, array $userIds, int $actorId): void
    {
        DB::transaction(function () use ($application, $payload, $userIds, $actorId): void {
            $groups = $this->usersByApprovalFlow($userIds);
            $application->update($payload + ['updated_by' => $actorId]);
            $application->Users()->sync($userIds);
            CreateWeekOffSwapApplicationApprovals::dispatch(
                (int) $application->id,
                array_map('intval', array_keys($groups)),
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    /**
     * @param  array<int, int>  $userIds
     * @return array<int, Collection<int, User>>
     */
    private function usersByApprovalFlow(array $userIds): array
    {
        $users = User::query()
            ->with('week_off_swap_approval_flow.approval_flow_details')
            ->whereIn('id', $userIds)
            ->get();

        if ($users->count() !== count(array_unique($userIds))) {
            throw ValidationException::withMessages([
                'users' => 'One or more selected employees could not be found.',
            ]);
        }

        $missingFlow = $users->filter(
            static fn (User $user): bool => $user->week_off_swap_approval_flow === null,
        );
        if ($missingFlow->isNotEmpty()) {
            throw ValidationException::withMessages([
                'users' => 'A week-off swap approval flow must be assigned to every selected employee.',
            ]);
        }

        $missingDetails = $users->filter(
            static fn (User $user): bool => $user->week_off_swap_approval_flow->approval_flow_details->isEmpty(),
        );
        if ($missingDetails->isNotEmpty()) {
            throw ValidationException::withMessages([
                'users' => 'The assigned week-off swap approval flow has no approval levels.',
            ]);
        }

        return $users->groupBy(
            static fn (User $user): int => (int) $user->week_off_swap_approval_flow_id,
        )->all();
    }
}
