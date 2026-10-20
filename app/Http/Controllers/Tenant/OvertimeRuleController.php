<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreOvertimeRuleRequest;
use App\Http\Requests\Tenant\UpdateOvertimeRuleRequest;
use App\Jobs\Tenant\GenerateOvertimeRuleExport;
use App\Jobs\Tenant\ProcessOvertimeRuleImport;
use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OvertimeRuleController extends Controller
{
    public function index(): View
    {
        return view('time-management.overtime-rules.index', [
            'overtimeRules' => OvertimeRule::query()
                ->withCount('overtime_slabs')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $overtimeRule = OvertimeRule::query()
            ->with(['overtime_slabs' => fn ($query) => $query->orderBy('id')])
            ->findOrFail($request->route('overtime_rule'));

        return view('time-management.overtime-rules.show', compact('overtimeRule'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = OvertimeRule::query()->withCount('overtime_slabs')->latest('id');
        $status = $validated['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $overtimeRules = $query->get();

        return response()->json([
            'html' => view('time-management.overtime-rules.partials.rows', compact('overtimeRules'))->render(),
            'count' => $overtimeRules->count(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.overtime-rules.add');
    }

    public function store(StoreOvertimeRuleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slabs = $this->normalizeSlabs($validated['slabs'] ?? []);
        unset($validated['slabs']);

        DB::transaction(function () use ($validated, $slabs): void {
            $overtimeRule = OvertimeRule::query()->create($validated);
            $overtimeRule->overtime_slabs()->createMany($slabs);
        });

        return redirect(url('time-management/overtime-rules'))
            ->with('success', 'Overtime rule created successfully.');
    }

    public function edit(Request $request): View
    {
        $overtimeRule = OvertimeRule::query()
            ->with(['overtime_slabs' => fn ($query) => $query->orderBy('id')])
            ->findOrFail($request->route('overtime_rule'));

        return view('time-management.overtime-rules.edit', compact('overtimeRule'));
    }

    public function update(UpdateOvertimeRuleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slabs = $this->normalizeSlabs($validated['slabs'] ?? []);
        unset($validated['slabs']);

        DB::transaction(function () use ($request, $validated, $slabs): void {
            $overtimeRule = OvertimeRule::query()
                ->findOrFail($request->route('overtime_rule'));
            $overtimeRule->update($validated);
            $overtimeRule->overtime_slabs()->delete();
            $overtimeRule->overtime_slabs()->createMany($slabs);
        });

        return redirect(url('time-management/overtime-rules'))
            ->with('success', 'Overtime rule updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $overtimeRule = OvertimeRule::query()->findOrFail($request->route('overtime_rule'));

        if ($overtimeRule->users()->withTrashed()->exists()) {
            return redirect(url('time-management/overtime-rules'))
                ->with('error', 'This overtime rule cannot be deleted because it is assigned to one or more employees.');
        }

        DB::transaction(function () use ($overtimeRule): void {
            $overtimeRule->overtime_slabs()->delete();
            $overtimeRule->delete();
        });

        return redirect(url('time-management/overtime-rules'))
            ->with('success', 'Overtime rule deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'overtime_rules_'.now()->format('Ymd_His').'.csv';

        GenerateOvertimeRuleExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url('time-management/overtime-rules'))
            ->with('success', 'Overtime rule export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, OvertimeRule::IMPORT_EXPORT_COLUMNS, [
                'code' => 'Required; text, maximum 255 characters; must be unique.',
                'description' => 'Optional; explanatory text for this overtime rule.',
                'ignore_early_come_minutes' => 'Optional; early coming time in HH:MM format.',
                'ignore_late_come_minutes' => 'Optional; late coming time in HH:MM format.',
                'ignore_late_going' => 'Optional; true/false, yes/no, or 1/0; blank defaults to false.',
                'ignore_overtime' => 'Optional; overtime duration in HH:MM format, zero or greater; blank defaults to 00:00. Legacy decimal minutes are also accepted.',
                'ignore_late_going_overtime' => 'Optional; late-going overtime duration in HH:MM format, zero or greater; blank defaults to 00:00. Legacy decimal minutes are also accepted.',
                'status' => 'Optional; Active, Inactive, 1, or 0; blank defaults to Active.',
                'slab_value_from' => 'Optional; comma-separated slab start times in HH:MM format, in slab order.',
                'slab_value_to' => 'Optional; comma-separated slab end times in HH:MM format, in slab order.',
                'slab_head_value' => 'Optional; comma-separated slab head times in HH:MM format, in slab order.',
                'slab_decimal_value' => 'Optional; comma-separated decimal values from 0 to 999999.99, in slab order.',
            ]);
            fclose($handle);
        }, 'overtime_rules_import_sample.csv', [
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
            OvertimeRule::IMPORT_EXPORT_COLUMNS,
        );

        if ($headerErrors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $headerErrors)]);
        }

        $storedPath = $file->storeAs(
            'overtime-rule-imports',
            'overtime_rule_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded overtime rule import file.');
        }

        ProcessOvertimeRuleImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url('time-management/overtime-rules'))
            ->with('success', 'Overtime rule import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aovertime_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);

        $filePath = 'overtime-rule-exports/'.$fileName;
        if (! Storage::disk('local')->exists($filePath)) {
            (new GenerateOvertimeRuleExport(Auth::guard('tenant')->id(), $fileName))->handle();
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
            'overtime_rules.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @param array<int, array<string, mixed>> $slabs
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSlabs(array $slabs): array
    {
        return collect($slabs)
            ->filter(static fn (array $slab): bool => collect($slab)->contains(
                static fn (mixed $value): bool => $value !== null && trim((string) $value) !== '',
            ))
            ->map(static fn (array $slab): array => [
                'value_from' => blank($slab['value_from']) ? null : $slab['value_from'],
                'value_to' => blank($slab['value_to']) ? null : $slab['value_to'],
                'head_value' => blank($slab['head_value']) ? null : $slab['head_value'],
                'decimal_value' => blank($slab['decimal_value']) ? null : $slab['decimal_value'],
            ])
            ->values()
            ->all();
    }
}
