<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUnitRequest;
use App\Http\Requests\Tenant\UpdateUnitRequest;
use App\Jobs\Tenant\GenerateUnitExport;
use App\Jobs\Tenant\ProcessUnitImport;
use App\Models\Tenant\Location;
use App\Models\Tenant\Unit;
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

class UnitController extends Controller
{
    public function index(): View
    {
        return view('company-structure.units.index', [
            'units' => Unit::query()->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.units.show', [
            'unit' => Unit::query()->findOrFail($request->route('unit')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,1,0']]);
        $status = $validated['status'] ?? 'all';
        $query = Unit::query()->latest('id');
        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }
        $units = $query->get();

        return response()->json([
            'html' => view('company-structure.units.partials.rows', compact('units'))->render(),
            'count' => $units->count(),
        ]);
    }

    public function create(): View
    {
        return view('company-structure.units.add', [
            'unit' => null,
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::query()->create($request->validated());
        return redirect(url('company-structure/units'))->with('success', 'Unit created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.units.edit', [
            'unit' => Unit::query()->findOrFail($request->route('unit')),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUnitRequest $request): RedirectResponse
    {
        Unit::query()->findOrFail($request->route('unit'))->update($request->validated());
        return redirect(url('company-structure/units'))->with('success', 'Unit updated successfully.');
    }

    public function updateStatus(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', 'integer', 'in:1,0']]);
        $unit->update(['status' => $validated['status']]);
        return response()->json(['message' => 'Unit status updated successfully.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $unit = Unit::query()->findOrFail($request->route('unit'));
        if ($unit->hasRelatedRecords()) {
            return redirect(url('company-structure/units'))->with('error', $unit->getRelatedRecordsMessage('Unit'));
        }
        $unit->delete();
        return redirect(url('company-structure/units'))->with('success', 'Unit deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'units_' . Auth::guard('tenant')->id() . '_' . now()->format('Ymd_His') . '.csv';
        GenerateUnitExport::dispatch(Auth::guard('tenant')->id(), $fileName)->onConnection('database_tenant')->onQueue('tenant');
        return redirect(url('company-structure/units'))->with('success', 'Unit export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, Unit::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'units_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        $storedPath = $file->storeAs('unit-imports', 'unit_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessUnitImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))->onConnection('database_tenant')->onQueue('tenant');
        return redirect(url('company-structure/units'))->with('success', 'Unit import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aunits_' . Auth::guard('tenant')->id() . '_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'unit-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateUnitExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'units.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}