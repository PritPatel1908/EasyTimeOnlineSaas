@extends('layout.mainlayout')
@section('content')
@php
    $customReportDetail = $customReport->custom_report_details->first();
    $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $customReportFields = array_values(array_filter($customReportDetail?->custom_report_fields ?? [], 'is_array'));
    $fieldNameLabels = \App\Support\CustomReportFieldOptions::all();
    $dateTimeFormatLabels = [
        'default' => 'Default',
        'd-m-Y' => 'DD-MM-YYYY',
        'm-d-Y' => 'MM-DD-YYYY',
        'Y-m-d' => 'YYYY-MM-DD',
        'd/m/Y' => 'DD/MM/YYYY',
        'm/d/Y' => 'MM/DD/YYYY',
        'H:i' => '24-hour time (HH:mm)',
        'h:i A' => '12-hour time (hh:mm AM/PM)',
        'd-m-Y H:i' => 'DD-MM-YYYY HH:mm',
        'Y-m-d H:i' => 'YYYY-MM-DD HH:mm',
    ];
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
        <div class="col-md-4 mb-3"><small class="text-muted d-block">Report Name</small>{{ $customReport->report_name }}</div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">Orientation</small>{{ ucfirst($customReport->report_orientation) }}</div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">From Date</small>{{ $customReport->from_date->format('Y-m-d') }}</div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">To Date</small>{{ $customReport->to_date->format('Y-m-d') }}</div>
        <div class="col-md-2 mb-3"><small class="text-muted d-block">Status</small><span class="badge {{ $customReport->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $customReport->status === 1 ? 'Active' : 'Inactive' }}</span></div>
    </div></div></div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Custom Report Fields</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Field Name</th><th>Date/Time Format</th><th>Use in Summary</th></tr></thead>
                    <tbody>
                        @forelse($customReportFields as $field)
                            <tr>
                                <td>{{ $fieldNameLabels[$field['field_name'] ?? ''] ?? ($field['field_name'] ?? '—') }}</td>
                                <td>{{ $dateTimeFormatLabels[$field['date_time_format'] ?? 'default'] ?? ($field['date_time_format'] ?? 'Default') }}</td>
                                <td>{{ filter_var($field['use_in_summary'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No custom report fields configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card"><div class="card-header"><h5 class="mb-0">Custom Report Filter</h5></div><div class="card-body"><pre class="mb-0">{{ json_encode($customReportDetail?->custom_report_filter ?? [], $jsonFlags) }}</pre></div></div>
</div>@include('partials.footer')</div>
@endsection
