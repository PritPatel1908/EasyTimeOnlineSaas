<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateShortLeaveApplicationApprovals;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShortLeaveApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, int $actorId): ShortLeaveApplication
    {
        $userId = (int) $payload['user_id'];
        unset($payload['user_id']);

        return $this->createForUsers($payload, [$userId], $actorId)[0];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @return array<int, ShortLeaveApplication>
     */
    public function createForUsers(array $payload, array $userIds, int $actorId): array
    {
        unset($payload['user_id']);
        $payload = array_replace($payload, ['status' => 1]);

        return DB::transaction(function () use ($payload, $userIds, $actorId): array {
            $applications = [];
            foreach (array_values(array_unique($userIds)) as $userId) {
                $flowId = $this->approvalFlowId($userId);
                $application = ShortLeaveApplication::query()->create($payload + [
                    'user_id' => $userId,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                CreateShortLeaveApplicationApprovals::dispatch(
                    (int) $application->id,
                    [$flowId],
                    $actorId,
                )->onConnection('database_tenant')->onQueue('processing')->afterCommit();

                $applications[] = $application;
            }

            return $applications;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(ShortLeaveApplication $application, array $payload, int $actorId): void
    {
        unset($payload['status']);

        DB::transaction(function () use ($application, $payload, $actorId): void {
            $application = ShortLeaveApplication::query()
                ->with('approval_statuses')
                ->lockForUpdate()
                ->findOrFail($application->id);
            $this->reverseApprovedMinutes($application);
            $flowId = $this->approvalFlowId((int) $payload['user_id']);
            $application->update($payload + ['updated_by' => $actorId]);

            CreateShortLeaveApplicationApprovals::dispatch(
                (int) $application->id,
                [$flowId],
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    public function delete(ShortLeaveApplication $application): void
    {
        DB::transaction(function () use ($application): void {
            $application = ShortLeaveApplication::query()
                ->with('approval_statuses')
                ->lockForUpdate()
                ->findOrFail($application->id);
            $this->reverseApprovedMinutes($application);
            $application->delete();
        });
    }

    private function reverseApprovedMinutes(ShortLeaveApplication $application): void
    {
        if ((int) $application->status !== 1 || $application->overallApprovalStatus() !== 'approved') {
            return;
        }

        $user = User::query()->lockForUpdate()->findOrFail($application->user_id);
        $user->short_leave_minutes = max(
            0,
            (int) $user->short_leave_minutes - $application->creditMinutes(),
        );
        $user->save();
    }

    private function approvalFlowId(int $userId): int
    {
        $user = User::query()
            ->with('short_leave_approval_flow.approval_flow_details')
            ->find($userId);

        if ($user === null) {
            throw ValidationException::withMessages([
                'user_id' => 'The selected employee could not be found.',
            ]);
        }

        if ($user->short_leave_approval_flow === null) {
            throw ValidationException::withMessages([
                'user_id' => 'A short leave approval flow must be assigned to the selected employee.',
            ]);
        }

        if ($user->short_leave_approval_flow->approval_flow_details->isEmpty()) {
            throw ValidationException::withMessages([
                'user_id' => 'The assigned short leave approval flow has no approval levels.',
            ]);
        }

        return (int) $user->short_leave_approval_flow_id;
    }
}
