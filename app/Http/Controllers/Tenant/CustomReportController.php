<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCustomReportRequest;
use App\Http\Requests\Tenant\UpdateCustomReportRequest;
use App\Models\Tenant\CustomReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                ->with('custom_report_details')
                ->findOrFail($request->route('custom_report')),
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
