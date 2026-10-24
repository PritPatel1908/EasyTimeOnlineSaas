<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreShiftRotationRequest;
use App\Http\Requests\Tenant\UpdateShiftRotationRequest;
use App\Jobs\Tenant\GenerateShiftRotationExport;
use App\Jobs\Tenant\ProcessRotationMusters;
use App\Jobs\Tenant\ProcessShiftRotationImport;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShiftRotationController extends Controller
{
    public function index(): View
    {
        return view('time-management.shift-rotations.index', [
            'shiftRotations' => ShiftRotation::query()
                ->withCount('rotation_values')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $shiftRotation = ShiftRotation::query()
            ->with('rotation_values.shift')
            ->findOrFail($request->route('shift_rotation'));

        return view('time-management.shift-rotations.show', compact('shiftRotation'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = ShiftRotation::query()->withCount('rotation_values')->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $shiftRotations = $query->get();

        return response()->json([
            'html' => view('time-management.shift-rotations.partials.rows', compact('shiftRotations'))->render(),
            'count' => $shiftRotations->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.shift-rotations.add', $this->formOptions());
    }

    public function store(StoreShiftRotationRequest $request): RedirectResponse
    {
        $shiftRotation = DB::transaction(function () use ($request): ShiftRotation {
            $shiftRotation = ShiftRotation::query()->create($request->safe()->except('rotation_values'));
            $shiftRotation->rotation_values()->createMany($request->validated('rotation_values'));

            return $shiftRotation;
        });
        ProcessRotationMusters::dispatch($shiftRotation->id)->afterCommit();

        return redirect(url('time-management/shift-rotations'))
            ->with('success', 'Shift rotation created successfully.');
    }

    public function edit(Request $request): View
    {
        $shiftRotation = ShiftRotation::query()
            ->with('rotation_values')
            ->findOrFail($request->route('shift_rotation'));

        return view('time-management.shift-rotations.edit', array_merge(
            ['shiftRotation' => $shiftRotation],
            $this->formOptions(),
        ));
    }

    public function update(UpdateShiftRotationRequest $request): RedirectResponse
    {
        $shiftRotationId = DB::transaction(function () use ($request): int {
            $shiftRotation = ShiftRotation::query()->findOrFail($request->route('shift_rotation'));
            $shiftRotation->update($request->safe()->except('rotation_values'));
            $shiftRotation->rotation_values()->delete();
            $shiftRotation->rotation_values()->createMany($request->validated('rotation_values'));

            return $shiftRotation->id;
        });
        ProcessRotationMusters::dispatch($shiftRotationId)->afterCommit();

        return redirect(url('time-management/shift-rotations'))
            ->with('success', 'Shift rotation updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $shiftRotation = ShiftRotation::query()->findOrFail($request->route('shift_rotation'));

        $references = [
            ['users', 'shift_rotation_id'],
            ['shift_changes', 'shift_rotation_id'],
            ['shift_change_applications', 'shift_rotation_id'],
            ['rotation_musters', 'rotation_id'],
        ];

        foreach ($references as [$table, $column]) {
            if (DB::table($table)->where($column, $shiftRotation->id)->exists()) {
                return redirect(url('time-management/shift-rotations'))
                    ->with('error', 'This shift rotation cannot be deleted because it is still in use.');
            }
        }

        DB::transaction(function () use ($shiftRotation): void {
            $shiftRotation->rotation_values()->delete();
            $shiftRotation->delete();
        });

        return redirect(url('time-management/shift-rotations'))
            ->with('success', 'Shift rotation deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'shift_rotations_'.now()->format('Ymd_His').'.csv';

        GenerateShiftRotationExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/shift-rotations'))
            ->with('success', 'Shift rotation export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ShiftRotation::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; use a unique rotation code.',
                'start_date' => 'Required; date in YYYY-MM-DD format.',
                'end_date' => 'Required unless is_forever is Yes; date in YYYY-MM-DD format, on or after start_date.',
                'is_forever' => 'Required; Yes, No, 1, or 0. Yes clears end_date.',
                'skip_days_on_month_end' => 'Optional; Yes, No, 1, or 0; blank defaults to No.',
                'skip_days_on_year_end' => 'Optional; Yes, No, 1, or 0; blank defaults to No.',
                'shift_status' => 'Optional; Yes, No, 1, or 0; blank defaults to No.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
                'shift_code' => 'Optional; comma-separated shift codes for the rotation steps in order (for example SF1,SF2,SF3). Keep the list in one CSV cell.',
                'days' => 'Optional; comma-separated positive day counts in matching order (for example 10,10,8). Use one row per rotation.',
            ]);
            fclose($handle);
        }, 'shift_rotations_import_sample.csv', [
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
        $this->validateImportCsv($file, ShiftRotation::IMPORT_EXPORT_COLUMNS, 'shift_rotations_import_sample.csv');
        $storedPath = $file->storeAs(
            'shift-rotation-imports',
            'shift_rotation_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded shift rotation import file.');
        }

        ProcessShiftRotationImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/shift-rotations'))
            ->with('success', 'Shift rotation import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Ashift_rotations_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'shift-rotation-exports/'.$fileName;

        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateShiftRotationExport(Auth::guard('tenant')->id(), $fileName))->handle();
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
            'shift_rotations.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return ['shifts' => Shift::query()->orderBy('name')->get()];
    }
}
