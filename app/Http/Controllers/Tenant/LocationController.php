<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLocationRequest;
use App\Http\Requests\Tenant\UpdateLocationRequest;
use App\Jobs\Tenant\GenerateLocationExport;
use App\Jobs\Tenant\ProcessLocationImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('company-structure.locations.index', ['locations' => Location::query()->latest('id')->paginate(15)->withQueryString()]);
    }

    public function show(Request $request): View
    {
        $location = Location::query()->findOrFail($request->route('location'));
        $locationId = (int) $location->getKey();

        return view('company-structure.locations.show', [
            'location' => $location,
            'companies' => Company::query()
                ->orderBy('name')
                ->get()
                ->filter(fn(Company $company): bool => $this->containsId($company->getRawOriginal('location_id'), $locationId))
                ->values(),
            'departments' => Department::query()
                ->orderBy('name')
                ->get()
                ->filter(fn(Department $department): bool => $this->containsId($department->getRawOriginal('location_id'), $locationId))
                ->values(),
        ]);
    }

    private function containsId(mixed $value, int $id): bool
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        return in_array((string) $id, array_map('strval', (array) $value), true);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $status = $request->validate(['status' => ['nullable', 'in:all,1,0']])['status'] ?? 'all';
        $query = Location::query()->latest('id');
        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }
        $locations = $query->get();

        return response()->json(['html' => view('company-structure.locations.partials.rows', compact('locations'))->render(), 'count' => $locations->count()]);
    }

    public function export(): RedirectResponse
    {
        $fileName = 'locations_' . now()->format('Ymd_His') . '.csv';
        GenerateLocationExport::dispatch(Auth::guard('tenant')->id(), $fileName)->onConnection('database_tenant')->onQueue('export');

        return redirect(url('company-structure/locations'))->with('success', 'Location export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, Location::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'locations_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Alocations_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'location-exports/' . $fileName;

        if (! Storage::disk('local')->exists($path)) {
            (new GenerateLocationExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'locations.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user instanceof User && $user->can('create_location'), 403);

        $validated = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'], 'update_duplicate_records' => ['nullable', 'boolean']]);
        $file = $request->file('file');
        $storedPath = $file->storeAs('location-imports', 'location_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessLocationImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))->onConnection('database_tenant')->onQueue('import');

        return redirect(url('company-structure/locations'))->with('success', 'Location import has started. You will receive a notification when the import finishes.');
    }

    public function create(): View
    {
        return view('company-structure.locations.add');
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        Location::query()->create($request->validated());

        return redirect(url('company-structure/locations'))->with('success', 'Location created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.locations.edit', ['location' => Location::query()->findOrFail($request->route('location'))]);
    }

    public function update(UpdateLocationRequest $request): RedirectResponse
    {
        Location::query()->findOrFail($request->route('location'))->update($request->validated());

        return redirect(url('company-structure/locations'))->with('success', 'Location updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $location = Location::query()->findOrFail($request->route('location'));
        $location->delete();

        return redirect(url('company-structure/locations'))->with('success', 'Location deleted successfully.');
    }
}
