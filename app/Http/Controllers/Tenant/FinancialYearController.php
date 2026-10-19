<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreFinancialYearRequest;
use App\Http\Requests\Tenant\UpdateFinancialYearRequest;
use App\Jobs\Tenant\GenerateFinancialYearExport;
use App\Jobs\Tenant\ProcessFinancialYearImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialYearController extends Controller
{
    private const INDEX_URL = 'time-management/financial-years';

    public function index(): View
    {
        return view('time-management.financial-years.index', [
            'financialYears' => FinancialYear::query()
                ->latest('start_date')
                ->paginate(15)
                ->withQueryString(),
            'startedFinancialYearId' => $this->startedFinancialYearId(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('time-management.financial-years.show', [
            'financialYear' => FinancialYear::query()->findOrFail($request->route('financial_year')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);
        $query = FinancialYear::query()->latest('start_date');

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }

        $financialYears = $query->get();

        return response()->json([
            'html' => view('time-management.financial-years.partials.rows', [
                'financialYears' => $financialYears,
                'startedFinancialYearId' => $this->startedFinancialYearId(),
            ])->render(),
            'count' => $financialYears->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.financial-years.add', [
            'financialYear' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreFinancialYearRequest $request): RedirectResponse
    {
        FinancialYear::query()->create($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Financial Year created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('time-management.financial-years.edit', [
            'financialYear' => FinancialYear::query()->findOrFail($request->route('financial_year')),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateFinancialYearRequest $request): RedirectResponse
    {
        FinancialYear::query()
            ->findOrFail($request->route('financial_year'))
            ->update($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Financial Year updated successfully.');
    }

    public function updateFlags(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_started' => ['required', 'boolean'],
            'is_closed' => ['required', 'boolean'],
        ]);

        $financialYear = FinancialYear::query()->findOrFail($request->route('financial_year'));

        if (
            (bool) $validated['is_started']
            && FinancialYear::query()
                ->where('is_started', true)
                ->whereKeyNot($financialYear->getKey())
                ->exists()
        ) {
            return redirect(url(self::INDEX_URL))
                ->with('error', 'Another Financial Year is already started. Stop it before starting this one.');
        }

        $financialYear->update($validated);

        return redirect(url(self::INDEX_URL))->with('success', 'Financial Year actions updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $financialYear = FinancialYear::query()->findOrFail($request->route('financial_year'));

        if ($financialYear->hasRelatedRecords()) {
            return redirect(url(self::INDEX_URL))
                ->with('error', $financialYear->getRelatedRecordsMessage('Financial Year'));
        }

        $financialYear->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Financial Year deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'financial_years_' . now()->format('Ymd_His') . '.csv';
        GenerateFinancialYearExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Financial Year export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                abort(500, 'Unable to create the Financial Year import sample.');
            }

            CsvImportTemplate::write($handle, FinancialYear::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; unique text, maximum 50 characters.',
                'name' => 'Optional; text, maximum 255 characters.',
                'start_date' => 'Required; date in YYYY-MM-DD format.',
                'end_date' => 'Required; date in YYYY-MM-DD format, on or after start_date.',
                'location' => 'Optional; comma-separated location ID, name, or code.',
                'company' => 'Optional; comma-separated company ID, name, or code.',
                'is_started' => 'Optional; Yes or No; blank defaults to No.',
                'is_closed' => 'Optional; Yes or No; blank defaults to No.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'financial_years_import_sample.csv', [
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
        $this->validateImportCsv($file, FinancialYear::IMPORT_EXPORT_COLUMNS, 'financial_years_import_sample.csv');
        $storedPath = Storage::disk('local')->putFileAs(
            'financial-year-imports',
            $file,
            'financial_year_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the Financial Year import file.');
        }

        ProcessFinancialYearImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Financial Year import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Afinancial_years_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'financial-year-exports/' . $fileName;
        abort_unless(Storage::disk('local')->exists($filePath), 404);

        $user = Auth::guard('tenant')->user();

        if ($user !== null && method_exists($user, 'unreadNotifications')) {
            $user->unreadNotifications->each(function ($notification): void {
                if (data_get($notification->data, 'type') === 'export'
                    && data_get($notification->data, 'download_url')) {
                    $notification->markAsRead();
                }
            });
        }

        return response()->download(Storage::disk('local')->path($filePath), 'financial_years.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{locations: \Illuminate\Database\Eloquent\Collection, companies: \Illuminate\Database\Eloquent\Collection}
     */
    private function formOptions(): array
    {
        return [
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
        ];
    }

    private function startedFinancialYearId(): ?int
    {
        $startedFinancialYear = FinancialYear::query()
            ->where('is_started', true)
            ->first(['id']);

        return $startedFinancialYear?->id;
    }
}
