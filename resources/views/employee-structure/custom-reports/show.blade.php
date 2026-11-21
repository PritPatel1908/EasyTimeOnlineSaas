@extends('layout.mainlayout')
@section('content')
@php
    $customReportDetail = $customReport->custom_report_details->first();
    $customReportFields = array_values(array_filter($customReportDetail?->custom_report_fields ?? [], 'is_array'));
    $fieldNameLabels = \App\Support\CustomReportFieldOptions::all();
    $customReportFilters = $customReportDetail?->custom_report_filter;
    $customReportFilters = is_array($customReportFilters) ? $customReportFilters : [];
    $selectedFilterLabels = \App\Support\CustomReportFilterOptions::selectedLabels($customReportFilters);
@endphp
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-flex justify-content-between page-breadcrumb mb-3">
        <div><h2 class="mb-1">View Custom Report</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Reports</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/custom-reports') }}">Custom Reports</a></li><li class="breadcrumb-item active">{{ $customReport->report_name }}</li></ol></nav></div>
        <div class="d-flex align-items-center gap-2">
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'write'))
                <a href="{{ url('employee-structure/custom-reports/'.$customReport->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
            @endif
            <a href="{{ url('employee-structure/custom-reports') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Basic Information</h5></div><div class="card-body"><div class="row">
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Report Name</small>{{ $customReport->report_name }}</div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">Report Orientation</small>{{ ucfirst($customReport->report_orientation) }}</div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">Summary Orientation</small>{{ ucfirst($customReport->summary_orientation ?? $customReport->report_orientation) }}</div>
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Date Range</small>
            @if($customReport->date_range_preset === 'custom')
                {{ $customReport->from_date?->format('Y-m-d') }} – {{ $customReport->to_date?->format('Y-m-d') }}
            @else
                {{ ucwords(str_replace('_', ' ', $customReport->date_range_preset)) }} (calculated when generated)
            @endif
        </div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">Status</small><span class="badge {{ $customReport->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $customReport->status === 1 ? 'Active' : 'Inactive' }}</span></div>
    </div></div></div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Custom Report Fields</h5></div>
        <div class="card-body py-2">
            @if($customReportFields === [])
                <p class="text-center text-muted mb-0 py-2">No custom report fields configured.</p>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-0">
                    @foreach($customReportFields as $field)
                        @php
                            $fieldName = $field['field_name'] ?? '';
                        @endphp
                        <div class="col border-bottom py-2 px-2">
                            {{ $fieldNameLabels[$fieldName] ?? ($fieldName ?: '—') }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="card"><div class="card-header"><h5 class="mb-0">Custom Report Filter</h5></div><div class="card-body py-2">
        @if($selectedFilterLabels === [])
            <p class="text-muted mb-0 py-2">No filters applied. All employees are included.</p>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-2">
                @foreach($selectedFilterLabels as $filter)
                    <div class="col">
                        <div class="border rounded p-2 h-100">
                            <strong class="d-block mb-1">{{ $filter['label'] }}</strong>
                            <div class="text-muted">{{ implode(', ', $filter['values']) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div></div>
    <div class="card mt-4">
        <div class="card-header"><h5 class="mb-0">Generate and Download</h5></div>
        <div class="card-body">
            <p class="text-muted">The selected employee fields are filled from matching employees. Date presets are recalculated each time the report is generated; manual dates are used only for Custom. With Date selected, vertical exports show each employee's master details above date-wise rows; horizontal exports show master details first, then dates as columns and attendance fields as rows.</p>
            <form method="POST" action="{{ url('employee-structure/custom-reports/'.$customReport->id.'/generate') }}" class="d-flex align-items-end gap-2 flex-wrap mb-4">
                @csrf
                <div class="custom-report-format-select">
                    <label class="form-label" for="custom_report_format">File format</label>
                    <select id="custom_report_format" name="report_format" class="form-control select2" style="width: 225px" required>
                        <option value="csv">CSV</option>
                        <option value="xlsx">Excel (.xlsx)</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="ti ti-file-export me-1"></i>Generate report</button>
            </form>
            <div class="table-responsive">
                <table class="table datatable">
                    <thead><tr><th>Requested</th><th>Format</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($customReport->generations as $generation)
                            <tr>
                                <td>{{ $generation->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ strtoupper($generation->report_format->value) }}</td>
                                <td>{{ ucfirst($generation->report_status->value) }}</td>
                                <td class="text-end">
                                    @if($generation->report_status === \App\Enums\ReportStatus::generated)
                                        <a class="btn btn-sm btn-outline-primary" href="{{ url('employee-structure/custom-reports/'.$customReport->id.'/generations/'.$generation->id.'/download') }}"><i class="ti ti-download me-1"></i>Download</a>
                                    @elseif($generation->report_status === \App\Enums\ReportStatus::error)
                                        <span class="text-danger me-2">Generation failed</span>
                                        <form method="POST" action="{{ url('employee-structure/custom-reports/'.$customReport->id.'/generations/'.$generation->id.'/regenerate') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="ti ti-refresh me-1"></i>Regenerate</button>
                                        </form>
                                    @else
                                        <span class="text-muted">Refresh to check status</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No generated files yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>@include('partials.footer')</div>
@endsection
