<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\GenerateApprovalFlowExport;
use App\Jobs\Tenant\ProcessApprovalFlowImport;
use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApprovalFlowController extends Controller
{
    private const INDEX_URL = 'employee-structure/approval-flows';

    private const EMPLOYEE_APPROVAL_MODULES = [
        'shift_change_approval_flow_id' => 'Shift Change',
        'week_off_change_approval_flow_id' => 'Week Off Change',
        'week_off_swap_approval_flow_id' => 'Week Off Swap',
        'manual_punch_approval_flow_id' => 'Manual Punch',
        'manual_attendance_approval_flow_id' => 'Manual Attendance',
        'leave_approval_flow_id' => 'Leave',
        'short_leave_approval_flow_id' => 'Short Leave',
        'coff_approval_flow_id' => 'COFF',
        'od_approval_flow_id' => 'OD',
    ];

    public function index(Request $request): View
    {
        $query = ApprovalFlow::query()
            ->with(['approval_flow_details.users'])
            ->withCount('approval_flow_details')
            ->latest('id');
        $status = (string) $request->query('status', 'all');

        if (in_array($status, ['0', '1'], true)) {
            $query->where('status', (int) $status);
        } else {
            $status = 'all';
        }

        return view('employee-structure.approval-flows.index', [
            'approvalFlows' => $query->paginate(15)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:all,1,0'],
        ]);
        $query = ApprovalFlow::query()
            ->with(['approval_flow_details.users'])
            ->withCount('approval_flow_details')
            ->latest('id');

        if ($validated['status'] !== 'all') {
            $query->where('status', (int) $validated['status']);
        }

        $approvalFlows = $query->get();

        return response()->json([
            'html' => view('employee-structure.approval-flows.partials.rows', compact('approvalFlows'))->render(),
            'count' => $approvalFlows->count(),
        ]);
    }

    public function create(): View
    {
        return view('employee-structure.approval-flows.form', [
            'approvalFlow' => null,
            'users' => $this->availableUsers(),
            'employees' => $this->availableEmployees(),
            'details' => [['level' => 1, 'users' => []]],
            'status' => 1,
            'approvalModules' => self::EMPLOYEE_APPROVAL_MODULES,
            'employeeAssignments' => array_fill_keys(array_keys(self::EMPLOYEE_APPROVAL_MODULES), []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFlow($request);

        DB::transaction(function () use ($validated): void {
            $flow = ApprovalFlow::query()->create([
                'approval_flow_code' => trim($validated['approval_flow_code']),
                'status' => (int) $validated['status'],
            ]);
            $this->syncDetails($flow, $validated['details'] ?? []);
            $this->syncEmployeeAssignments($flow, $validated['employee_assignments'] ?? []);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Approval flow created successfully.');
    }

    public function show(Request $request): View
    {
        $approvalFlow = ApprovalFlow::query()->findOrFail($request->route('approvalFlow'));
        $approvalFlow->load('approval_flow_details.users');

        return view('employee-structure.approval-flows.show', compact('approvalFlow'));
    }

    public function edit(Request $request): View
    {
        $approvalFlow = ApprovalFlow::query()->findOrFail($request->route('approvalFlow'));
        $approvalFlow->load('approval_flow_details.users');

        $details = $approvalFlow->approval_flow_details
            ->sortBy('level')
            ->map(fn($detail): array => [
                'level' => $detail->level,
                'users' => $detail->users->pluck('id')->all(),
            ])
            ->values()
            ->all();

        $employeeAssignments = array_fill_keys(array_keys(self::EMPLOYEE_APPROVAL_MODULES), []);
        $employees = User::query()
            ->where('user_type', 'employee')
            ->where(function ($query) use ($approvalFlow): void {
                foreach (array_keys(self::EMPLOYEE_APPROVAL_MODULES) as $moduleField) {
                    $query->orWhere($moduleField, $approvalFlow->id);
                }
            })
            ->get(['id', ...array_keys(self::EMPLOYEE_APPROVAL_MODULES)]);

        foreach ($employees as $employee) {
            foreach (array_keys(self::EMPLOYEE_APPROVAL_MODULES) as $moduleField) {
                if ((int) $employee->{$moduleField} === (int) $approvalFlow->id) {
                    $employeeAssignments[$moduleField][] = $employee->id;
                }
            }
        }

        return view('employee-structure.approval-flows.form', [
            'approvalFlow' => $approvalFlow,
            'status' => $approvalFlow->status,
            'users' => $this->availableUsers(),
            'employees' => $this->availableEmployees(),
            'details' => $details === [] ? [['level' => 1, 'users' => []]] : $details,
            'approvalModules' => self::EMPLOYEE_APPROVAL_MODULES,
            'employeeAssignments' => $employeeAssignments,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $approvalFlow = ApprovalFlow::query()->findOrFail($request->route('approvalFlow'));
        $validated = $this->validateFlow($request, $approvalFlow);

        DB::transaction(function () use ($validated, $approvalFlow): void {
            $approvalFlow->update([
                'approval_flow_code' => trim($validated['approval_flow_code']),
                'status' => (int) $validated['status'],
            ]);
            $this->syncDetails($approvalFlow, $validated['details'] ?? []);
            $this->syncEmployeeAssignments($approvalFlow, $validated['employee_assignments'] ?? []);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Approval flow updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $approvalFlow = ApprovalFlow::query()->findOrFail($request->route('approvalFlow'));
        $approvalFlow->load('approval_flow_details.users');

        if (
            $approvalFlow->users()->exists()
            || $approvalFlow->approval_statuses()->exists()
            || $approvalFlow->approval_flow_details->contains(fn($detail): bool => $detail->users->isNotEmpty())
        ) {
            return redirect(url(self::INDEX_URL))->with('error', 'This approval flow cannot be deleted because it is still in use.');
        }

        $approvalFlow->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Approval flow deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'approval_flows_' . now()->format('Ymd_His') . '.csv';
        GenerateApprovalFlowExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))->with('success', 'Approval flow export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                CsvImportTemplate::write($handle, ApprovalFlow::IMPORT_EXPORT_COLUMNS, [
                    'approval_flow_code' => 'Required; unique approval flow code.',
                    'level' => 'Required; positive integer. For multiple levels, separate values with commas.',
                    'users' => 'Approver email addresses or user_code values separated by | per level. For multiple levels, separate approver groups with commas in the same order.',
                ]);
                fclose($handle);
            }
        }, 'approval_flows_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('file');
        $errors = CsvImportTemplate::validateHeaders($file->getPathname(), ApprovalFlow::IMPORT_EXPORT_COLUMNS);
        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        $storedPath = $file->storeAs(
            'approval-flow-imports',
            'approval_flow_import_' . now()->format('Ymd_His') . '_' . uniqid('', true) . '.' . $file->getClientOriginalExtension(),
        );
        if ($storedPath === false) {
            throw new \RuntimeException('The approval flow import file could not be stored.');
        }

        ProcessApprovalFlowImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))->with('success', 'Approval flow import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aapproval_flows_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'approval-flow-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateApprovalFlowExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'approval_flows.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validateFlow(Request $request, ?ApprovalFlow $approvalFlow = null): array
    {
        $rules = [
            'approval_flow_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('approval_flows', 'approval_flow_code')->ignore($approvalFlow?->id),
            ],
            'status' => ['required', Rule::in(['0', '1'])],
            'details' => ['nullable', 'array'],
            'details.*.users' => ['required', 'array', 'min:1'],
            'details.*.users.*' => ['required', 'integer', 'exists:users,id'],
            'employee_assignments' => ['nullable', 'array'],
        ];

        foreach (array_keys(self::EMPLOYEE_APPROVAL_MODULES) as $moduleField) {
            $rules["employee_assignments.{$moduleField}"] = ['nullable', 'array'];
            $rules["employee_assignments.{$moduleField}.*"] = [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('user_type', 'employee'),
            ];
        }

        return $request->validate($rules);
    }

    private function syncDetails(ApprovalFlow $flow, array $details): void
    {
        $flow->approval_flow_details()->delete();

        foreach (array_values($details) as $index => $detail) {
            $flowDetail = $flow->approval_flow_details()->create(['level' => $index + 1]);
            $flowDetail->users()->sync(array_map('intval', $detail['users']));
        }
    }

    private function syncEmployeeAssignments(ApprovalFlow $flow, array $assignments): void
    {
        foreach (array_keys(self::EMPLOYEE_APPROVAL_MODULES) as $moduleField) {
            $employeeIds = array_values(array_unique(array_map(
                'intval',
                $assignments[$moduleField] ?? [],
            )));

            $employeesPreviouslyAssigned = User::query()
                ->where('user_type', 'employee')
                ->where($moduleField, $flow->id);

            if ($employeeIds !== []) {
                $employeesPreviouslyAssigned->whereNotIn('id', $employeeIds);
            }

            $employeesPreviouslyAssigned->update([$moduleField => null]);

            if ($employeeIds !== []) {
                User::query()
                    ->where('user_type', 'employee')
                    ->whereIn('id', $employeeIds)
                    ->update([$moduleField => $flow->id]);
            }
        }
    }

    private function availableUsers()
    {
        return User::query()->orderBy('name')->get(['id', 'name', 'email', 'code']);
    }

    private function availableEmployees()
    {
        return User::query()
            ->where('user_type', 'employee')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'code']);
    }
}
