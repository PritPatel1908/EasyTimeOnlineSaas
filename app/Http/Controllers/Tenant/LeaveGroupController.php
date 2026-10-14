<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeaveGroupRequest;
use App\Http\Requests\Tenant\UpdateLeaveGroupRequest;
use App\Jobs\Tenant\GenerateLeaveGroupExport;
use App\Jobs\Tenant\ProcessLeaveGroupImport;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveGroupController extends Controller
{
    private const INDEX_URL = 'employee-structure/leave-groups';

    public function index(): View
    {
        return view('employee-structure.leave-groups.index', [
            'leaveGroups' => LeaveGroup::query()
                ->with(['financial_year'])
                ->withCount([
                    'grade_wise_monthly_leave_details as monthly_rules_count',
                    'grade_wise_yearly_leave_details as yearly_rules_count',
                ])
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $leaveGroup = LeaveGroup::query()
            ->with([
                'financial_year',
                'grade_wise_monthly_leave_details.leave_type',
                'grade_wise_monthly_leave_details.sandwich_leave',
                'grade_wise_yearly_leave_details.leave_type',
                'grade_wise_yearly_leave_details.sandwich_leave',
            ])
            ->findOrFail($request->route('leave_group'));

        return view('employee-structure.leave-groups.show', compact('leaveGroup'));
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,1,0']]);
        $query = LeaveGroup::query()->with('financial_year')->withCount([
            'grade_wise_monthly_leave_details as monthly_rules_count',
            'grade_wise_yearly_leave_details as yearly_rules_count',
        ])->latest('id');

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }

        $leaveGroups = $query->get();

        return response()->json([
            'html' => view('employee-structure.leave-groups.partials.rows', compact('leaveGroups'))->render(),
            'count' => $leaveGroups->count(),
        ]);
    }

    public function create(): View
    {
        return view('employee-structure.leave-groups.add', [
            'leaveGroup' => null,
            'financialYears' => FinancialYear::query()->orderByDesc('id')->get(),
        ]);
    }

    public function store(StoreLeaveGroupRequest $request): RedirectResponse
    {
        LeaveGroup::query()->create($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Leave Group created successfully.');
    }

    public function edit(Request $request): View
    {
        $leaveGroup = LeaveGroup::query()
            ->with([
                'grade_wise_monthly_leave_details.leave_type',
                'grade_wise_monthly_leave_details.sandwich_leave',
            ])
            ->findOrFail($request->route('leave_group'));

        return view('employee-structure.leave-groups.edit', [
            'leaveGroup' => $leaveGroup,
            'financialYears' => FinancialYear::query()->orderByDesc('id')->get(),
            'leaveTypes' => LeaveType::withTrashed()->orderBy('description')->get(),
        ]);
    }

    public function update(UpdateLeaveGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $monthlyRules = $validated['monthly_rules'] ?? [];
        $replaceMonthlyRules = (bool) ($validated['monthly_rules_present'] ?? array_key_exists('monthly_rules', $validated));
        unset($validated['monthly_rules']);
        unset($validated['monthly_rules_present']);

        DB::transaction(function () use ($request, $validated, $monthlyRules, $replaceMonthlyRules): void {
            $leaveGroup = LeaveGroup::query()->findOrFail($request->route('leave_group'));
            $leaveGroup->update($validated);

            if (!$replaceMonthlyRules) {
                return;
            }

            $retainedRuleIds = [];
            foreach ($monthlyRules as $rule) {
                $values = array_diff_key($rule, ['id' => true]);
                $values['leave_types_not_to_be_clubbed'] ??= [];
                $values['include_leaves'] ??= [];
                if (isset($rule['id'])) {
                    $leaveGroup->grade_wise_monthly_leave_details()
                        ->whereKey($rule['id'])
                        ->firstOrFail()
                        ->update($values);
                    $retainedRuleIds[] = $rule['id'];
                } else {
                    $createdRule = $leaveGroup->grade_wise_monthly_leave_details()->create($values);
                    $retainedRuleIds[] = $createdRule->getKey();
                }
            }

            $rulesToDelete = $leaveGroup->grade_wise_monthly_leave_details();
            if ($retainedRuleIds !== []) {
                $rulesToDelete->whereNotIn('id', $retainedRuleIds);
            }
            $rulesToDelete->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Leave Group updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $leaveGroup = LeaveGroup::query()->findOrFail($request->route('leave_group'));
        if (
            $leaveGroup->users()->exists()
            || $leaveGroup->grade_wise_monthly_leave_details()->exists()
            || $leaveGroup->grade_wise_yearly_leave_details()->exists()
        ) {
            return redirect(url(self::INDEX_URL))->with('error', 'This Leave Group has assigned employees or leave rules and cannot be deleted.');
        }

        $leaveGroup->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Leave Group deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'leave_groups_'.now()->format('Ymd_His').'.csv';
        GenerateLeaveGroupExport::dispatch(Auth::guard('tenant')->id(), $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))->with('success', 'Leave Group export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                abort(500, 'Unable to create the Leave Group import sample.');
            }

            CsvImportTemplate::write($handle, LeaveGroup::IMPORT_EXPORT_COLUMNS, [
                'name' => 'Required; text, maximum 255 characters.',
                'financial_year_id' => 'Optional; an existing financial year ID.',
                'is_monthly' => 'Required; Yes or No.',
                'status' => 'Optional; Active or Inactive; blank defaults to Active.',
            ]);
            fclose($handle);
        }, 'leave_groups_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);
        $file = $request->file('file');
        $headerErrors = CsvImportTemplate::validateHeaders($file->getPathname(), LeaveGroup::IMPORT_EXPORT_COLUMNS);
        if ($headerErrors !== []) {
            return redirect(url(self::INDEX_URL))->withErrors(['file' => implode(' ', $headerErrors)]);
        }

        $storedPath = $file->storeAs(
            'leave-group-imports',
            'leave_group_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if ($storedPath === false) {
            abort(500, 'Unable to store the Leave Group import file.');
        }

        ProcessLeaveGroupImport::dispatch(
            Auth::guard('tenant')->id(),
            $storedPath,
            (bool) ($validated['update_duplicate_records'] ?? false),
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))->with('success', 'Leave Group import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aleave_groups_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'leave-group-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateLeaveGroupExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'leave_groups.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
