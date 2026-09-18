<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCanteenFacilityRequest;
use App\Http\Requests\Tenant\UpdateCanteenFacilityRequest;
use App\Jobs\Tenant\GenerateCanteenFacilityExport;
use App\Jobs\Tenant\ProcessCanteenFacilityImport;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CanteenFacilityController extends Controller
{
    private const INDEX_URL = 'company-structure/canteen-facilities';

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');
        $query = CanteenFacility::with(['location', 'rules'])->latest('id');
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        if (in_array((string) $status, ['0', '1'], true)) {
            $query->where('status', (int) $status);
        }

        return view('company-structure.canteen-facilities.index', [
            'canteenFacilities' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => (string) $status,
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.canteen-facilities.show', ['canteenFacility' => CanteenFacility::with(['location', 'rules'])->findOrFail($request->route('canteen_facility'))]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $status = $validated['status'] ?? 'all';
        $search = trim((string) ($validated['search'] ?? ''));
        $query = CanteenFacility::with(['location', 'rules'])->latest('id');
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        if ($status !== 'all') $query->where('status', (int) $status);
        $canteenFacilities = $query->get();
        return response()->json(['html' => view('company-structure.canteen-facilities.partials.rows', compact('canteenFacilities'))->render(), 'count' => $canteenFacilities->count()]);
    }

    public function create(): View
    {
        return view('company-structure.canteen-facilities.add', ['canteenFacility' => null, 'locations' => $this->locations()]);
    }

    public function store(StoreCanteenFacilityRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $this->assertLocationAccess((int) $payload['location_id']);
        DB::transaction(function () use ($payload): void {
            $facility = CanteenFacility::create($this->facilityPayload($payload));
            $facility->rules()->createMany($payload['rules']);
        });
        return redirect(url(self::INDEX_URL))->with('success', 'Canteen Facility created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.canteen-facilities.edit', ['canteenFacility' => CanteenFacility::with('rules')->findOrFail($request->route('canteen_facility')), 'locations' => $this->locations()]);
    }

    public function update(UpdateCanteenFacilityRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $this->assertLocationAccess((int) $payload['location_id']);
        $facility = CanteenFacility::query()->findOrFail($request->route('canteen_facility'));
        DB::transaction(function () use ($facility, $payload): void {
            $facility->update($this->facilityPayload($payload));
            $facility->rules()->delete();
            $facility->rules()->createMany($payload['rules']);
        });
        return redirect(url(self::INDEX_URL))->with('success', 'Canteen Facility updated successfully.');
    }

    public function updateStatus(Request $request, CanteenFacility $canteenFacility): JsonResponse
    {
        $status = $request->validate(['status' => ['required', 'integer', 'in:1,0']])['status'];
        $canteenFacility->update(['status' => $status]);
        return response()->json(['message' => 'Canteen Facility status updated successfully.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $facility = CanteenFacility::query()->findOrFail($request->route('canteen_facility'));
        if ($facility->hasRelatedRecords()) return redirect(url(self::INDEX_URL))->with('error', $facility->getRelatedRecordsMessage('Canteen Facility'));
        $facility->delete();
        return redirect(url(self::INDEX_URL))->with('success', 'Canteen Facility deleted successfully.');
    }

    public function export(Request $request): RedirectResponse
    {
        $fileName = 'canteen_facilities_' . now()->format('Ymd_His') . '.csv';
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        GenerateCanteenFacilityExport::dispatch(Auth::guard('tenant')->id(), $fileName, trim((string) ($filters['search'] ?? '')), $filters['status'] ?? 'all')->onConnection('database_tenant')->onQueue('export');
        return redirect(url(self::INDEX_URL))->with('success', 'Canteen Facility export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, CanteenFacility::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'canteen_facilities_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $validated = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'], 'update_duplicate_records' => ['nullable', 'boolean']]);
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $storedPath = $file->storeAs('canteen-facility-imports', 'canteen_facility_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessCanteenFacilityImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))->onConnection('database_tenant')->onQueue('import');
        return redirect(url(self::INDEX_URL))->with('success', 'Canteen Facility import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\\Acanteen_facilities_\\d{8}_\\d{6}\\.csv\\z/', $fileName) === 1, 404);
        $path = 'canteen-facility-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) (new GenerateCanteenFacilityExport(Auth::guard('tenant')->id(), $fileName))->handle();
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'canteen_facilities.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function locations()
    {
        return Location::query()->where('status', 1)->orderBy('name')->get();
    }

    private function assertLocationAccess(int $locationId): void
    {
        abort_unless(Location::query()->whereKey($locationId)->exists(), 422, 'The selected location is outside your permitted data-policy scope.');
    }

    private function facilityPayload(array $payload): array
    {
        return array_intersect_key($payload, array_flip(['name', 'code', 'total_cfa', 'status', 'location_id']));
    }
}
