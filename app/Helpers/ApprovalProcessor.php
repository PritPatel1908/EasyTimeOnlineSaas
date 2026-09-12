<?php

namespace App\Helpers;

use Filament\Notifications\Notification;

class ApprovalProcessor
{
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
