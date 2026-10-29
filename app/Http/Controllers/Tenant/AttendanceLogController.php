<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\DeleteAttendanceLogs;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
use App\Jobs\Tenant\RestoreAttendanceLogs;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceLogController extends Controller
{
    public function timeCard(Request $request): View
    {
        $currentYear = (int) now()->format('Y');
        $firstLogDate = AttendanceLog::query()->min('datetime');
        $lastLogDate = AttendanceLog::query()->max('datetime');
        $firstYear = $firstLogDate ? Carbon::parse($firstLogDate)->year : $currentYear;
        $lastYear = max($lastLogDate ? Carbon::parse($lastLogDate)->year : $currentYear, $currentYear);
        $years = range($lastYear, min($firstYear, $currentYear));

        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $year = (int) ($filters['year'] ?? $currentYear);

        if (! in_array($year, $years, true)) {
            throw ValidationException::withMessages([
                'year' => 'Select a year available in the year list.',
            ]);
        }

        $startDate = $filters['start_date'] ?? Carbon::create($year, 1, 1)->toDateString();
        $endDate = $filters['end_date'] ?? Carbon::create($year, 12, 31)->toDateString();

        if (
            Carbon::parse($startDate)->year !== $year
            || Carbon::parse($endDate)->year !== $year
            || Carbon::parse($endDate)->lt(Carbon::parse($startDate))
        ) {
            throw ValidationException::withMessages([
                'start_date' => 'Choose a valid date range within the selected year.',
            ]);
        }

        $employee = null;
        if (! empty($filters['employee_id'])) {
            $employee = User::query()
                ->where('user_type', 'employee')
                ->find($filters['employee_id']);

            if (! $employee) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Select a valid employee.',
                ]);
            }
        }

        $query = Attendance::year($year)
            ->with('user')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employee) {
            $query->where('user_id', $employee->id);
        }

        return view('time-management.time-card.index', [
            'attendanceRecords' => $query
                ->orderByDesc('date')
                ->latest('id')
                ->paginate(50)
                ->withQueryString(),
            'employees' => User::query()
                ->where('user_type', 'employee')
                ->orderBy('code')
                ->get(['id', 'name', 'code']),
            'years' => $years,
            'year' => $year,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeId' => (string) ($employee?->id ?? ''),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'deleted' => ['nullable', 'boolean'],
        ]);
        $search = trim($filters['search'] ?? '');
        $showDeleted = $request->boolean('deleted');

        $query = AttendanceLog::query()->with('user');

        if ($showDeleted) {
            $query->onlyTrashed();
        }

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
            'showDeleted' => $showDeleted,
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

    public function deleteRange(Request $request): RedirectResponse
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

        DeleteAttendanceLogs::dispatch(
            str_replace('T', ' ', $validated['start_datetime']).':00',
            str_replace('T', ' ', $validated['end_datetime']).':59',
            $employeeIds,
        )
            ->onConnection('database_tenant')
            ->onQueue('processing');

        return redirect(url('time-management/attendance-logs'))
            ->with('success', 'Attendance log deletion has started for matching unlocked punches.');
    }

    public function restoreRange(Request $request): RedirectResponse
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

        RestoreAttendanceLogs::dispatch(
            str_replace('T', ' ', $validated['start_datetime']).':00',
            str_replace('T', ' ', $validated['end_datetime']).':59',
            $employeeIds,
        )
            ->onConnection('database_tenant')
            ->onQueue('processing');

        return redirect(url('time-management/attendance-logs?deleted=1'))
            ->with('success', 'Attendance log restoration has started for matching deleted punches.');
    }

    public function show(Request $request): View
    {
        $attendanceLog = $this->resolveAttendanceLog($request, true);
        $attendanceLog->load(['user', 'machine', 'area', 'process_tags']);

        return view('time-management.attendance-logs.show', [
            'attendanceLog' => $attendanceLog,
        ]);
    }

    public function reprocess(Request $request): RedirectResponse
    {
        $attendanceLog = $this->resolveAttendanceLog($request);

        $status = $attendanceLog->getRawOriginal('status');

        if ($attendanceLog->is_locked || $attendanceLog->trashed() || ($status !== null && (int) $status !== 1)) {
            return redirect(url('time-management/attendance-logs/'.$attendanceLog->id))
                ->with('error', 'Locked or deleted attendance logs cannot be reprocessed.');
        }

        $attendanceLog->update([
            'is_calculated' => false,
            'is_staged' => false,
            'is_ignored' => false,
            'has_error' => false,
        ]);

        ProcessPendingLogs::dispatch((int) $attendanceLog->id)
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();

        return redirect(url('time-management/attendance-logs/'.$attendanceLog->id))
            ->with('success', 'Punch reprocessing has started.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $attendanceLog = $this->resolveAttendanceLog($request);

        if ($attendanceLog->trashed()) {
            return redirect(url('time-management/attendance-logs?deleted=1'))
                ->with('error', 'This punch has already been deleted.');
        }

        if ($attendanceLog->is_locked) {
            return redirect(url('time-management/attendance-logs/'.$attendanceLog->id))
                ->with('error', 'Locked attendance logs cannot be deleted.');
        }

        DB::transaction(function () use ($attendanceLog): void {
            $attendanceLog->status = 0;
            $attendanceLog->is_calculated = false;
            $attendanceLog->is_ignored = true;
            $attendanceLog->save();
            $attendanceLog->delete();
        });

        return redirect(url('time-management/attendance-logs'))
            ->with('success', 'Punch deleted successfully.');
    }

    public function restore(Request $request): RedirectResponse
    {
        $attendanceLog = $this->resolveAttendanceLog($request, true);

        if (! $attendanceLog->trashed()) {
            return redirect(url('time-management/attendance-logs'))
                ->with('error', 'This punch is not deleted.');
        }

        $attendanceLog->status = 1;
        $attendanceLog->is_calculated = false;
        $attendanceLog->is_ignored = false;
        $attendanceLog->restore();

        return redirect(url('time-management/attendance-logs?deleted=1'))
            ->with('success', 'Punch restored successfully.');
    }

    private function resolveAttendanceLog(Request $request, bool $withTrashed = false): AttendanceLog
    {
        $attendanceLog = $request->route('attendance_log');

        if ($attendanceLog instanceof AttendanceLog) {
            return $attendanceLog;
        }

        $query = AttendanceLog::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($attendanceLog);
    }
}
