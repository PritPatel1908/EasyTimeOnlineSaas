<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreShiftRequest;
use App\Http\Requests\Tenant\UpdateShiftRequest;
use App\Jobs\Tenant\GenerateShiftExport;
use App\Jobs\Tenant\ProcessShiftImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\Shift;
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
use RuntimeException;

class ShiftController extends Controller
{
    public function index(): View
    {
        return view('time-management.shifts.index', [
            'shifts' => Shift::query()
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $shift = Shift::query()->findOrFail($request->route('shift'));
        $locations = Location::query()->whereIn('id', $this->idsFrom($shift->location_id))->orderBy('name')->get();
        $companies = Company::query()->whereIn('id', $this->idsFrom($shift->company_id))->orderBy('name')->get();

        return view('time-management.shifts.show', compact('shift', 'locations', 'companies'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = Shift::query()->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $shifts = $query->get();

        return response()->json([
            'html' => view('time-management.shifts.partials.rows', compact('shifts'))->render(),
            'count' => $shifts->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.shifts.add', $this->formOptions());
    }

    public function store(StoreShiftRequest $request): RedirectResponse
    {
        Shift::query()->create($request->validated());

        return redirect(url('time-management/shifts'))
            ->with('success', 'Shift created successfully.');
    }

    public function edit(Request $request): View
    {
        $shift = Shift::query()->findOrFail($request->route('shift'));

        return view('time-management.shifts.edit', array_merge(
            ['shift' => $shift],
            $this->formOptions(),
        ));
    }

    public function update(UpdateShiftRequest $request): RedirectResponse
    {
        Shift::query()->findOrFail($request->route('shift'))->update($request->validated());

        return redirect(url('time-management/shifts'))
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $shift = Shift::query()->findOrFail($request->route('shift'));

        if ($shift->hasRelatedRecords()) {
            return redirect(url('time-management/shifts'))
                ->with('error', $shift->getRelatedRecordsMessage('Shift'));
        }

        $shift->delete();

        return redirect(url('time-management/shifts'))
            ->with('success', 'Shift deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'shifts_' . now()->format('Ymd_His') . '.csv';

        GenerateShiftExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/shifts'))
            ->with('success', 'Shift export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, Shift::IMPORT_EXPORT_COLUMNS, [
                'name' => 'Required; text, maximum 255 characters.',
                'code' => 'Required; text, maximum 50 characters; use a unique shift code.',
                'location' => 'Optional; comma-separated location ID, name, or code.',
                'company' => 'Optional; comma-separated company ID, name, or code.',
                'in_time' => 'Optional; shift start time in HH:MM or HH:MM:SS format.',
                'out_time' => 'Optional; shift end time in HH:MM or HH:MM:SS format.',
                'first_half_end_time' => 'Optional; first-half end time in HH:MM or HH:MM:SS format.',
                'second_half_start_time' => 'Optional; second-half start time in HH:MM or HH:MM:SS format.',
                'brack_start_time' => 'Optional; break start time in HH:MM or HH:MM:SS format.',
                'brack_end_time' => 'Optional; break end time in HH:MM or HH:MM:SS format.',
                'auto_from' => 'Optional; automatic attendance start time in HH:MM or HH:MM:SS format.',
                'auto_to' => 'Optional; automatic attendance end time in HH:MM or HH:MM:SS format.',
                'is_night_shift' => 'Optional; Yes, No, 1, or 0; blank defaults to No.',
                'set_cutoff' => 'Optional; Yes, No, 1, or 0; blank defaults to No.',
                'cutoff_time' => 'Required when set_cutoff is Yes; enter a duration from shift start in HH:MM or HH:MM:SS format (24:00 means 24 hours; maximum 838:59:59).',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'shifts_import_sample.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $this->validateImportCsv($file, Shift::IMPORT_EXPORT_COLUMNS, 'shifts_import_sample.csv');
        $storedPath = $file->storeAs(
            'shift-imports',
            'shift_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded shift import file.');
        }

        ProcessShiftImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/shifts'))
            ->with('success', 'Shift import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Ashifts_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'shift-exports/' . $fileName;

        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateShiftExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

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

        return response()->download(
            Storage::disk('local')->path($filePath),
            'shifts.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'locations' => Location::query()->orderBy('name')->get(),
            'companies' => Company::query()->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function idsFrom(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_values(array_filter(
            (array) $value,
            static fn(mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
        ));
    }
}
