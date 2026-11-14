<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Jobs\Tenant\GenerateCustomReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCustomReportRequest;
use App\Http\Requests\Tenant\UpdateCustomReportRequest;
use App\Models\Tenant\CustomReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomReportController extends Controller
{
    private const INDEX_URL = 'employee-structure/custom-reports';

    public function index(): View
    {
        return view('employee-structure.custom-reports.index', [
            'customReports' => CustomReport::query()
                ->with('custom_report_details')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('employee-structure.custom-reports.show', [
            'customReport' => CustomReport::query()
                ->with([
                    'custom_report_details',
                    'generations' => static fn ($query) => $query->latest()->limit(10),
                ])
                ->findOrFail($request->route('custom_report')),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_format' => ['required', 'in:csv,pdf,xlsx'],
        ]);
        $customReport = CustomReport::query()->findOrFail($request->route('custom_report'));
        $generation = $customReport->generations()->create([
            'report_format' => ReportFormat::from($validated['report_format']),
            'report_status' => ReportStatus::generating,
        ]);

        GenerateCustomReportExport::dispatch($generation->id, (string) tenant('id'))
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL.'/'.$customReport->id))
            ->with('success', 'Custom report generation has started. Refresh this page to check its status.');
    }

    public function regenerate(Request $request): RedirectResponse
    {
        $customReport = CustomReport::query()->findOrFail($request->route('custom_report'));
        $failedGeneration = $customReport->generations()->findOrFail($request->route('generation'));
        abort_unless($failedGeneration->report_status === ReportStatus::error, 404);

        $generation = $customReport->generations()->create([
            'report_format' => $failedGeneration->report_format,
            'report_status' => ReportStatus::generating,
        ]);

        GenerateCustomReportExport::dispatch($generation->id, (string) tenant('id'))
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL.'/'.$customReport->id))
            ->with('success', 'Custom report regeneration has started. Refresh this page to check its status.');
    }

    public function downloadGeneration(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $customReport = CustomReport::query()->findOrFail($request->route('custom_report'));
        $generation = $customReport->generations()->findOrFail($request->route('generation'));

        abort_unless(
            $generation->report_status === ReportStatus::generated
                && $generation->report_path !== null
                && Storage::disk('local')->exists($generation->report_path),
            404,
        );

        $format = $generation->report_format->value;
        $contentTypes = [
            'csv' => 'text/csv; charset=UTF-8',
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $fileName = (Str::slug($customReport->report_name) ?: 'custom-report').'.'.$format;

        return Storage::disk('local')->download($generation->report_path, $fileName, [
            'Content-Type' => $contentTypes[$format],
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,1,0']]);
        $query = CustomReport::query()->latest('id');

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }

        $customReports = $query->get();

        return response()->json([
            'html' => view('employee-structure.custom-reports.partials.rows', compact('customReports'))->render(),
            'count' => $customReports->count(),
        ]);
    }

    public function create(): View
    {
        return view('employee-structure.custom-reports.add', [
            'customReport' => new CustomReport,
            'customReportDetail' => null,
        ]);
    }

    public function store(StoreCustomReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $detail = [
            'custom_report_fields' => $validated['custom_report_fields'],
            'custom_report_filter' => $validated['custom_report_filter'],
        ];
        unset($validated['custom_report_fields'], $validated['custom_report_filter']);

        DB::transaction(function () use ($validated, $detail): void {
            $customReport = CustomReport::query()->create($validated);
            $customReport->custom_report_details()->create($detail);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Custom report created successfully.');
    }

    public function edit(Request $request): View
    {
        $customReport = CustomReport::query()
            ->with('custom_report_details')
            ->findOrFail($request->route('custom_report'));

        return view('employee-structure.custom-reports.edit', [
            'customReport' => $customReport,
            'customReportDetail' => $customReport->custom_report_details->first(),
        ]);
    }

    public function update(UpdateCustomReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $detail = [
            'custom_report_fields' => $validated['custom_report_fields'],
            'custom_report_filter' => $validated['custom_report_filter'],
        ];
        unset($validated['custom_report_fields'], $validated['custom_report_filter']);

        DB::transaction(function () use ($request, $validated, $detail): void {
            $customReport = CustomReport::query()->findOrFail($request->route('custom_report'));
            $customReport->update($validated);

            $customReportDetail = $customReport->custom_report_details()->firstOrNew();
            $customReportDetail->fill($detail);
            $customReportDetail->save();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Custom report updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        CustomReport::query()->findOrFail($request->route('custom_report'))->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Custom report deleted successfully.');
    }
}
