<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAreaRequest;
use App\Http\Requests\Tenant\UpdateAreaRequest;
use App\Jobs\Tenant\GenerateAreaExport;
use App\Jobs\Tenant\ProcessAreaImport;
use App\Models\Tenant\Area;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AreaController extends Controller
{
    private const INDEX_URL = 'company-structure/areas';

    public function index(): View
    {
        return view('company-structure.areas.index', [
            'areas' => Area::query()->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.areas.show', [
            'area' => Area::query()->findOrFail($request->route('area')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $status = $request->validate(['status' => ['nullable', 'in:all,1,0']])['status'] ?? 'all';
        $query = Area::query()->latest('id');

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $areas = $query->get();

        return response()->json([
            'html' => view('company-structure.areas.partials.rows', compact('areas'))->render(),
            'count' => $areas->count(),
        ]);
    }

    public function create(): View
    {
        return view('company-structure.areas.add', [
            'area' => null,
            'companies' => Company::query()->orderBy('name')->get(),
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        Area::query()->create($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Area created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.areas.edit', [
            'area' => Area::query()->findOrFail($request->route('area')),
            'companies' => Company::query()->orderBy('name')->get(),
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAreaRequest $request): RedirectResponse
    {
        Area::query()->findOrFail($request->route('area'))->update($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Area updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $area = Area::query()->findOrFail($request->route('area'));

        if ($area->hasRelatedRecords()) {
            return redirect(url(self::INDEX_URL))
                ->with('error', $area->getRelatedRecordsMessage('Area'));
        }

        $area->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Area deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'areas_' . now()->format('Ymd_His') . '.csv';
        GenerateAreaExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Area export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            CsvImportTemplate::write($handle, Area::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; must be unique.',
                'name' => 'Required; text, maximum 255 characters.',
                'company' => 'Optional; comma-separated company ID, name, or code.',
                'location' => 'Optional; comma-separated location ID, name, or code.',
                'time_limit_in_min' => 'Optional; non-negative number of minutes; blank defaults to 0.',
                'dms_area_id' => 'Optional; non-negative numeric DMS area identifier.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'areas_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $this->validateImportCsv($file, Area::IMPORT_EXPORT_COLUMNS, 'areas_import_sample.csv');
        $storedPath = $file->storeAs(
            'area-imports',
            'area_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        ProcessAreaImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Area import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aareas_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $path = 'area-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateAreaExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'areas.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
