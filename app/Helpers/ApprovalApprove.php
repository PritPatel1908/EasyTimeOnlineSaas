<?php

namespace App\Helpers;

use App\Jobs\Tenant\ActivityLog;
use App\Jobs\Tenant\ApproveCoff;
use App\Jobs\Tenant\ApproveLeaveApplication;
use App\Jobs\Tenant\ApproveOd;
use App\Jobs\Tenant\ApproveShortLeaveApplication;
use App\Jobs\Tenant\ApproveUserManualAttendance;
use App\Jobs\Tenant\ApproveUserManualPunch;
use App\Jobs\Tenant\CreateShiftMuster;
use App\Jobs\Tenant\CreateWeekOffMuster;
use App\Jobs\Tenant\CreateWeekOffSwap;
use App\Models\Tenant\User;
use Filament\Infolists\Infolist;
use Jenssegers\Agent\Agent;

class ApprovalApprove
{
    public static function infolist(Infolist $infolist)
    {
        $record = $infolist->getRecord();
        if (get_class($record) === "App\Models\Tenant\ShiftChangeApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            CreateShiftMuster::dispatch($record)->onQueue('processing');

            $users = $record->Users()->pluck('user_id')->toArray();
            foreach ($users as $user_id) {
                $user = User::find($user_id);
                $user->shifts()->delete();
            }

            foreach ($users as $user_id) {
                $user = User::find($user_id);
                $user->shift_type = $record->shift_type;
                $user->save();
                if ($record->shift_type == 'auto') {
                    $user->shifts()->sync($record->shifts()->pluck('shift_id'));
                } elseif ($record->shift_type == 'rotational') {
                    $user->shift_rotation_id = $record->shift_rotation->id;
                    $user->save();
                } else {
                    $user->shifts()->sync($record->shifts()->pluck('shift_id'));
                }
            }
        } elseif (get_class($record) === "App\Models\Tenant\WeekOffChangeApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            CreateWeekOffMuster::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\WeekOffSwapApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            CreateWeekOffSwap::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\ManualPunchApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            ApproveUserManualPunch::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\ManualAttendanceApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            ApproveUserManualAttendance::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\LeaveApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            ApproveLeaveApplication::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\ShortLeaveApplication") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            ApproveShortLeaveApplication::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\Coff") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            ApproveCoff::dispatch($record)->onQueue('processing');
        } elseif (get_class($record) === "App\Models\Tenant\OutDuty") {
            // $old_record = [];
            // $agent = new Agent();
            // $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            // $os = $agent->platform();
            // $new_record = [
            //     'ip' => request()->ip(),
            //     'browser' => $os . " > " . $browser,
            //     'approval_request_type' => get_class($record),
            //     'status' => "approved",
            //     'approved_by' => auth()->user()->name . "(" . auth()->user()->code . ")",
            // ];
            // ActivityLog::dispatch(auth()->user(), $record, $old_record, $new_record, 'approved')->onQueue('processing');

            ApproveOd::dispatch($record)->onQueue('processing');
        } else {
        }
    }
}
