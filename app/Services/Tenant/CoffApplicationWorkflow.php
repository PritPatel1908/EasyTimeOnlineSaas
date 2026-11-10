<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateCoffApplicationApprovals;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoffApplicationWorkflow
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, int $actorId): CoffApplication
    {
        $userId = (int) $payload['user_id'];
        unset($payload['user_id']);

        return $this->createForUsers($payload, [$userId], $actorId)[0];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $userIds
     * @return array<int, CoffApplication>
     */
    public function createForUsers(array $payload, array $userIds, int $actorId): array
    {
        unset($payload['user_id']);

        return DB::transaction(function () use ($payload, $userIds, $actorId): array {
            $applications = [];
            foreach (array_values(array_unique(array_map('intval', $userIds))) as $userId) {
                $user = User::query()->lockForUpdate()->find($userId);
                if ($user === null) {
                    throw ValidationException::withMessages([
                        'users' => 'One or more selected employees could not be found.',
                    ]);
                }

                app(CoffApplicationPolicy::class)->validate($payload, [$userId]);
                $flowId = $this->approvalFlowId($user);
                $application = CoffApplication::query()->create($payload + [
                    'user_id' => $userId,
                    'status' => 'pending',
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);
                $this->dispatchApprovals($application, $flowId, $actorId);
                $applications[] = $application;
            }

            return $applications;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(CoffApplication $application, array $payload, int $actorId): void
    {
        DB::transaction(function () use ($application, $payload, $actorId): void {
            $application = CoffApplication::query()
                ->lockForUpdate()
                ->findOrFail($application->id);
            if ($application->status === 'approved') {
                throw ValidationException::withMessages([
                    'application' => 'Approved COFF applications cannot be changed.',
                ]);
            }

            $user = User::query()->lockForUpdate()->find($payload['user_id']);
            if ($user === null) {
                throw ValidationException::withMessages([
                    'user_id' => 'The selected employee could not be found.',
                ]);
            }

            app(CoffApplicationPolicy::class)->validate(
                $payload,
                [(int) $user->id],
                (int) $application->id,
            );
            $flowId = $this->approvalFlowId($user);
            $application->update($payload + [
                'status' => 'pending',
                'updated_by' => $actorId,
            ]);

            $this->dispatchApprovals($application, $flowId, $actorId);
        });
    }

    public function delete(CoffApplication $application, int $actorId): void
    {
        DB::transaction(function () use ($application, $actorId): void {
            $application = CoffApplication::query()->lockForUpdate()->findOrFail($application->id);
            if ($application->status === 'approved') {
                throw ValidationException::withMessages([
                    'application' => 'Approved COFF applications cannot be deleted.',
                ]);
            }

            $application->update(['deleted_by' => $actorId]);
            $application->delete();
        });
    }

    private function approvalFlowId(User $user): int
    {
        $user->loadMissing('coff_approval_flow.approval_flow_details');
        $flow = $user->coff_approval_flow;

        if ($flow === null) {
            throw ValidationException::withMessages([
                'user_id' => 'A COFF approval flow must be assigned to the selected employee.',
            ]);
        }

        if ($flow->approval_flow_details->isEmpty()) {
            throw ValidationException::withMessages([
                'user_id' => 'The assigned COFF approval flow has no approval levels.',
            ]);
        }

        return (int) $flow->id;
    }

    private function dispatchApprovals(CoffApplication $application, int $flowId, int $actorId): void
    {
        CreateCoffApplicationApprovals::dispatch(
            (int) $application->id,
            [$flowId],
            $actorId,
        )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
    }
}
