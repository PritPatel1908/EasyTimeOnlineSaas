<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreDepartmentRequest;
use App\Http\Requests\Tenant\UpdateDepartmentRequest;
use App\Jobs\Tenant\GenerateDepartmentExport;
use App\Jobs\Tenant\ProcessDepartmentImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('company-structure.departments.index', [
            'departments' => Department::query()->latest('id')->paginate(15)->withQueryString(),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.departments.show', [
            'department' => Department::query()->findOrFail($request->route('department')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $status = $validated['status'] ?? 'all';

        $query = Department::query()->latest('id');

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $departments = $query->get();
        return response()->json([
            'html' => view('company-structure.departments.partials.rows', compact('departments'))->render(),
            'count' => $departments->count(),
        ]);
    }

    public function create(): View
    {
        return view('company-structure.departments.add', [
            'department' => null,
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::query()->create($request->validated());
        return redirect(url('company-structure/departments'))->with('success', 'Department created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.departments.edit', [
            'department' => Department::query()->findOrFail($request->route('department')),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDepartmentRequest $request): RedirectResponse
    {
        Department::query()->findOrFail($request->route('department'))->update($request->validated());
        return redirect(url('company-structure/departments'))->with('success', 'Department updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $department = Department::query()->findOrFail($request->route('department'));
        if ($department->hasRelatedRecords()) {
            return redirect(url('company-structure/departments'))->with('error', $department->getRelatedRecordsMessage('Department'));
        }
        $department->delete();
        return redirect(url('company-structure/departments'))->with('success', 'Department deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'departments_' . now()->format('Ymd_His') . '.csv';
        GenerateDepartmentExport::dispatch(Auth::guard('tenant')->id(), $fileName)->onConnection('database_tenant')->onQueue('export');
        return redirect(url('company-structure/departments'))->with('success', 'Department export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, Department::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'departments_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $storedPath = $file->storeAs('department-imports', 'department_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessDepartmentImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))->onConnection('database_tenant')->onQueue('import');
        return redirect(url('company-structure/departments'))->with('success', 'Department import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Adepartments_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'department-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateDepartmentExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'departments.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
