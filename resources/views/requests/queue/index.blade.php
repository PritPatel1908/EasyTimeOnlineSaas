@extends('layout.mainlayout')
@section('content')
@php
    $tenant = request()->route('tenant');
@endphp
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2">
            <h2 class="mb-1">{{ $pageTitle }}</h2>
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                <li class="breadcrumb-item">Request Management</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
            </ol></nav>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ rtrim($pageTitle, 's') }} List</h5></div>
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
                        <th class="no-sort text-center">Actions</th>
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
                                @if ($source === 'cancelled')
                                    <span class="badge bg-secondary d-block mt-1">Cancelled</span>
                                @endif
                            </td>
                            <td>{{ $row['requestDetails'] }}</td>
                            <td>{{ $approval->main_status?->approval_flow?->approval_flow_code ?? '—' }}</td>
                            <td>
                                @php
                                    $levelBadge = match ($approval->approval_status) {
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'cancelled' => 'bg-secondary',
                                        default => 'bg-warning',
                                    };
                                @endphp
                                <span class="badge {{ $levelBadge }}">Level {{ $approval->level ?? '—' }}</span>
                            </td>
                            <td>{{ $record?->created_at?->format('d-m-Y h:i A') ?? $approval->created_at?->format('d-m-Y h:i A') ?? '—' }}</td>
                            <td class="text-center">
                                @if ($source === 'approved' && $row['cancellationStatus'] === 'pending')
                                    <span class="badge bg-info d-block mb-1">Cancellation queued</span>
                                @elseif ($source === 'approved' && $row['cancellationStatus'] === 'failed')
                                    <span class="badge bg-danger d-block mb-1" title="Retry cancellation or contact support">Cancellation failed</span>
                                @endif
                                <div class="action-icon d-inline-flex align-items-center">
                                    <a href="{{ route('tenant.requests.'.$source.'.show', ['tenant' => $tenant, 'detail' => $approval->id]) }}"
                                        class="me-2" title="View request" aria-label="View request">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if ($source === 'approved' && $canCancel && $row['cancellationStatus'] !== 'pending')
                                        <a href="javascript:void(0);" class="text-danger"
                                            data-bs-toggle="modal" data-bs-target="#approved-request-cancel-modal"
                                            data-cancel-url="{{ route('tenant.requests.approved.cancel', ['tenant' => $tenant, 'detail' => $approval->id]) }}"
                                            title="{{ $row['cancellationStatus'] === 'failed' ? 'Retry cancellation' : 'Cancel approved request' }}"
                                            aria-label="{{ $row['cancellationStatus'] === 'failed' ? 'Retry cancellation' : 'Cancel approved request' }}">
                                            <i class="ti ti-x"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ti ti-inbox fs-24 d-block mb-2"></i>{{ $emptyMessage }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>@include('partials.footer')</div>

@if ($source === 'approved' && $canCancel)
    <div class="modal fade" id="approved-request-cancel-modal" tabindex="-1" aria-labelledby="approved-request-cancel-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="approved-request-cancel-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approved-request-cancel-title">Cancel approved request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">The request's saved attendance and schedule effects will be restored.</p>
                        <label for="approved-request-cancellation-reason" class="form-label">Cancellation reason <span class="text-muted">(required)</span></label>
                        <textarea id="approved-request-cancellation-reason" name="cancellation_reason" rows="3" maxlength="1000" required
                            class="form-control @error('cancellation_reason') is-invalid @enderror">{{ old('cancellation_reason') }}</textarea>
                        @error('cancellation_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep approved</button>
                        <button type="submit" class="btn btn-danger">Cancel Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.getElementById('approved-request-cancel-modal');
                var form = document.getElementById('approved-request-cancel-form');
                if (!modal || !form) {
                    return;
                }

                modal.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    if (trigger) {
                        form.action = trigger.getAttribute('data-cancel-url');
                    }
                });
            });
        </script>
    @endpush
@endif
@endsection
