<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreManualAttendanceRequest;
use App\Http\Requests\Tenant\UpdateManualAttendanceRequest;
use App\Jobs\Tenant\ApproveUserManualAttendance;
use App\Jobs\Tenant\GenerateManualAttendanceExport;
use App\Jobs\Tenant\ProcessManualAttendanceImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualAttendance;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualAttendanceController extends Controller
{
    private const INDEX_URL = 'time-management/manual-attendances';

    public function index(): View
    {
        return view('time-management.manual-attendances.index', [
            'attendances' => ManualAttendance::query()
                ->with(['Users', 'location', 'company', 'shift', 'shift_rotation'])
                ->latest('in_time')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.manual-attendances.add', $this->formOptions(null));
    }

    public function store(StoreManualAttendanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = array_map('intval', $validated['users']);
        unset($validated['users']);
        $validated = $this->preparePayload($validated);
        $validated['created_by'] = Auth::guard('tenant')->id();
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $attendance = DB::transaction(function () use ($validated, $userIds): ManualAttendance {
            $attendance = ManualAttendance::query()->create($validated);
            $attendance->Users()->sync($userIds);

            return $attendance->load('Users');
        });

        $this->dispatchAttendanceProcessing($attendance);

        return redirect(url(self::INDEX_URL))->with('success', 'Manual attendance created successfully.');
    }

    public function show(Request $request): View
    {
        $attendance = ManualAttendance::query()
            ->with(['Users', 'location', 'company', 'shift', 'shift_rotation'])
            ->findOrFail($request->route('manual_attendance'));

        return view('time-management.manual-attendances.show', ['attendance' => $attendance]);
    }

    public function edit(Request $request): View
    {
        $attendance = ManualAttendance::query()
            ->with('Users')
            ->findOrFail($request->route('manual_attendance'));

        return view('time-management.manual-attendances.edit', $this->formOptions($attendance));
    }

    public function update(UpdateManualAttendanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = array_map('intval', $validated['users']);
        unset($validated['users']);
        $validated = $this->preparePayload($validated);
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $attendance = ManualAttendance::query()->findOrFail($request->route('manual_attendance'));
        DB::transaction(function () use ($attendance, $validated, $userIds): void {
            $attendance->update($validated);
            $attendance->Users()->sync($userIds);
        });

        $this->dispatchAttendanceProcessing($attendance->load('Users'));

        return redirect(url(self::INDEX_URL))->with('success', 'Manual attendance updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $attendance = ManualAttendance::query()->findOrFail($request->route('manual_attendance'));

        DB::transaction(function () use ($attendance): void {
            $attendance->Users()->detach();
            $attendance->deleted_by = Auth::guard('tenant')->id();
            $attendance->save();
            $attendance->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Manual attendance deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'manual_attendances_' . now()->format('Ymd_His') . '.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateManualAttendanceExport::dispatch($userId === null ? null : (int) $userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual attendance export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ManualAttendance::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by semicolons.',
                'location' => 'Optional; one location name, code, or ID.',
                'company' => 'Optional; one company name, code, or ID.',
                'shift_type' => 'Optional; auto, fixed, or rotational.',
                'shift' => 'Optional; shift name, code, or ID.',
                'shift_rotation' => 'Optional; shift rotation code or ID.',
                'in_time' => 'Required; date and time, for example YYYY-MM-DD HH:MM:SS.',
                'out_time' => 'Required; date and time, for example YYYY-MM-DD HH:MM:SS.',
                'reason' => 'Optional; text.',
            ]);
            fclose($handle);
        }, 'manual_attendances_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'manual-attendance-imports',
            'manual_attendance_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded manual attendance import file.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessManualAttendanceImport::dispatch($userId === null ? null : (int) $userId, $storedPath)
            ->onConnection('database_tenant')
            ->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual attendance import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Amanual_attendances_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'manual-attendance-exports/' . $fileName;

        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateManualAttendanceExport($userId === null ? null : (int) $userId, $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'manual_attendances.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?ManualAttendance $attendance): array
    {
        return [
            'attendance' => $attendance,
            'users' => User::query()->orderBy('code')->get(['id', 'name', 'code']),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name', 'code']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'shifts' => Shift::query()->orderBy('name')->get(['id', 'name', 'code']),
            'shiftRotations' => ShiftRotation::query()->orderBy('code')->get(['id', 'code']),
        ];
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function preparePayload(array $validated): array
    {
        $validated['location_id'] = $validated['location_id'] ?: null;
        $validated['company_id'] = $validated['company_id'] ?: null;
        $validated['shift_type'] = $validated['shift_type'] ?: null;
        $validated['shift_id'] = $validated['shift_id'] ?: null;
        $validated['shift_rotation_id'] = $validated['shift_rotation_id'] ?: null;
        $validated['reason'] = $validated['reason'] ?? null;

        return $validated;
    }

    private function dispatchAttendanceProcessing(ManualAttendance $attendance): void
    {
        ApproveUserManualAttendance::dispatch($attendance)
            ->onConnection('database_tenant')
            ->onQueue('processing');
    }
}
