<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreHalfDayRuleRequest;
use App\Http\Requests\Tenant\UpdateHalfDayRuleRequest;
use App\Jobs\Tenant\GenerateHalfDayRuleExport;
use App\Jobs\Tenant\ProcessHalfDayRuleImport;
use App\Models\Tenant\HalfDayRule;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HalfDayRuleController extends Controller
{
    public function index(): View
    {
        return view('time-management.half-day-rules.index', [
            'halfDayRules' => HalfDayRule::query()
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $halfDayRule = HalfDayRule::query()->findOrFail($request->route('half_day_rule'));

        return view('time-management.half-day-rules.show', compact('halfDayRule'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = HalfDayRule::query()->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $halfDayRules = $query->get();

        return response()->json([
            'html' => view('time-management.half-day-rules.partials.rows', compact('halfDayRules'))->render(),
            'count' => $halfDayRules->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.half-day-rules.add');
    }

    public function store(StoreHalfDayRuleRequest $request): RedirectResponse
    {
        HalfDayRule::query()->create($request->validated());

        return redirect(url('time-management/half-day-rules'))
            ->with('success', 'Half-day rule created successfully.');
    }

    public function edit(Request $request): View
    {
        $halfDayRule = HalfDayRule::query()->findOrFail($request->route('half_day_rule'));

        return view('time-management.half-day-rules.edit', compact('halfDayRule'));
    }

    public function update(UpdateHalfDayRuleRequest $request): RedirectResponse
    {
        HalfDayRule::query()
            ->findOrFail($request->route('half_day_rule'))
            ->update($request->validated());

        return redirect(url('time-management/half-day-rules'))
            ->with('success', 'Half-day rule updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $halfDayRule = HalfDayRule::query()->findOrFail($request->route('half_day_rule'));

        if ($halfDayRule->hasRelatedRecords()) {
            return redirect(url('time-management/half-day-rules'))
                ->with('error', $halfDayRule->getRelatedRecordsMessage('Half-day Rule'));
        }

        $halfDayRule->delete();

        return redirect(url('time-management/half-day-rules'))
            ->with('success', 'Half-day rule deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'half_day_rules_'.now()->format('Ymd_His').'.csv';

        GenerateHalfDayRuleExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/half-day-rules'))
            ->with('success', 'Half-day rule export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, HalfDayRule::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; must be unique.',
                'description' => 'Optional; explanatory text for this half-day rule.',
                'single_punch_allowed' => 'Optional; true/false, yes/no, or 1/0; blank defaults to false.',
                'work_hr_less_than_minutes' => 'Optional; whole number of minutes, zero or greater.',
                'late_coming_minutes' => 'Optional; whole number of minutes, zero or greater.',
                'no_of_late' => 'Optional; numeric value, zero or greater.',
                'consecutive_late_coming' => 'Optional; numeric value, zero or greater.',
                'ignore_month_end_late' => 'Optional; true/false, yes/no, or 1/0; blank defaults to false.',
                'early_going_minutes' => 'Optional; whole number of minutes, zero or greater.',
                'no_of_early' => 'Optional; numeric value, zero or greater.',
                'consecutive_early_going' => 'Optional; numeric value, zero or greater.',
                'ignore_month_end_early' => 'Optional; true/false, yes/no, or 1/0; blank defaults to false.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'half_day_rules_import_sample.csv', [
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
        $headerErrors = CsvImportTemplate::validateHeaders(
            $file->getRealPath(),
            HalfDayRule::IMPORT_EXPORT_COLUMNS,
        );

        if ($headerErrors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $headerErrors)]);
        }

        $storedPath = $file->storeAs(
            'half-day-rule-imports',
            'half_day_rule_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded half-day rule import file.');
        }

        ProcessHalfDayRuleImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/half-day-rules'))
            ->with('success', 'Half-day rule import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Ahalf_day_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'half-day-rule-exports/'.$fileName;
        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateHalfDayRuleExport(Auth::guard('tenant')->id(), $fileName))->handle();
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
            'half_day_rules.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
