@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2">
            <h2 class="mb-1">Leave Account Details</h2>
            <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item active">Leave Account Details</li></ol></nav>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5>Transaction History</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light"><tr><th>Employee</th><th>Leave Type</th><th>Opening Balance</th><th>Credit</th><th>Debit</th><th>Balance</th><th>Transaction</th><th>Transaction Date</th><th>Remarks</th><th>Source</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        @php
                            $account = $accountsById[$transaction->leave_account_id] ?? null;
                            $leaveType = $leaveTypesById[$transaction->leave_type_id] ?? null;
                        @endphp
                        <tr>
                            <td>{{ $account?->user?->name ?? '—' }}<small class="d-block text-muted">{{ $account?->code ?? $account?->user?->code }}</small></td>
                            <td>{{ $leaveType?->code }}{{ $leaveType?->description ? ' — '.$leaveType->description : '' }}</td>
                            <td>{{ number_format((float) $transaction->opening_balance, 2) }}</td>
                            <td>{{ number_format((float) $transaction->credit, 2) }}</td>
                            <td>{{ number_format((float) $transaction->debit, 2) }}</td>
                            <td>{{ number_format((float) $transaction->balance, 2) }}</td>
                            <td>{{ $transaction->transaction_year.' #'.$transaction->id }}</td>
                            <td>{{ $transaction->trx_datetime ? \Illuminate\Support\Carbon::parse($transaction->trx_datetime)->format('d-m-Y h:i A') : '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($transaction->remarks ?? '—', 50) }}</td>
                            <td>{{ $transaction->referenceable_type ? class_basename($transaction->referenceable_type).' #'.$transaction->referenceable_id : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-4 text-muted"><i class="ti ti-calendar-off fs-24 d-block mb-2"></i>No leave transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
        @if ($transactions->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2"><p class="mb-0 text-muted fs-13">Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }} leave transactions</p>{{ $transactions->links() }}</div>
        @endif
    </div>
</div>@include('partials.footer')</div>
@endsection
