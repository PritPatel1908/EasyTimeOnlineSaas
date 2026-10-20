<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreEarlyGoingRuleRequest;
use App\Http\Requests\Tenant\UpdateEarlyGoingRuleRequest;
use App\Jobs\Tenant\GenerateEarlyGoingRuleExport;
use App\Jobs\Tenant\ProcessEarlyGoingRuleImport;
use App\Models\Tenant\EarlyGoingRule;
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

class EarlyGoingRuleController extends Controller
{
    public function index(): View
    {
        return view('time-management.early-going-rules.index', [
            'earlyGoingRules' => EarlyGoingRule::query()
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $earlyGoingRule = EarlyGoingRule::query()->findOrFail($request->route('early_going_rule'));

        return view('time-management.early-going-rules.show', compact('earlyGoingRule'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = EarlyGoingRule::query()->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $earlyGoingRules = $query->get();

        return response()->json([
            'html' => view('time-management.early-going-rules.partials.rows', compact('earlyGoingRules'))->render(),
            'count' => $earlyGoingRules->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.early-going-rules.add');
    }

    public function store(StoreEarlyGoingRuleRequest $request): RedirectResponse
    {
        EarlyGoingRule::query()->create($request->validated());

        return redirect(url('time-management/early-going-rules'))
            ->with('success', 'Early going rule created successfully.');
    }

    public function edit(Request $request): View
    {
        $earlyGoingRule = EarlyGoingRule::query()->findOrFail($request->route('early_going_rule'));

        return view('time-management.early-going-rules.edit', compact('earlyGoingRule'));
    }

    public function update(UpdateEarlyGoingRuleRequest $request): RedirectResponse
    {
        EarlyGoingRule::query()
            ->findOrFail($request->route('early_going_rule'))
            ->update($request->validated());

        return redirect(url('time-management/early-going-rules'))
            ->with('success', 'Early going rule updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $earlyGoingRule = EarlyGoingRule::query()->findOrFail($request->route('early_going_rule'));

        if ($earlyGoingRule->hasRelatedRecords()) {
            return redirect(url('time-management/early-going-rules'))
                ->with('error', $earlyGoingRule->getRelatedRecordsMessage('Early Going Rule'));
        }

        $earlyGoingRule->delete();

        return redirect(url('time-management/early-going-rules'))
            ->with('success', 'Early going rule deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'early_going_rules_' . now()->format('Ymd_His') . '.csv';

        GenerateEarlyGoingRuleExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/early-going-rules'))
            ->with('success', 'Early going rule export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, EarlyGoingRule::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; must be unique.',
                'description' => 'Optional; explanatory text for the early going rule.',
                'ignore_early_going_minutes' => 'Required; whole number of minutes, zero or greater.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'early_going_rules_import_sample.csv', [
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
            EarlyGoingRule::IMPORT_EXPORT_COLUMNS,
        );

        if ($headerErrors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $headerErrors)]);
        }

        $storedPath = $file->storeAs(
            'early-going-rule-imports',
            'early_going_rule_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded early going rule import file.');
        }

        ProcessEarlyGoingRuleImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/early-going-rules'))
            ->with('success', 'Early going rule import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aearly_going_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'early-going-rule-exports/' . $fileName;
        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateEarlyGoingRuleExport(Auth::guard('tenant')->id(), $fileName))->handle();
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
            'early_going_rules.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
