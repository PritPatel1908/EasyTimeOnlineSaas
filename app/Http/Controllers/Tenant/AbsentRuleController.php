<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAbsentRuleRequest;
use App\Http\Requests\Tenant\UpdateAbsentRuleRequest;
use App\Jobs\Tenant\GenerateAbsentRuleExport;
use App\Jobs\Tenant\ProcessAbsentRuleImport;
use App\Models\Tenant\AbsentRule;
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

class AbsentRuleController extends Controller
{
    public function index(): View
    {
        return view('time-management.absent-rules.index', [
            'absentRules' => AbsentRule::query()
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $absentRule = AbsentRule::query()->findOrFail($request->route('absent_rule'));

        return view('time-management.absent-rules.show', compact('absentRule'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = AbsentRule::query()->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $absentRules = $query->get();

        return response()->json([
            'html' => view('time-management.absent-rules.partials.rows', compact('absentRules'))->render(),
            'count' => $absentRules->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.absent-rules.add');
    }

    public function store(StoreAbsentRuleRequest $request): RedirectResponse
    {
        AbsentRule::query()->create($request->validated());

        return redirect(url('time-management/absent-rules'))
            ->with('success', 'Absent rule created successfully.');
    }

    public function edit(Request $request): View
    {
        $absentRule = AbsentRule::query()->findOrFail($request->route('absent_rule'));

        return view('time-management.absent-rules.edit', compact('absentRule'));
    }

    public function update(UpdateAbsentRuleRequest $request): RedirectResponse
    {
        AbsentRule::query()
            ->findOrFail($request->route('absent_rule'))
            ->update($request->validated());

        return redirect(url('time-management/absent-rules'))
            ->with('success', 'Absent rule updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $absentRule = AbsentRule::query()->findOrFail($request->route('absent_rule'));

        if ($absentRule->hasRelatedRecords()) {
            return redirect(url('time-management/absent-rules'))
                ->with('error', $absentRule->getRelatedRecordsMessage('Absent Rule'));
        }

        $absentRule->delete();

        return redirect(url('time-management/absent-rules'))
            ->with('success', 'Absent rule deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'absent_rules_'.now()->format('Ymd_His').'.csv';

        GenerateAbsentRuleExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/absent-rules'))
            ->with('success', 'Absent rule export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, AbsentRule::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; must be unique.',
                'description' => 'Optional; explanatory text for this absent rule.',
                'work_hr_less_than_minutes' => 'Required; whole number of minutes, zero or greater.',
                'allow_single_punch' => 'Optional; true/false, yes/no, or 1/0; blank defaults to false.',
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
        }, 'absent_rules_import_sample.csv', [
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
            AbsentRule::IMPORT_EXPORT_COLUMNS,
        );

        if ($headerErrors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $headerErrors)]);
        }

        $storedPath = $file->storeAs(
            'absent-rule-imports',
            'absent_rule_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded absent rule import file.');
        }

        ProcessAbsentRuleImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/absent-rules'))
            ->with('success', 'Absent rule import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aabsent_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'absent-rule-exports/'.$fileName;
        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateAbsentRuleExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($filePath), 404);

        $user = Auth::guard('tenant')->user();
        if ($user !== null && method_exists($user, 'unreadNotifications')) {
            $user->unreadNotifications->each(function ($notification) use ($fileName): void {
                if (data_get($notification->data, 'type') === 'export'
                    && data_get($notification->data, 'download_url') !== null
                    && basename((string) parse_url(data_get($notification->data, 'download_url'), PHP_URL_PATH)) === $fileName) {
                    $notification->markAsRead();
                }
            });
        }

        return response()->download(
            Storage::disk('local')->path($filePath),
            'absent_rules.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
