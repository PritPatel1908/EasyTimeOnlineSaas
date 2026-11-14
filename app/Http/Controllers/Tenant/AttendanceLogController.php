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
use App\Models\Tenant\ManualAttendance;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceLogController extends Controller
{
    public function timeCard(Request $request): View
    {
        return view('time-management.time-card.index', $this->timeCardData($request));
    }

    public function filterTimeCard(Request $request): JsonResponse
    {
        if ($request->filled('punch_attendance_id')) {
            $filters = $request->validate([
                'year' => ['required', 'integer', 'min:1900', 'max:2200'],
                'punch_attendance_id' => ['required', 'integer', 'min:1'],
            ]);

            return response()->json([
                'html' => $this->timeCardPunchesHtml(
                    (int) $filters['year'],
                    (int) $filters['punch_attendance_id'],
                ),
            ]);
        }

        return response()->json([
            'html' => view('time-management.time-card.partials.records', $this->timeCardData($request))->render(),
        ]);
    }

    private function timeCardPunchesHtml(int $year, int $attendanceId): string
    {
        $guard = Auth::guard('tenant');
        $user = $guard->user();
        $isSuperAdmin = $guard->id() == 1 || $user?->hasRole('super_admin');
        $attendance = Attendance::year($year)->findOrFail($attendanceId);

        abort_unless($isSuperAdmin || (int) $attendance->user_id === (int) $guard->id(), 404);

        $punches = collect();
        $attendanceRemarks = $attendance->remarks;
        if ($attendance->in_time !== null && $attendance->out_time !== null) {
            $userCode = User::query()
                ->withoutGlobalScopes()
                ->whereKey($attendance->user_id)
                ->value('code');
            $punches = AttendanceLog::query()
                ->where(function ($query) use ($attendance, $userCode): void {
                    $query->where('user_id', $attendance->user_id);
                    if ($userCode) {
                        $query->orWhere('user_code', $userCode);
                    }
                })
                ->whereBetween('datetime', [$attendance->in_time, $attendance->out_time])
                ->orderBy('datetime')
                ->orderBy('id')
                ->get();
        }

        $machineIds = $punches->pluck('dms_device_id')->filter()->unique()->values();
        $machines = $machineIds->isEmpty()
            ? collect()
            : DB::table('machines')
                ->whereIn('dms_device_id', $machineIds)
                ->get(['dms_device_id', 'name', 'machine_serial_number'])
                ->keyBy('dms_device_id');
        $areaIds = $punches->pluck('dms_area_id')->filter()->unique()->values();
        $areas = $areaIds->isEmpty()
            ? collect()
            : DB::table('areas')
                ->whereIn('dms_area_id', $areaIds)
                ->get(['dms_area_id', 'name'])
                ->keyBy('dms_area_id');

        $referenceSources = [
            ManualPunch::class => ['label' => 'Manual Punch', 'model' => ManualPunch::class],
            ManualPunchApplication::class => ['label' => 'Manual Punch Application', 'model' => ManualPunchApplication::class],
            'manual_attendance_start' => ['label' => 'Manual Attendance', 'model' => ManualAttendance::class],
            'manual_attendance_end' => ['label' => 'Manual Attendance', 'model' => ManualAttendance::class],
            'manual_attendance_application_start' => ['label' => 'Manual Attendance Application', 'model' => ManualAttendanceApplication::class],
            'manual_attendance_application_end' => ['label' => 'Manual Attendance Application', 'model' => ManualAttendanceApplication::class],
        ];
        $referenceReasons = collect();
        foreach ($punches->groupBy('referance_type') as $referenceType => $relatedPunches) {
            $source = $referenceSources[$referenceType] ?? null;
            if ($source === null) {
                continue;
            }

            $reasons = $source['model']::query()
                ->whereIn('id', $relatedPunches->pluck('referance_id')->filter()->unique())
                ->pluck('reason', 'id');
            foreach ($reasons as $id => $reason) {
                $referenceReasons->put($referenceType.':'.$id, $reason);
            }
        }

        return view('time-management.time-card.partials.punches', [
            'punches' => $punches,
            'attendanceRemarks' => $attendanceRemarks,
            'machines' => $machines,
            'areas' => $areas,
            'referenceSources' => $referenceSources,
            'referenceReasons' => $referenceReasons,
        ])->render();
    }

    private function timeCardData(Request $request): array
    {
        $guard = Auth::guard('tenant');
        $user = $guard->user();
        $isSuperAdmin = $guard->id() == 1 || $user?->hasRole('super_admin');
        $currentYear = (int) now()->format('Y');
        $currentMonth = (int) now()->format('n');
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
        $defaultMonthStart = Carbon::create($year, $currentMonth, 1)->startOfMonth()->toDateString();
        $defaultMonthEnd = Carbon::create($year, $currentMonth, 1)->endOfMonth()->toDateString();

        if (! in_array($year, $years, true)) {
            throw ValidationException::withMessages([
                'year' => 'Select a year available in the year list.',
            ]);
        }

        $startDate = $filters['start_date'] ?? $defaultMonthStart;
        $endDate = $filters['end_date'] ?? $defaultMonthEnd;

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

        $employeeId = $employee?->id;
        if ($employeeId === null && ! $isSuperAdmin) {
            $employeeId = $guard->id();
        }

        $query = Attendance::year($year)
            ->with(['user', 'shift', 'status_master'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employeeId !== null) {
            $query->where('user_id', $employeeId);
        }

        return [
            'attendanceRecords' => $query
                ->orderByDesc('date')
                ->latest('id')
                ->get(),
            'employees' => User::query()
                ->where('user_type', 'employee')
                ->orderBy('code')
                ->get(['id', 'name', 'code']),
            'isSuperAdmin' => $isSuperAdmin,
            'years' => $years,
            'year' => $year,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeId' => (string) ($employeeId ?? ''),
        ];
    }

    public function index(Request $request): View
    {
        $request->validate([
            'deleted' => ['nullable', 'boolean'],
        ]);
        $showDeleted = $request->boolean('deleted');

        $query = AttendanceLog::query()->with('user');

        if ($showDeleted) {
            $query->onlyTrashed();
        }

        return view('time-management.attendance-logs.index', [
            'attendanceLogs' => $query
                ->latest('datetime')
                ->latest('id')
                ->get(),
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

    public function show(Request $request): View
    {
        $attendanceLog = $this->resolveAttendanceLog($request, true);
        $attendanceLog->load(['user', 'machine', 'area', 'process_tags']);

        return view('time-management.attendance-logs.show', [
            'attendanceLog' => $attendanceLog,
        ]);
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
