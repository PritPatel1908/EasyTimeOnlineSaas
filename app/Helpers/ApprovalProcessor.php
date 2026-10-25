<?php

namespace App\Helpers;

use App\Jobs\Tenant\DebitLeaveApplication;
use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\Coff;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApprovalProcessor
{
    public static function processDecision(
        int $approvalStatusDetailId,
        int $actorId,
        string $decision,
        ?string $rejectionReason,
    ): void {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw new RuntimeException('The approval decision is invalid.');
        }

        if ($decision === 'rejected' && trim((string) $rejectionReason) === '') {
            throw new RuntimeException('A rejection reason is required.');
        }

        DB::transaction(function () use ($approvalStatusDetailId, $actorId, $decision, $rejectionReason): void {
            $detail = ApprovalStatusDetails::query()->findOrFail($approvalStatusDetailId);
            $status = ApprovalStatus::query()->findOrFail($detail->approval_status_id);

            $approvable = $status->approvable;
            if (! $approvable instanceof LeaveApplication
                && ! $approvable instanceof Coff
                && ! $approvable instanceof ManualAttendanceApplication
                && ! $approvable instanceof ManualPunchApplication
                && ! $approvable instanceof OutDuty
                && ! $approvable instanceof ShortLeaveApplication
                && ! $approvable instanceof ShiftChangeApplication
                && ! $approvable instanceof WeekOffChangeApplication
                && ! $approvable instanceof WeekOffSwapApplication) {
                throw new RuntimeException('The approval request type is not supported.');
            }

            $statuses = ApprovalStatus::query()
                ->where('approvable_type', $status->approvable_type)
                ->where('approvable_id', $status->approvable_id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $status = $statuses->firstWhere('id', $status->id);
            if ($status === null) {
                throw new RuntimeException('The approval status could not be locked.');
            }

            $detail = ApprovalStatusDetails::query()->whereKey($approvalStatusDetailId)->lockForUpdate()->firstOrFail();
            $flowDetail = $detail->approval_flow_detail;
            if ($status->approval_status !== 'pending'
                || $detail->approval_status !== 'pending'
                || $flowDetail === null
                || (int) $flowDetail->approval_flow_id !== (int) $status->approval_flow_id
                || (int) $flowDetail->level !== (int) $detail->level
                || ! $flowDetail->users()->whereKey($actorId)->exists()) {
                throw new RuntimeException('You are not assigned to this pending approval level.');
            }

            $hasUnapprovedPriorLevel = $status->approval_status_details()
                ->where('level', '<', $detail->level)
                ->where('approval_status', '!=', 'approved')
                ->exists();
            if ($hasUnapprovedPriorLevel) {
                throw new RuntimeException('This approval level is not active yet.');
            }

            $detail->update([
                'approval_status' => $decision,
                'rejection_reason' => $decision === 'rejected' ? trim((string) $rejectionReason) : null,
                'timestamp' => now(),
                'user_id' => $actorId,
                'updated_by' => $actorId,
            ]);

            if ($decision === 'rejected') {
                $status->approval_status_details()
                    ->whereKeyNot($detail->id)
                    ->where('approval_status', 'pending')
                    ->update([
                        'approval_status' => 'cancelled',
                        'updated_by' => $actorId,
                        'updated_at' => now(),
                    ]);

                ApprovalStatus::query()
                    ->whereKey($status->id)
                    ->update([
                        'approval_status' => 'rejected',
                        'updated_by' => $actorId,
                        'updated_at' => now(),
                    ]);

                $otherPendingStatuses = $statuses->where('id', '!=', $status->id)
                    ->where('approval_status', 'pending');
                foreach ($otherPendingStatuses as $otherStatus) {
                    $otherStatus->approval_status_details()
                        ->where('approval_status', 'pending')
                        ->update([
                            'approval_status' => 'cancelled',
                            'updated_by' => $actorId,
                            'updated_at' => now(),
                        ]);
                    $otherStatus->update([
                        'approval_status' => 'cancelled',
                        'updated_by' => $actorId,
                    ]);
                }

                return;
            }

            $remainingLevels = $status->approval_status_details()
                ->where('approval_status', '!=', 'approved')
                ->exists();
            if ($remainingLevels) {
                return;
            }

            $status->update([
                'approval_status' => 'approved',
                'updated_by' => $actorId,
            ]);

            $allFlowsApproved = $statuses->every(
                static fn (ApprovalStatus $approvalStatus): bool => $approvalStatus->id === $status->id
                    || $approvalStatus->approval_status === 'approved',
            );
            if ($allFlowsApproved) {
                FinalizeApprovedApplication::dispatch($status->approvable_type, (int) $status->approvable_id)
                    ->onConnection('database_tenant')
                    ->onQueue('processing')
                    ->afterCommit();
            }
        });
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public static function CreateLeaveApplicationApprovals(
        LeaveApplication $application,
        array $approvalFlowIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $approvalFlowIds, $actorId): void {
            $flowIds = array_values(array_unique(array_map('intval', $approvalFlowIds)));
            if ($flowIds === []) {
                throw new RuntimeException('At least one approval flow is required for a leave application.');
            }

            $existingStatuses = $application->approval_statuses()
                ->lockForUpdate()
                ->get()
                ->keyBy('approval_flow_id');

            foreach ($flowIds as $flowId) {
                $flow = ApprovalFlow::query()
                    ->with('approval_flow_details')
                    ->findOrFail($flowId);
                $flowDetails = $flow->approval_flow_details->sortBy('level');
                if ($flowDetails->isEmpty()) {
                    throw new RuntimeException("Approval flow {$flowId} has no approval levels.");
                }

                $approvalStatus = $existingStatuses->get($flowId);
                if ($approvalStatus === null) {
                    $approvalStatus = $application->approval_statuses()->create([
                        'approval_flow_id' => $flowId,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                } else {
                    $approvalStatus->approval_status_details()->delete();
                    $approvalStatus->update([
                        'approval_status' => 'pending',
                        'updated_by' => $actorId,
                    ]);
                }

                foreach ($flowDetails as $flowDetail) {
                    $approvalStatus->approval_status_details()->create([
                        'level' => $flowDetail->level,
                        'approval_flow_detail_id' => $flowDetail->id,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            $application->approval_statuses()
                ->whereNotIn('approval_flow_id', $flowIds)
                ->delete();
        });

        DebitLeaveApplication::dispatch($application)
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public static function CreateManualAttendanceApplicationApprovals(
        ManualAttendanceApplication $application,
        array $approvalFlowIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $approvalFlowIds, $actorId): void {
            $flowIds = array_values(array_unique(array_map('intval', $approvalFlowIds)));
            if ($flowIds === []) {
                throw new RuntimeException('At least one approval flow is required for a manual attendance application.');
            }

            $existingStatuses = $application->approval_statuses()
                ->lockForUpdate()
                ->get()
                ->keyBy('approval_flow_id');

            foreach ($flowIds as $flowId) {
                $flow = ApprovalFlow::query()
                    ->with('approval_flow_details')
                    ->findOrFail($flowId);
                $flowDetails = $flow->approval_flow_details->sortBy('level');
                if ($flowDetails->isEmpty()) {
                    throw new RuntimeException("Approval flow {$flowId} has no approval levels.");
                }

                $approvalStatus = $existingStatuses->get($flowId);
                if ($approvalStatus === null) {
                    $approvalStatus = $application->approval_statuses()->create([
                        'approval_flow_id' => $flowId,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                } else {
                    $approvalStatus->approval_status_details()->delete();
                    $approvalStatus->update([
                        'approval_status' => 'pending',
                        'updated_by' => $actorId,
                    ]);
                }

                foreach ($flowDetails as $flowDetail) {
                    $approvalStatus->approval_status_details()->create([
                        'level' => $flowDetail->level,
                        'approval_flow_detail_id' => $flowDetail->id,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            $application->approval_statuses()
                ->whereNotIn('approval_flow_id', $flowIds)
                ->delete();
        });
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public static function CreateManualPunchApplicationApprovals(
        ManualPunchApplication $application,
        array $approvalFlowIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $approvalFlowIds, $actorId): void {
            $flowIds = array_values(array_unique(array_map('intval', $approvalFlowIds)));
            if ($flowIds === []) {
                throw new RuntimeException('At least one approval flow is required for a manual punch application.');
            }

            $existingStatuses = $application->approval_statuses()
                ->lockForUpdate()
                ->get()
                ->keyBy('approval_flow_id');

            foreach ($flowIds as $flowId) {
                $flow = ApprovalFlow::query()
                    ->with('approval_flow_details')
                    ->findOrFail($flowId);
                $flowDetails = $flow->approval_flow_details->sortBy('level');
                if ($flowDetails->isEmpty()) {
                    throw new RuntimeException("Approval flow {$flowId} has no approval levels.");
                }

                $approvalStatus = $existingStatuses->get($flowId);
                if ($approvalStatus === null) {
                    $approvalStatus = $application->approval_statuses()->create([
                        'approval_flow_id' => $flowId,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                } else {
                    $approvalStatus->approval_status_details()->delete();
                    $approvalStatus->update([
                        'approval_status' => 'pending',
                        'updated_by' => $actorId,
                    ]);
                }

                foreach ($flowDetails as $flowDetail) {
                    $approvalStatus->approval_status_details()->create([
                        'level' => $flowDetail->level,
                        'approval_flow_detail_id' => $flowDetail->id,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            $application->approval_statuses()
                ->whereNotIn('approval_flow_id', $flowIds)
                ->delete();
        });
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public static function CreateWeekOffSwapApplicationApprovals(
        WeekOffSwapApplication $application,
        array $approvalFlowIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $approvalFlowIds, $actorId): void {
            $flowIds = array_values(array_unique(array_map('intval', $approvalFlowIds)));
            if ($flowIds === []) {
                throw new RuntimeException('At least one approval flow is required for a week-off swap application.');
            }

            $existingStatuses = $application->approval_statuses()
                ->lockForUpdate()
                ->get()
                ->keyBy('approval_flow_id');

            foreach ($flowIds as $flowId) {
                $flow = ApprovalFlow::query()
                    ->with('approval_flow_details')
                    ->findOrFail($flowId);
                $flowDetails = $flow->approval_flow_details->sortBy('level');
                if ($flowDetails->isEmpty()) {
                    throw new RuntimeException("Approval flow {$flowId} has no approval levels.");
                }

                $approvalStatus = $existingStatuses->get($flowId);
                if ($approvalStatus === null) {
                    $approvalStatus = $application->approval_statuses()->create([
                        'approval_flow_id' => $flowId,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                } else {
                    $approvalStatus->approval_status_details()->delete();
                    $approvalStatus->update([
                        'approval_status' => 'pending',
                        'updated_by' => $actorId,
                    ]);
                }

                foreach ($flowDetails as $flowDetail) {
                    $approvalStatus->approval_status_details()->create([
                        'level' => $flowDetail->level,
                        'approval_flow_detail_id' => $flowDetail->id,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            $application->approval_statuses()
                ->whereNotIn('approval_flow_id', $flowIds)
                ->delete();
        });
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public static function CreateShiftChangeApplicationApprovals(
        ShiftChangeApplication $application,
        array $approvalFlowIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $approvalFlowIds, $actorId): void {
            $flowIds = array_values(array_unique(array_map('intval', $approvalFlowIds)));
            if ($flowIds === []) {
                throw new RuntimeException('At least one approval flow is required for a shift change application.');
            }

            $existingStatuses = $application->approval_statuses()
                ->lockForUpdate()
                ->get()
                ->keyBy('approval_flow_id');

            foreach ($flowIds as $flowId) {
                $flow = ApprovalFlow::query()
                    ->with('approval_flow_details')
                    ->findOrFail($flowId);
                $flowDetails = $flow->approval_flow_details->sortBy('level');
                if ($flowDetails->isEmpty()) {
                    throw new RuntimeException("Approval flow {$flowId} has no approval levels.");
                }

                $approvalStatus = $existingStatuses->get($flowId);
                if ($approvalStatus === null) {
                    $approvalStatus = $application->approval_statuses()->create([
                        'approval_flow_id' => $flowId,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                } else {
                    $approvalStatus->approval_status_details()->delete();
                    $approvalStatus->update([
                        'approval_status' => 'pending',
                        'updated_by' => $actorId,
                    ]);
                }

                foreach ($flowDetails as $flowDetail) {
                    $approvalStatus->approval_status_details()->create([
                        'level' => $flowDetail->level,
                        'approval_flow_detail_id' => $flowDetail->id,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            $application->approval_statuses()
                ->whereNotIn('approval_flow_id', $flowIds)
                ->delete();
        });
    }

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public static function CreateWeekOffChangeApplicationApprovals(
        WeekOffChangeApplication $application,
        array $approvalFlowIds,
        int $actorId,
    ): void {
        DB::transaction(function () use ($application, $approvalFlowIds, $actorId): void {
            $flowIds = array_values(array_unique(array_map('intval', $approvalFlowIds)));
            if ($flowIds === []) {
                throw new RuntimeException('At least one approval flow is required for a week-off change application.');
            }

            $existingStatuses = $application->approval_statuses()
                ->lockForUpdate()
                ->get()
                ->keyBy('approval_flow_id');

            foreach ($flowIds as $flowId) {
                $flow = ApprovalFlow::query()
                    ->with('approval_flow_details')
                    ->findOrFail($flowId);
                $flowDetails = $flow->approval_flow_details->sortBy('level');
                if ($flowDetails->isEmpty()) {
                    throw new RuntimeException("Approval flow {$flowId} has no approval levels.");
                }

                $approvalStatus = $existingStatuses->get($flowId);
                if ($approvalStatus === null) {
                    $approvalStatus = $application->approval_statuses()->create([
                        'approval_flow_id' => $flowId,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                } else {
                    $approvalStatus->approval_status_details()->delete();
                    $approvalStatus->update([
                        'approval_status' => 'pending',
                        'updated_by' => $actorId,
                    ]);
                }

                foreach ($flowDetails as $flowDetail) {
                    $approvalStatus->approval_status_details()->create([
                        'level' => $flowDetail->level,
                        'approval_flow_detail_id' => $flowDetail->id,
                        'approval_status' => 'pending',
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            $application->approval_statuses()
                ->whereNotIn('approval_flow_id', $flowIds)
                ->delete();
        });
    }

    public static function CreateApproval($record, $approval_flow)
    {
        $record->approval_status()->create([
            'approval_status' => 'draft',
            'approval_flow_id' => $approval_flow->id,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        // Create approval status_detail
        if ($record->approval_status == null) {
            $approval_flow = auth()->user()->approval_flow;
            $record->approval_status()->create([
                'approval_status' => 'draft',
                'approval_flow_id' => $approval_flow->id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
        }

        if ($record->approval_status->approval_flow == null) {
            Notification::make()
                ->title('Approval Flow Not Found')
                ->body('User must need an approval flow. Ask your admin to assign an approval flow to you.')
                ->persistent()
                ->danger()
                ->send();

            return false;
        }

        if ($record->approval_status->approval_status != 'draft' && $record->approval_status->approval_status != 'pending') {
            Notification::make()
                ->title('Request Already Sent for Approval')
                ->body('Request already sent for approval.')
                ->persistent()
                ->danger()
                ->send();

            return false;
        }

        if ($record->approval_status->approval_status != 'draft' && $record->approval_status->approval_status == 'pending') {
            Notification::make()
                ->title('Request Already Sent for Approval, Update Request')
                ->body('Request already sent for approval, Update Request.')
                ->persistent()
                ->success()
                ->send();

            return false;
        }

        $flow_detail = $record->approval_status->approval_flow->approval_flow_details()->orderBy('level', 'asc')->first();
        if ($flow_detail == null) {
            Notification::make()
                ->title('Approval Flow Not Found')
                ->body('Approval flow details not found.')
                ->persistent()
                ->danger()
                ->send();

            return false;
        }

        $record->approval_status->approval_status_details()->create([
            'level' => $flow_detail->level,
            'approval_flow_detail_id' => $flow_detail->id,
            'approval_status' => 'pending',
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        $record->approval_status->update([
            'approval_status' => 'pending',
            'updated_by' => auth()->user()->id,
        ]);

        $record->save();

        Notification::make()
            ->title('Request Sent for Approval')
            ->body('Request Sent for Approval.')
            ->persistent()
            ->success()
            ->send();
    }
}
