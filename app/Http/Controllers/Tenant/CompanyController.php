<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCompanyRequest;
use App\Http\Requests\Tenant\UpdateCompanyRequest;
use App\Jobs\Tenant\GenerateCompanyExport;
use App\Jobs\Tenant\ProcessCompanyImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::query()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.index', [
            'companies' => $companies,
            'locations' => $locations,
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.companies.show', [
            'company' => Company::query()->findOrFail($request->route('company')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = Company::query()
            ->latest('id');

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }

        $companies = $query->get();

        return response()->json([
            'html' => view('company-structure.companies.partials.rows', [
                'companies' => $companies,
            ])->render(),
            'count' => $companies->count(),
        ]);
    }

    public function export(): RedirectResponse
    {
        $fileName = 'companies_' . now()->format('Ymd_His') . '.csv';
        $userId = Auth::guard('tenant')->id();

        GenerateCompanyExport::dispatch($userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('tenant');

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, Company::IMPORT_EXPORT_COLUMNS);
            fclose($handle);
        }, 'companies_import_sample.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);

        // Direct path: mirrors exactly where GenerateCompanyExport writes the file.
        $filePath = 'company-exports/' . $fileName;

        \Illuminate\Support\Facades\Log::info('downloadExport called', [
            'file'       => $file,
            'fileName'   => $fileName,
            'filePath'   => $filePath,
            'diskRoot'   => Storage::disk('local')->path(''),
            'diskExists' => Storage::disk('local')->exists($filePath),
            'storagePath' => storage_path(),
            'tenant'     => tenant()?->id,
        ]);

        $downloadPath = Storage::disk('local')->exists($filePath)
            ? Storage::disk('local')->path($filePath)
            : $this->resolveExportFilePath($fileName);

        if (! is_string($downloadPath) || ! file_exists($downloadPath)) {
            abort_unless(
                preg_match('/\Acompanies_\d{8}_\d{6}\.csv\z/', $fileName) === 1,
                404,
            );

            (new GenerateCompanyExport(Auth::guard('tenant')->id(), $fileName))->handle();

            $downloadPath = Storage::disk('local')->exists($filePath)
                ? Storage::disk('local')->path($filePath)
                : $this->resolveExportFilePath($fileName);
        }

        \Illuminate\Support\Facades\Log::info('downloadExport path resolved', [
            'downloadPath' => $downloadPath,
            'fileExists'   => is_string($downloadPath) && file_exists($downloadPath),
        ]);

        abort_unless(is_string($downloadPath) && file_exists($downloadPath), 404);

        $user = Auth::guard('tenant')->user();

        if ($user !== null && method_exists($user, 'unreadNotifications')) {
            $user->unreadNotifications->each(function ($notification) {
                if (data_get($notification->data, 'type') === 'export' && data_get($notification->data, 'download_url')) {
                    $notification->markAsRead();
                }
            });
        }

        return response()->download($downloadPath, 'companies.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
        $storedPath = $file->storeAs(
            'company-imports',
            'company_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension()
        );

        ProcessCompanyImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('tenant');

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company import has started. You will receive a notification when the import finishes.');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::query()->create($request->validated());

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company created successfully.');
    }

    public function create(): View
    {
        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.add', [
            'locations' => $locations,
        ]);
    }

    public function edit(Request $request): View
    {
        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.edit', [
            'company' => Company::query()->findOrFail($request->route('company')),
            'locations' => $locations,
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        Company::query()->findOrFail($request->route('company'))->update($request->validated());

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $company = Company::query()->findOrFail($request->route('company'));

        if ($company->hasRelatedRecords()) {
            return redirect(url('company-structure/companies'))
                ->with('error', $company->getRelatedRecordsMessage('Company'));
        }

        $company->delete();

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company deleted successfully.');
    }

    private function resolveExportFilePath(string $fileName): ?string
    {
        $diskFiles = Storage::disk('local')->allFiles('company-exports');

        $candidate = collect($diskFiles)
            ->first(fn(string $path) => basename($path) === $fileName);

        if (is_string($candidate)) {
            return Storage::disk('local')->path($candidate);
        }

        $appRoot = storage_path('app');

        if (! is_dir($appRoot)) {
            return null;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $appRoot,
                \FilesystemIterator::SKIP_DOTS,
            )
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getFilename() === $fileName) {
                return $item->getPathname();
            }
        }

        return null;
    }

    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [];
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false || count($headerRow) === 0) {
            fclose($handle);

            return [];
        }

        $mappedHeaders = [];

        foreach ($headerRow as $index => $header) {
            $mappedHeaders[$index] = $this->normalizeHeader((string) $header);
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $normalizedRow = [];

            foreach ($mappedHeaders as $index => $header) {
                $normalizedRow[$header] = trim((string) ($row[$index] ?? ''));
            }

            $rows[] = $normalizedRow;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', trim($header));
        $normalized = strtolower(trim((string) $normalized, '_'));

        return match ($normalized) {
            'company_name', 'name' => 'name',
            'company_code', 'code' => 'code',
            'company_email', 'email' => 'email',
            'status' => 'status',
            'location', 'location_name' => 'location_name',
            'location_code' => 'location_code',
            'location_id', 'company_location_id' => 'location_id',
            default => $normalized,
        };
    }

    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(fn($value) => trim((string) $value) === '');
    }

    private function validateImportRow(array $row): array
    {
        $errors = [];

        if (trim((string) ($row['name'] ?? '')) === '') {
            $errors[] = 'name is required';
        }

        if (trim((string) ($row['code'] ?? '')) === '') {
            $errors[] = 'code is required';
        }

        $status = $this->normalizeStatus((string) ($row['status'] ?? '1'));

        if ($status === null) {
            $errors[] = 'status must be Active, Inactive, 1 or 0';
        }

        $location = $this->resolveLocation($row);

        if (isset($row['location_name'], $row['location_code']) && $row['location_name'] !== '' && $row['location_code'] !== '' && $location === null) {
            $errors[] = 'location could not be resolved from the provided location name/code';
        }

        if (isset($row['location_id']) && trim((string) $row['location_id']) !== '' && $location === null && ! is_numeric($row['location_id'])) {
            $errors[] = 'location_id must be numeric';
        }

        return $errors;
    }

    private function findExistingCompany(array $row): ?Company
    {
        $uniqueFields = Company::getImportUniqueFields();

        foreach ($uniqueFields as $field) {
            $value = trim((string) ($row[$field] ?? ''));

            if ($value === '') {
                continue;
            }

            $company = Company::query()->where($field, $value)->first();

            if ($company !== null) {
                return $company;
            }
        }

        $name = trim((string) ($row['name'] ?? ''));

        if ($name !== '') {
            return Company::query()->where('name', $name)->first();
        }

        return null;
    }

    private function buildCompanyPayload(array $row): array
    {
        $location = $this->resolveLocation($row);
        $locationId = $location?->id ?? (is_numeric($row['location_id'] ?? '') ? (int) $row['location_id'] : null);

        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'code' => trim((string) ($row['code'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')) !== '' ? trim((string) $row['email']) : null,
            'status' => $this->normalizeStatus((string) ($row['status'] ?? '1')),
            'location_id' => $locationId !== null ? [$locationId] : null,
        ];
    }

    private function resolveLocation(array $row): ?Location
    {
        $locationId = trim((string) ($row['location_id'] ?? ''));

        if ($locationId !== '') {
            if (is_numeric($locationId)) {
                return Location::query()->find((int) $locationId);
            }

            return null;
        }

        $locationName = trim((string) ($row['location_name'] ?? ''));

        if ($locationName !== '') {
            return Location::query()->where('name', $locationName)->first();
        }

        $locationCode = trim((string) ($row['location_code'] ?? ''));

        if ($locationCode !== '') {
            return Location::query()->where('code', $locationCode)->first();
        }

        return null;
    }

    private function normalizeStatus(string $status): ?int
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'active', '1', 'true', 'yes' => 1,
            'inactive', '0', 'false', 'no' => 0,
            default => null,
        };
    }

    private function normalizeStatusForExport(int|string|null $status): string
    {
        return $status === 1 || strtolower((string) $status) === 'active' ? 'Active' : 'Inactive';
    }
}
