<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreOvertimeApplicationRequest;
use App\Http\Requests\Tenant\UpdateOvertimeApplicationRequest;
use App\Jobs\Tenant\GenerateOvertimeApplicationExport;
use App\Jobs\Tenant\ProcessOvertimeApplicationImport;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\OvertimeApplicationWorkflow;
use App\Support\CsvImportTemplate;
use App\Support\TenantPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OvertimeApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/overtime-applications';

    public function index(): View
    {
        return view('time-management.overtime-applications.index', [
            'applications' => OvertimeApplication::query()
                ->with(['user', 'approval_statuses'])
                ->latest('date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $application = OvertimeApplication::query()
            ->with(['user', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'])
            ->findOrFail($request->route('overtime_application'));

        return view('time-management.overtime-applications.show', [
            'application' => $application,
        ]);
    }

    public function create(): View
    {
        return view('time-management.overtime-applications.add', $this->formOptions(null));
    }

    public function attendanceOvertime(Request $request, OvertimeApplicationWorkflow $workflow): JsonResponse
    {
        abort_unless(
            TenantPermissions::userCan('OvertimeApplication', 'create')
                || TenantPermissions::userCan('OvertimeApplication', 'write'),
            403,
        );

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $minutes = $workflow->availableOvertimeMinutes(
            (int) $validated['user_id'],
            $validated['date'],
        );

        return response()->json([
            'eligible' => $minutes !== null,
            'overtime_minutes' => $minutes,
            'message' => $minutes === null
                ? $workflow->unavailableOvertimeMessage(
                    (int) $validated['user_id'],
                    $validated['date'],
                )
                : null,
        ]);
    }

    public function store(
        StoreOvertimeApplicationRequest $request,
        OvertimeApplicationWorkflow $workflow,
    ): RedirectResponse {
        $payload = $request->validated();
        $workflow->create($payload, (int) Auth::guard('tenant')->id());

        return redirect(url(self::INDEX_URL))->with('success', 'Overtime application submitted for approval.');
    }

    public function edit(Request $request): View
    {
        $application = OvertimeApplication::query()
            ->findOrFail($request->route('overtime_application'));

        return view('time-management.overtime-applications.edit', $this->formOptions($application));
    }

    public function update(
        UpdateOvertimeApplicationRequest $request,
        OvertimeApplicationWorkflow $workflow,
    ): RedirectResponse {
        $application = OvertimeApplication::query()
            ->findOrFail($request->route('overtime_application'));
        $workflow->update(
            $application,
            $request->validated(),
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Overtime application updated and resubmitted for approval.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        OvertimeApplication::query()
            ->findOrFail($request->route('overtime_application'))
            ->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Overtime application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'overtime_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateOvertimeApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Overtime application export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, OvertimeApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas or semicolons.',
                'date' => 'Required; date in YYYY-MM-DD format.',
                'overtime_minutes' => 'Required; positive number of minutes.',
                'reason' => 'Optional; text up to 255 characters.',
                'approval_status' => 'Generated by the application; this column is ignored on import.',
            ]);
            fclose($handle);
        }, 'overtime_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'overtime-application-imports',
            'overtime_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded overtime application import file.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessOvertimeApplicationImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Overtime application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aovertime_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'overtime-application-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateOvertimeApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'overtime_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?OvertimeApplication $application): array
    {
        return [
            'application' => $application,
            'users' => User::query()->orderBy('code')->get(['id', 'name', 'code']),
        ];
    }
}
