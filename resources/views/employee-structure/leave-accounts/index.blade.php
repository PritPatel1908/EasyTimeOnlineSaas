@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Leave Accounts</h2>
            <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item active">Leave Accounts</li></ol></nav>
        </div>
        @if (\App\Support\TenantPermissions::userCan('LeaveAccount', 'create'))
            <div class="mb-2"><a href="{{ url('employee-structure/leave-accounts/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Leave Account</a></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header"><h5>Leave Account List</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light"><tr><th>Employee</th><th>Employee Code</th><th>Final Leave Balance</th><th>Created</th><th class="no-sort"></th></tr></thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td>{{ $account->user?->name ?? '—' }}</td>
                            <td>{{ $account->user?->code ?? '—' }}</td>
                            <td>
                                <strong>{{ number_format((float) $account->final_balance_total, 2) }}</strong>
                                @forelse ($account->final_leave_balances as $finalBalance)
                                    <small class="d-block text-muted">{{ $finalBalance['leave_type']?->code ?? 'Leave Type' }}: {{ number_format($finalBalance['balance'], 2) }}</small>
                                @empty
                                    <small class="d-block text-muted">No balance details</small>
                                @endforelse
                            </td>
                            <td>{{ $account->created_at?->format('d-m-Y h:i A') ?? '—' }}</td>
                            <td><div class="action-icon d-inline-flex">
                                @if (\App\Support\TenantPermissions::userCan('LeaveAccount', 'delete'))
                                    <form method="POST" action="{{ url('employee-structure/leave-accounts/'.$account->id) }}" onsubmit="return confirm('Delete this leave account? Accounts with linked details cannot be deleted.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" title="Delete"><i class="ti ti-trash"></i></button>
                                    </form>
                                @endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="ti ti-calendar-off fs-24 d-block mb-2"></i>No leave accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
        @if ($accounts->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2"><p class="mb-0 text-muted fs-13">Showing {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} of {{ $accounts->total() }} leave accounts</p>{{ $accounts->links() }}</div>
        @endif
    </div>
</div>@include('partials.footer')</div>
@endsection
