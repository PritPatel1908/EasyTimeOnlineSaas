<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeaveTypeRequest;
use App\Http\Requests\Tenant\UpdateLeaveTypeRequest;
use App\Jobs\Tenant\GenerateLeaveTypeExport;
use App\Jobs\Tenant\ProcessLeaveTypeImport;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        return view('employee-structure.leave-types.index', [
            'leaveTypes' => LeaveType::query()->with('location')->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('employee-structure.leave-types.show', [
            'leaveType' => LeaveType::query()
                ->with(['location', 'leave_groups'])
                ->withCount('leave_applications')
                ->findOrFail($request->route('leave_type')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $status = $request->validate(['status' => ['nullable', 'in:all,1,0']])['status'] ?? 'all';
        $query = LeaveType::query()->with('location')->latest('id');

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $leaveTypes = $query->get();

        return response()->json([
            'html' => view('employee-structure.leave-types.partials.rows', compact('leaveTypes'))->render(),
            'count' => $leaveTypes->count(),
        ]);
    }

    public function export(): RedirectResponse
    {
        $fileName = 'leave_types_' . now()->format('Ymd_His') . '.csv';
        GenerateLeaveTypeExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('employee-structure/leave-types'))
            ->with('success', 'Leave Type export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                CsvImportTemplate::write($handle, LeaveType::IMPORT_EXPORT_COLUMNS, [
                    'code' => 'Required; text, maximum 255 characters; must be unique.',
                    'description' => 'Optional; text.',
                    'location' => 'Optional; enter an existing location code or name.',
                    'status' => 'Optional; Active or Inactive; blank defaults to Active.',
                ]);
                fclose($handle);
            }
        }, 'leave_types_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aleave_types_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'leave-type-exports/' . $fileName;

        if (! Storage::disk('local')->exists($path)) {
            (new GenerateLeaveTypeExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'leave_types.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user instanceof User && $user->can('create_leavetype'), 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);
        $file = $request->file('file');
        $this->validateImportCsv($file, LeaveType::IMPORT_EXPORT_COLUMNS, 'leave_types_import_sample.csv');
        $storedPath = $file->storeAs(
            'leave-type-imports',
            'leave_type_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded CSV file could not be saved. Please try again.'],
            ]);
        }

        ProcessLeaveTypeImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('employee-structure/leave-types'))
            ->with('success', 'Leave Type import has started. You will receive a notification when the import finishes.');
    }

    public function create(): View
    {
        return view('employee-structure.leave-types.add', [
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::query()->create($request->validated());

        return redirect(url('employee-structure/leave-types'))->with('success', 'Leave Type created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('employee-structure.leave-types.edit', [
            'leaveType' => LeaveType::query()->findOrFail($request->route('leave_type')),
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::query()->findOrFail($request->route('leave_type'))->update($request->validated());

        return redirect(url('employee-structure/leave-types'))->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        LeaveType::query()->findOrFail($request->route('leave_type'))->delete();

        return redirect(url('employee-structure/leave-types'))->with('success', 'Leave Type deleted successfully.');
    }
}
