<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSubDepartmentRequest;
use App\Http\Requests\Tenant\UpdateSubDepartmentRequest;
use App\Jobs\Tenant\GenerateSubDepartmentExport;
use App\Jobs\Tenant\ProcessSubDepartmentImport;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubDepartmentController extends Controller
{
    private const INDEX_URL = 'company-structure/sub-departments';

    public function index(): View
    {
        return view('company-structure.sub-departments.index', [
            'subDepartments' => SubDepartment::query()->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.sub-departments.show', [
            'subDepartment' => SubDepartment::query()->findOrFail($request->route('sub_department')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,1,0']]);
        $status = $validated['status'] ?? 'all';
        $query = SubDepartment::query()->latest('id');
        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }
        $subDepartments = $query->get();

        return response()->json([
            'html' => view('company-structure.sub-departments.partials.rows', compact('subDepartments'))->render(),
            'count' => $subDepartments->count(),
        ]);
    }

    public function create(): View
    {
        return view('company-structure.sub-departments.add', [
            'subDepartment' => null,
            'departments' => Department::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSubDepartmentRequest $request): RedirectResponse
    {
        SubDepartment::query()->create($request->validated());
        return redirect(url(self::INDEX_URL))->with('success', 'Sub Department created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.sub-departments.edit', [
            'subDepartment' => SubDepartment::query()->findOrFail($request->route('sub_department')),
            'departments' => Department::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSubDepartmentRequest $request): RedirectResponse
    {
        SubDepartment::query()->findOrFail($request->route('sub_department'))->update($request->validated());
        return redirect(url(self::INDEX_URL))->with('success', 'Sub Department updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $subDepartment = SubDepartment::query()->findOrFail($request->route('sub_department'));
        if ($subDepartment->hasRelatedRecords()) {
            return redirect(url(self::INDEX_URL))->with('error', $subDepartment->getRelatedRecordsMessage('Sub Department'));
        }
        $subDepartment->delete();
        return redirect(url(self::INDEX_URL))->with('success', 'Sub Department deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'sub_departments_' . now()->format('Ymd_His') . '.csv';
        GenerateSubDepartmentExport::dispatch(Auth::guard('tenant')->id(), $fileName)->onConnection('database_tenant')->onQueue('export');
        return redirect(url(self::INDEX_URL))->with('success', 'Sub Department export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, SubDepartment::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'sub_departments_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        $storedPath = $file->storeAs('sub-department-imports', 'sub_department_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessSubDepartmentImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))->onConnection('database_tenant')->onQueue('import');
        return redirect(url(self::INDEX_URL))->with('success', 'Sub Department import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Asub_departments_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'sub-department-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateSubDepartmentExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'sub_departments.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
