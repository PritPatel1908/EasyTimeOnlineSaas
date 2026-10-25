@extends('layout.mainlayout')
@section('content')
@php
    $tenant = request()->route('tenant');
@endphp
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Pending Requests</h2>
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                <li class="breadcrumb-item">Request Management</li>
                <li class="breadcrumb-item active" aria-current="page">Pending Requests</li>
            </ol></nav>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5>Pending Request List</h5></div>
        <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Employees</th>
                        <th>Request Type</th>
                        <th>Request Details</th>
                        <th>Approval Flow</th>
                        <th>Level</th>
                        <th>Date &amp; Time</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($approvals as $row)
                        @php
                            $approval = $row['approval'];
                            $record = $approval->main_status?->approvable;
                        @endphp
                        <tr>
                            <td><h6 class="fw-medium fs-14 mb-0">{{ $row['employees'] }}</h6></td>
                            <td>
                                <span class="d-block fw-medium">{{ $row['requestType'] }}</span>
                                <small class="text-muted">Request #{{ $approval->main_status?->approvable_id }}</small>
                            </td>
                            <td>{{ $row['requestDetails'] }}</td>
                            <td>{{ $approval->main_status?->approval_flow?->approval_flow_code ?? '—' }}</td>
                            <td><span class="badge bg-primary">Level {{ $approval->level ?? '—' }}</span></td>
                            <td>{{ $record?->created_at?->format('d-m-Y h:i A') ?? $approval->created_at?->format('d-m-Y h:i A') ?? '—' }}</td>
                            <td>
                                <div class="action-icon d-inline-flex">
                                    <a href="{{ route('tenant.requests.pending.show', ['tenant' => $tenant, 'detail' => $approval->id]) }}" title="View request">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ti ti-inbox fs-24 d-block mb-2"></i>There are no requests waiting for your approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>@include('partials.footer')</div>
@endsection
