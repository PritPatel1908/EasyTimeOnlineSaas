<?php

namespace App\Helpers;

use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApprovalProcessor
{
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
