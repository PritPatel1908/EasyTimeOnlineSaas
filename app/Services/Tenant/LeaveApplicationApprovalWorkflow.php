<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateLeaveApplicationApprovals;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\User;
use Illuminate\Validation\ValidationException;

class LeaveApplicationApprovalWorkflow
{
    /**
     * @param  array<int, int>  $userIds
     * @return array<int, int>
     */
    public function approvalFlowIds(array $userIds): array
    {
        $users = User::query()
            ->with('leave_approval_flow.approval_flow_details')
            ->whereIn('id', $userIds)
            ->get();

        if ($users->count() !== count(array_unique($userIds))) {
            throw ValidationException::withMessages([
                'users' => 'One or more selected employees could not be found.',
            ]);
        }

        if ($users->contains(static fn (User $user): bool => $user->leave_approval_flow === null)) {
            throw ValidationException::withMessages([
                'users' => 'A leave approval flow must be assigned to every selected employee.',
            ]);
        }

        if ($users->contains(
            static fn (User $user): bool => $user->leave_approval_flow->approval_flow_details->isEmpty(),
        )) {
            throw ValidationException::withMessages([
                'users' => 'The assigned leave approval flow has no approval levels.',
            ]);
        }

        return $users->pluck('leave_approval_flow_id')
            ->map(static fn (mixed $flowId): int => (int) $flowId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public function dispatch(LeaveApplication $application, array $approvalFlowIds, int $actorId): void
    {
        CreateLeaveApplicationApprovals::dispatch(
            (int) $application->id,
            $approvalFlowIds,
            $actorId,
        )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
    }
}
