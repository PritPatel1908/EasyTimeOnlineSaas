<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreManualLeaveBalanceRequest;
use App\Http\Requests\Tenant\UpdateManualLeaveBalanceRequest;
use App\Jobs\Tenant\GenerateManualLeaveBalanceExport;
use App\Jobs\Tenant\ProcessManualLeaveBalanceImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualLeaveBalance;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use App\Support\TenantPermissions;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualLeaveBalanceController extends Controller
{
    private const INDEX_URL = 'employee-structure/manual-leave-balances';

    public function index(): View
    {
        return view('employee-structure.manual-leave-balances.index', [
            'balances' => ManualLeaveBalance::query()
                ->with(['Users', 'leave_type'])
                ->latest('date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'locationsById' => Location::query()->get(['id', 'name'])->keyBy('id'),
            'companiesById' => Company::query()->get(['id', 'name'])->keyBy('id'),
        ]);
    }

    public function show(Request $request): View
    {
        $balance = ManualLeaveBalance::query()
            ->with(['Users', 'leave_type'])
            ->findOrFail($request->route('manual_leave_balance'));

        return view('employee-structure.manual-leave-balances.show', [
            'balance' => $balance,
            'locations' => Location::query()->whereIn('id', (array) $balance->location_id)->orderBy('name')->get(),
            'companies' => Company::query()->whereIn('id', (array) $balance->company_id)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('employee-structure.manual-leave-balances.add', $this->formOptions(null));
    }

    public function store(StoreManualLeaveBalanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $validated['users'];
        $locationIds = $validated['location_ids'] ?? [];
        $companyIds = $validated['company_ids'] ?? [];
        unset($validated['users'], $validated['location_ids'], $validated['company_ids']);

        $validated['location_id'] = array_values(array_map('intval', $locationIds));
        $validated['company_id'] = array_values(array_map('intval', $companyIds));
        $validated['date'] = Carbon::parse($validated['date'])->format('Y-m-d H:i:s');
        $validated['created_by'] = Auth::guard('tenant')->id();
        $validated['updated_by'] = Auth::guard('tenant')->id();

        DB::transaction(function () use ($validated, $userIds): void {
            $balance = ManualLeaveBalance::query()->create($validated);
            $balance->Users()->sync($userIds);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Manual leave balance created successfully.');
    }

    public function edit(Request $request): View
    {
        $balance = ManualLeaveBalance::query()
            ->with('Users')
            ->findOrFail($request->route('manual_leave_balance'));

        return view('employee-structure.manual-leave-balances.edit', $this->formOptions($balance));
    }

    public function update(UpdateManualLeaveBalanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $validated['users'];
        $locationIds = $validated['location_ids'] ?? [];
        $companyIds = $validated['company_ids'] ?? [];
        unset($validated['users'], $validated['location_ids'], $validated['company_ids']);

        $validated['location_id'] = array_values(array_map('intval', $locationIds));
        $validated['company_id'] = array_values(array_map('intval', $companyIds));
        $validated['date'] = Carbon::parse($validated['date'])->format('Y-m-d H:i:s');
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $balance = ManualLeaveBalance::query()->findOrFail($request->route('manual_leave_balance'));
        DB::transaction(function () use ($balance, $validated, $userIds): void {
            $balance->update($validated);
            $balance->Users()->sync($userIds);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Manual leave balance updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $balance = ManualLeaveBalance::query()->findOrFail($request->route('manual_leave_balance'));
        $balance->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Manual leave balance deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'manual_leave_balances_' . now()->format('Ymd_His') . '.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateManualLeaveBalanceExport::dispatch($userId === null ? null : (int) $userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual leave balance export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                CsvImportTemplate::write($handle, ManualLeaveBalance::IMPORT_EXPORT_COLUMNS, [
                    'employee_codes' => 'Required; one or more employee codes separated by semicolons.',
                    'location' => 'Optional; one or more location names or codes separated by semicolons.',
                    'company' => 'Optional; one or more company names or codes separated by semicolons.',
                    'leave_type_code' => 'Required; an existing leave type code.',
                    'balance' => 'Required; decimal leave balance.',
                    'date' => 'Required; date and time in YYYY-MM-DD HH:MM format.',
                    'reason' => 'Optional; explanation for this manual adjustment.',
                ]);
                fclose($handle);
            }
        }, 'manual_leave_balances_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Amanual_leave_balances_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'manual-leave-balance-exports/' . $fileName;

        if (! Storage::disk('local')->exists($path)) {
            (new GenerateManualLeaveBalanceExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'manual_leave_balances.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function import(Request $request): RedirectResponse
    {
        TenantPermissions::authorize('ManualLeaveBalance', 'create');

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $this->validateImportCsv($file, ManualLeaveBalance::IMPORT_EXPORT_COLUMNS, 'manual_leave_balances_import_sample.csv');
        $storedPath = $file->storeAs(
            'manual-leave-balance-imports',
            'manual_leave_balances_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        ProcessManualLeaveBalanceImport::dispatch(
            Auth::guard('tenant')->id() === null ? null : (int) Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual leave balance import has started. You will receive a notification when the import finishes.');
    }

    private function formOptions(?ManualLeaveBalance $balance): array
    {
        return [
            'balance' => $balance,
            'users' => User::query()->orderBy('code')->get(['id', 'name', 'code', 'location_id', 'company_id']),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name', 'code']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'leaveTypes' => LeaveType::query()->orderBy('description')->get(['id', 'code', 'description']),
        ];
    }
}
