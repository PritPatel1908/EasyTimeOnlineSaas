<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\ReverseLeaveApplicationAccountBalance;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
use App\Jobs\Tenant\CancelApprovedRequest;
use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\ApprovedApplicationEffectSnapshot;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\ShortLeaveApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovedRequestCancellationService
{
    public function __construct(
        private ApprovedApplicationEffectSnapshotService $snapshots,
        private ShortLeaveAttendancePunchService $shortLeavePunches,
    ) {}

    public function requestCancellation(ApprovalStatusDetails $approval, int $actorId, string $reason): void
    {
        DB::transaction(function () use ($approval, $actorId, $reason): void {
            [$detail, $status, , , $snapshot] = $this->lockApprovedRequest((int) $approval->id);
            if ($snapshot->cancelled_at !== null) {
                throw ValidationException::withMessages([
                    'decision' => 'This request has already been cancelled.',
                ]);
            }
            if ($snapshot->cancellation_status === 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'Cancellation for this request is already queued.',
                ]);
            }

            $snapshot->update([
                'cancellation_status' => 'pending',
                'cancellation_requested_by' => $actorId,
                'cancellation_requested_at' => now(),
                'cancellation_reason' => trim($reason),
                'cancellation_error' => null,
            ]);

            CancelApprovedRequest::dispatch((int) $detail->id)
                ->onConnection('database_tenant')
                ->onQueue('processing')
                ->afterCommit();
        });
    }

    public function cancel(int $detailId): void
    {
        DB::transaction(function () use ($detailId): void {
            [, , $application, $statuses, $snapshot] = $this->lockApprovedRequest($detailId);
            if ($snapshot->cancelled_at !== null) {
                throw ValidationException::withMessages([
                    'decision' => 'This request has already been cancelled.',
                ]);
            }
            if ($snapshot->cancellation_status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'This request does not have a queued cancellation.',
                ]);
            }

            $actorId = (int) $snapshot->cancellation_requested_by;
            $scope = $this->snapshots->restore($application, $snapshot);

            if ($application instanceof ShortLeaveApplication) {
                $this->shortLeavePunches->removeGeneratedPunches($application);
            }

            if ($application instanceof CoffApplication) {
                $application->update(['status' => 'cancelled', 'updated_by' => $actorId]);
            }

            foreach ($statuses as $row) {
                $row->update([
                    'approval_status' => 'cancelled',
                    'updated_by' => $actorId,
                ]);
            }
            ApprovalStatusDetails::query()
                ->whereIn('approval_status_id', $statuses->modelKeys())
                ->update([
                    'approval_status' => 'cancelled',
                    'updated_by' => $actorId,
                ]);

            if ($application instanceof LeaveApplication) {
                ReverseLeaveApplicationAccountBalance::dispatch(
                    (int) $application->id,
                    $application->users()
                        ->pluck('users.id')
                        ->map(static fn (mixed $userId): int => (int) $userId)
                        ->all(),
                    $actorId,
                    false,
                )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
            }

            if (
                ! $application instanceof ShortLeaveApplication
                && ($application instanceof LeaveApplication
                    || $application instanceof ManualAttendanceApplication
                    || $application instanceof ManualPunchApplication)
            ) {
                $start = $scope['startDate']->copy()->subDay()->startOfDay();
                $end = $scope['endDate']->copy()->addDay()->endOfDay();
                ReprocessAttendanceLogs::dispatch(
                    $start->toDateTimeString(),
                    $end->toDateTimeString(),
                    $scope['userIds'],
                )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
            }

            $snapshot->update([
                'cancellation_status' => 'completed',
                'cancelled_by' => (int) $snapshot->cancellation_requested_by,
                'cancelled_at' => now(),
                'cancellation_error' => null,
            ]);
        });
    }

    public function markFailed(int $detailId, \Throwable $exception): void
    {
        $detail = ApprovalStatusDetails::query()->find($detailId);
        $status = $detail?->main_status;
        if ($status === null) {
            return;
        }

        ApprovedApplicationEffectSnapshot::query()
            ->where('approvable_type', $status->approvable_type)
            ->where('approvable_id', $status->approvable_id)
            ->where('cancellation_status', 'pending')
            ->update([
                'cancellation_status' => 'failed',
                'cancellation_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);
    }

    private function lockApprovedRequest(int $detailId): array
    {
        $detail = ApprovalStatusDetails::query()
            ->whereKey($detailId)
            ->lockForUpdate()
            ->firstOrFail();
        $status = ApprovalStatus::query()
            ->whereKey($detail->approval_status_id)
            ->lockForUpdate()
            ->firstOrFail();
        $application = $status->approvable;

        if ($application === null || $status->approval_status !== 'approved') {
            throw ValidationException::withMessages([
                'decision' => 'Only fully approved requests can be cancelled.',
            ]);
        }

        $statuses = ApprovalStatus::query()
            ->where('approvable_type', $status->approvable_type)
            ->where('approvable_id', $status->approvable_id)
            ->lockForUpdate()
            ->get();
        if ($statuses->isEmpty() || $statuses->contains(
            static fn (ApprovalStatus $row): bool => $row->approval_status !== 'approved',
        )) {
            throw ValidationException::withMessages([
                'decision' => 'All approval flows must be approved before cancellation.',
            ]);
        }

        $snapshot = ApprovedApplicationEffectSnapshot::query()
            ->where('approvable_type', $status->approvable_type)
            ->where('approvable_id', $status->approvable_id)
            ->lockForUpdate()
            ->first();
        if ($snapshot === null) {
            throw ValidationException::withMessages([
                'decision' => 'This request predates effect snapshots and cannot be safely cancelled.',
            ]);
        }

        return [$detail, $status, $application, $statuses, $snapshot];
    }
}
