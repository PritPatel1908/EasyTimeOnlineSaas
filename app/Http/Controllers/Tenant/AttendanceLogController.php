<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['search'] ?? '');

        $query = AttendanceLog::query()->with('user');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('user_code', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%');
                    });

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        }

        return view('time-management.attendance-logs.index', [
            'attendanceLogs' => $query
                ->latest('datetime')
                ->latest('id')
                ->paginate(25)
                ->withQueryString(),
            'search' => $search,
            'employees' => User::query()
                ->where('user_type', 'employee')
                ->orderBy('code')
                ->get(['id', 'name', 'code']),
        ]);
    }

    public function reprocessRange(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_datetime' => ['required', 'date_format:Y-m-d\TH:i'],
            'end_datetime' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:start_datetime'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        $employeeIds = array_values(array_unique(array_map('intval', $validated['employee_ids'] ?? [])));

        if ($employeeIds !== []) {
            $employeeCount = User::query()
                ->where('user_type', 'employee')
                ->whereIn('id', $employeeIds)
                ->count();

            if ($employeeCount !== count($employeeIds)) {
                throw ValidationException::withMessages([
                    'employee_ids' => 'Select valid employees only.',
                ]);
            }
        }

        ReprocessAttendanceLogs::dispatch(
            str_replace('T', ' ', $validated['start_datetime']).':00',
            str_replace('T', ' ', $validated['end_datetime']).':59',
            $employeeIds,
        )
            ->onConnection('database_tenant')
            ->onQueue('processing');

        return redirect(url('time-management/attendance-logs'))
            ->with('success', 'Attendance log reprocessing has started. Matching punches and attendance will be recalculated.');
    }

    public function show(Request $request): View
    {
        $attendanceLog = $this->resolveAttendanceLog($request);
        $attendanceLog->load(['user', 'machine', 'area', 'process_tags']);

        return view('time-management.attendance-logs.show', [
            'attendanceLog' => $attendanceLog,
        ]);
    }

    public function reprocess(Request $request): RedirectResponse
    {
        $attendanceLog = $this->resolveAttendanceLog($request);

        if ($attendanceLog->is_locked) {
            return redirect(url('time-management/attendance-logs/'.$attendanceLog->id))
                ->with('error', 'Locked attendance logs cannot be reprocessed.');
        }

        if ($attendanceLog->is_ignored) {
            return redirect(url('time-management/attendance-logs/'.$attendanceLog->id))
                ->with('error', 'Ignored attendance logs cannot be reprocessed.');
        }

        $attendanceLog->update([
            'is_calculated' => false,
            'is_staged' => false,
            'has_error' => false,
        ]);

        ProcessPendingLogs::dispatch((int) $attendanceLog->id)
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();

        return redirect(url('time-management/attendance-logs/'.$attendanceLog->id))
            ->with('success', 'Punch reprocessing has started.');
    }

    private function resolveAttendanceLog(Request $request): AttendanceLog
    {
        $attendanceLog = $request->route('attendance_log');

        return $attendanceLog instanceof AttendanceLog
            ? $attendanceLog
            : AttendanceLog::query()->findOrFail($attendanceLog);
    }
}
