<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreDesignationRequest;
use App\Http\Requests\Tenant\UpdateDesignationRequest;
use App\Jobs\Tenant\GenerateDesignationExport;
use App\Jobs\Tenant\ProcessDesignationImport;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Location;
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

class DesignationController extends Controller
{
    private const INDEX_URL = 'employee-structure/designations';

    public function index(): View
    {
        return view('employee-structure.designations.index', [
            'designations' => Designation::query()->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('employee-structure.designations.show', [
            'designation' => Designation::query()->findOrFail($request->route('designation')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,1,0']]);
        $query = Designation::query()->latest('id');
        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }
        $designations = $query->get();

        return response()->json([
            'html' => view('employee-structure.designations.partials.rows', compact('designations'))->render(),
            'count' => $designations->count(),
        ]);
    }

    public function create(): View
    {
        return view('employee-structure.designations.add', [
            'designation' => null,
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
            'categories' => Category::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        Designation::query()->create($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Designation created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('employee-structure.designations.edit', [
            'designation' => Designation::query()->findOrFail($request->route('designation')),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
            'categories' => Category::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDesignationRequest $request): RedirectResponse
    {
        $designation = Designation::query()->findOrFail($request->route('designation'));
        $designation->update($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Designation updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $designation = Designation::query()->findOrFail($request->route('designation'));
        if ($designation->hasRelatedRecords()) {
            return redirect(url(self::INDEX_URL))->with('error', $designation->getRelatedRecordsMessage('Designation'));
        }
        $designation->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Designation deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'designations_' . now()->format('Ymd_His') . '.csv';
        GenerateDesignationExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))->with('success', 'Designation export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, Designation::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'designations_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        $storedPath = $file->storeAs('designation-imports', 'designation_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessDesignationImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))
            ->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))->with('success', 'Designation import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Adesignations_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'designation-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateDesignationExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'designations.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
