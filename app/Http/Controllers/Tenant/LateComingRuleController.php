<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLateComingRuleRequest;
use App\Http\Requests\Tenant\UpdateLateComingRuleRequest;
use App\Jobs\Tenant\GenerateLateComingRuleExport;
use App\Jobs\Tenant\ProcessLateComingRuleImport;
use App\Models\Tenant\LateComingRule;
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

class LateComingRuleController extends Controller
{
    public function index(): View
    {
        return view('time-management.late-coming-rules.index', [
            'lateComingRules' => LateComingRule::query()
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $lateComingRule = LateComingRule::query()->findOrFail($request->route('late_coming_rule'));

        return view('time-management.late-coming-rules.show', compact('lateComingRule'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = LateComingRule::query()->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $lateComingRules = $query->get();

        return response()->json([
            'html' => view('time-management.late-coming-rules.partials.rows', compact('lateComingRules'))->render(),
            'count' => $lateComingRules->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.late-coming-rules.add');
    }

    public function store(StoreLateComingRuleRequest $request): RedirectResponse
    {
        LateComingRule::query()->create($request->validated());

        return redirect(url('time-management/late-coming-rules'))
            ->with('success', 'Late coming rule created successfully.');
    }

    public function edit(Request $request): View
    {
        $lateComingRule = LateComingRule::query()->findOrFail($request->route('late_coming_rule'));

        return view('time-management.late-coming-rules.edit', compact('lateComingRule'));
    }

    public function update(UpdateLateComingRuleRequest $request): RedirectResponse
    {
        LateComingRule::query()
            ->findOrFail($request->route('late_coming_rule'))
            ->update($request->validated());

        return redirect(url('time-management/late-coming-rules'))
            ->with('success', 'Late coming rule updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $lateComingRule = LateComingRule::query()->findOrFail($request->route('late_coming_rule'));

        if ($lateComingRule->hasRelatedRecords()) {
            return redirect(url('time-management/late-coming-rules'))
                ->with('error', $lateComingRule->getRelatedRecordsMessage('Late Coming Rule'));
        }

        $lateComingRule->delete();

        return redirect(url('time-management/late-coming-rules'))
            ->with('success', 'Late coming rule deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'late_coming_rules_' . now()->format('Ymd_His') . '.csv';

        GenerateLateComingRuleExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/late-coming-rules'))
            ->with('success', 'Late coming rule export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, LateComingRule::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; must be unique.',
                'description' => 'Optional; explanatory text for the late coming rule.',
                'ignore_late_coming_minutes' => 'Required; whole number of minutes, zero or greater.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'late_coming_rules_import_sample.csv', [
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
            LateComingRule::IMPORT_EXPORT_COLUMNS,
        );

        if ($headerErrors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $headerErrors)]);
        }

        $storedPath = $file->storeAs(
            'late-coming-rule-imports',
            'late_coming_rule_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded late coming rule import file.');
        }

        ProcessLateComingRuleImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/late-coming-rules'))
            ->with('success', 'Late coming rule import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Alate_coming_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'late-coming-rule-exports/' . $fileName;
        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateLateComingRuleExport(Auth::guard('tenant')->id(), $fileName))->handle();
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
            'late_coming_rules.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
