@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-flex justify-content-between align-items-center page-breadcrumb mb-3">
        <div><h2 class="mb-1">{{ $leaveGroup->name }}</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/leave-groups') }}">Leave Groups</a></li><li class="breadcrumb-item active">Details</li></ol></nav></div>
        @if(\App\Support\TenantPermissions::userCan('LeaveGroup', 'write'))<a class="btn btn-primary" href="{{ url('employee-structure/leave-groups/'.$leaveGroup->id.'/edit') }}"><i class="ti ti-edit me-1"></i>Edit</a>@endif
    </div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Leave Group Details</h5></div><div class="card-body"><div class="row">
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Financial Year</small>{{ $leaveGroup->financial_year?->name ?? '—' }}</div>
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Frequency</small>{{ $leaveGroup->is_monthly ? 'Monthly' : 'Yearly' }}</div>
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Status</small>{{ $leaveGroup->status === 1 ? 'Active' : 'Inactive' }}</div>
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Assigned Employees</small>{{ $leaveGroup->users()->count() }}</div>
    </div></div></div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Monthly Leave Rules</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Leave Type</th><th>Increment</th><th>Max Accumulation</th><th>Paid</th></tr></thead><tbody>
        @forelse($leaveGroup->grade_wise_monthly_leave_details as $detail)
            <tr><td>{{ $detail->leave_type?->description ?? $detail->leave_type?->name ?? '—' }}</td><td>{{ $detail->increment ?? '—' }}</td><td>{{ $detail->max_accumulation ?? '—' }}</td><td>{{ $detail->is_paid ? 'Yes' : 'No' }}</td></tr>
        @empty<tr><td colspan="4" class="text-center text-muted py-3">No monthly leave rules are linked.</td></tr>@endforelse
        </tbody></table></div></div></div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Yearly Leave Rules</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Leave Type</th><th>Eligible</th><th>Min. Utilization</th><th>Max Accumulation</th><th>Paid</th></tr></thead><tbody>
        @forelse($leaveGroup->grade_wise_yearly_leave_details as $detail)
            <tr><td>{{ $detail->leave_type?->description ?? $detail->leave_type?->name ?? '—' }}</td><td>{{ $detail->eligible ?? '—' }}</td><td>{{ $detail->min_utilization ?? '—' }}</td><td>{{ $detail->max_accumulation ?? '—' }}</td><td>{{ $detail->is_paid ? 'Yes' : 'No' }}</td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-3">No yearly leave rules are linked.</td></tr>@endforelse
        </tbody></table></div></div></div>
    <a href="{{ url('employee-structure/leave-groups') }}" class="btn btn-light">Back to Leave Groups</a>
</div>@include('partials.footer')</div>
@endsection
