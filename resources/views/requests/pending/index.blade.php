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
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0">Pending Request List</h5>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="text-muted fs-13" id="pending-request-selected-count">0 selected</span>
                @if ($canApprove)
                    <form method="POST" action="{{ route('tenant.requests.pending.bulk-decision', ['tenant' => $tenant]) }}" id="pending-request-bulk-approve-form" class="d-inline">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <button type="submit" class="btn btn-sm btn-success" id="pending-request-bulk-approve" disabled>
                            <i class="ti ti-check me-1"></i>Approve Selected
                        </button>
                    </form>
                @endif
                @if ($canReject)
                    <button type="button" class="btn btn-sm btn-danger" id="pending-request-bulk-reject" data-bs-toggle="modal" data-bs-target="#pending-request-bulk-reject-modal" disabled>
                        <i class="ti ti-x me-1"></i>Reject Selected
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort text-center">
                            <div class="form-check form-check-md">
                                <input class="form-check-input datatable-select-all" type="checkbox" id="pending-request-select-all" aria-label="Select all requests">
                            </div>
                        </th>
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
                            $approvalFormId = 'pending-request-approve-'.$approval->id;
                        @endphp
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input pending-request-checkbox" type="checkbox" value="{{ $approval->id }}"
                                    aria-label="Select request #{{ $approval->main_status?->approvable_id }}"
                                    @checked(in_array((int) $approval->id, array_map('intval', (array) old('approval_details', [])), true))>
                            </td>
                            <td><h6 class="fw-medium fs-14 mb-0">{{ $row['employees'] }}</h6></td>
                            <td>
                                <span class="d-block fw-medium">{{ $row['requestType'] }}</span>
                                <small class="text-muted">Request #{{ $approval->main_status?->approvable_id }}</small>
                            </td>
                            <td>{{ $row['requestDetails'] }}</td>
                            <td>{{ $approval->main_status?->approval_flow?->approval_flow_code ?? '—' }}</td>
                            <td><span class="badge bg-primary">Level {{ $approval->level ?? '—' }}</span></td>
                            <td>{{ $record?->created_at?->format('d-m-Y h:i A') ?? $approval->created_at?->format('d-m-Y h:i A') ?? '—' }}</td>
                            <td class="text-center">
                                <div class="action-icon d-inline-flex">
                                    <a href="{{ route('tenant.requests.pending.show', ['tenant' => $tenant, 'detail' => $approval->id]) }}" class="me-2" title="View request" aria-label="View request">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if ($canApprove)
                                        <a href="javascript:void(0);" class="me-2 text-success pending-request-approve" data-form-id="{{ $approvalFormId }}" title="Approve request" aria-label="Approve request">
                                            <i class="ti ti-check"></i>
                                        </a>
                                        <form method="POST" action="{{ route('tenant.requests.pending.decision', ['tenant' => $tenant, 'detail' => $approval->id]) }}" id="{{ $approvalFormId }}" class="d-none">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                        </form>
                                    @endif
                                    @if ($canReject)
                                        <a href="javascript:void(0);" class="text-danger" title="Reject request" aria-label="Reject request"
                                            data-bs-toggle="modal" data-bs-target="#pending-request-reject-modal"
                                            data-decision-url="{{ route('tenant.requests.pending.decision', ['tenant' => $tenant, 'detail' => $approval->id]) }}"
                                            data-detail="{{ $approval->id }}">
                                            <i class="ti ti-x"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ti ti-inbox fs-24 d-block mb-2"></i>There are no requests waiting for your approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>@include('partials.footer')</div>

@if ($canReject)
    <div class="modal fade" id="pending-request-bulk-reject-modal" tabindex="-1" aria-labelledby="pending-request-bulk-reject-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('tenant.requests.pending.bulk-decision', ['tenant' => $tenant]) }}" id="pending-request-bulk-reject-form">
                    @csrf
                    <input type="hidden" name="decision" value="rejected">
                    @foreach (old('approval_details', []) as $approvalId)
                        <input type="hidden" name="approval_details[]" value="{{ $approvalId }}">
                    @endforeach
                    <div class="modal-header">
                        <h5 class="modal-title" id="pending-request-bulk-reject-title">Reject selected requests</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">The reason will be applied to every selected request.</p>
                        <label for="pending-request-bulk-rejection-reason" class="form-label">Rejection reason <span class="text-muted">(required)</span></label>
                        <textarea id="pending-request-bulk-rejection-reason" name="rejection_reason" rows="3" maxlength="1000" required
                            class="form-control @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Selected</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pending-request-reject-modal" tabindex="-1" aria-labelledby="pending-request-reject-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="pending-request-reject-form">
                    @csrf
                    <input type="hidden" name="decision" value="rejected">
                    <input type="hidden" name="approval_detail" id="pending-request-approval-detail" value="{{ old('approval_detail') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pending-request-reject-title">Reject request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="pending-request-rejection-reason" class="form-label">Rejection reason <span class="text-muted">(required)</span></label>
                        <textarea id="pending-request-rejection-reason" name="rejection_reason" rows="3" maxlength="1000" required
                            class="form-control @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endif

@if ($canApprove || $canReject)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sourceTable = document.querySelector('table.datatable');
                var checkboxes = sourceTable
                    ? sourceTable.querySelectorAll('tbody .pending-request-checkbox')
                    : document.querySelectorAll('.pending-request-checkbox');
                var selectedCount = document.getElementById('pending-request-selected-count');
                var approveButton = document.getElementById('pending-request-bulk-approve');
                var rejectButton = document.getElementById('pending-request-bulk-reject');
                var approveForm = document.getElementById('pending-request-bulk-approve-form');
                var bulkRejectModal = document.getElementById('pending-request-bulk-reject-modal');
                var bulkRejectForm = document.getElementById('pending-request-bulk-reject-form');
                var rejectModal = document.getElementById('pending-request-reject-modal');
                var rejectForm = document.getElementById('pending-request-reject-form');
                var approvalDetail = document.getElementById('pending-request-approval-detail');

                function selectedApprovalIds() {
                    return Array.from(checkboxes)
                        .filter(function (checkbox) { return checkbox.checked; })
                        .map(function (checkbox) { return checkbox.value; });
                }

                function updateBulkActions() {
                    var selected = selectedApprovalIds();
                    selectedCount.textContent = selected.length + ' selected';
                    if (approveButton) {
                        approveButton.disabled = selected.length === 0;
                    }
                    if (rejectButton) {
                        rejectButton.disabled = selected.length === 0;
                    }
                    var visibleTable = document.querySelector('.gridjs-table') || sourceTable;
                    if (visibleTable) {
                        var visibleCheckboxes = visibleTable.querySelectorAll('tbody tr td:first-child .pending-request-checkbox');
                        var visibleSelected = Array.from(visibleCheckboxes).filter(function (checkbox) {
                            return checkbox.checked;
                        }).length;
                        document.querySelectorAll('.datatable-select-all').forEach(function (masterCheckbox) {
                            masterCheckbox.checked = visibleCheckboxes.length > 0 && visibleSelected === visibleCheckboxes.length;
                            masterCheckbox.indeterminate = visibleSelected > 0 && visibleSelected < visibleCheckboxes.length;
                        });
                    }
                }

                document.addEventListener('change', function (event) {
                    var changedCheckbox = event.target.closest('.pending-request-checkbox');
                    if (changedCheckbox && changedCheckbox.closest('table') !== sourceTable && sourceTable) {
                        var sourceCheckbox = Array.from(checkboxes).find(function (checkbox) {
                            return checkbox.value === changedCheckbox.value;
                        });
                        if (sourceCheckbox) {
                            sourceCheckbox.checked = changedCheckbox.checked;
                        }
                        updateBulkActions();
                        return;
                    }

                    var masterCheckbox = event.target.closest('.datatable-select-all');
                    if (masterCheckbox) {
                        var table = masterCheckbox.closest('table');
                        if (table) {
                            table.querySelectorAll('tbody tr td:first-child .pending-request-checkbox').forEach(function (checkbox) {
                                checkbox.checked = masterCheckbox.checked;
                                var sourceCheckbox = Array.from(checkboxes).find(function (source) {
                                    return source.value === checkbox.value;
                                });
                                if (sourceCheckbox) {
                                    sourceCheckbox.checked = masterCheckbox.checked;
                                }
                            });
                        }
                        updateBulkActions();
                        return;
                    }

                    if (changedCheckbox) {
                        updateBulkActions();
                    }
                });

                if (approveForm) {
                    approveForm.addEventListener('submit', function (event) {
                        var selected = selectedApprovalIds();
                        if (selected.length === 0) {
                            event.preventDefault();
                            return;
                        }

                        approveForm.querySelectorAll('.pending-request-bulk-id').forEach(function (input) {
                            input.remove();
                        });
                        selected.forEach(function (id) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'approval_details[]';
                            input.value = id;
                            input.className = 'pending-request-bulk-id';
                            approveForm.appendChild(input);
                        });
                    });
                }

                if (bulkRejectModal && bulkRejectForm) {
                    bulkRejectModal.addEventListener('show.bs.modal', function (event) {
                        if (!event.relatedTarget || event.relatedTarget.id !== 'pending-request-bulk-reject') {
                            return;
                        }

                        bulkRejectForm.querySelectorAll('.pending-request-bulk-id').forEach(function (input) {
                            input.remove();
                        });
                        selectedApprovalIds().forEach(function (id) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'approval_details[]';
                            input.value = id;
                            input.className = 'pending-request-bulk-id';
                            bulkRejectForm.appendChild(input);
                        });
                    });
                }

                document.querySelectorAll('.pending-request-approve').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var form = document.getElementById(button.getAttribute('data-form-id'));
                        if (form) {
                            form.requestSubmit();
                        }
                    });
                });

                if (rejectModal && rejectForm && approvalDetail) {
                    rejectModal.addEventListener('show.bs.modal', function (event) {
                        var trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        rejectForm.action = trigger.getAttribute('data-decision-url');
                        approvalDetail.value = trigger.getAttribute('data-detail');
                    });

                    @if (old('approval_detail'))
                        var previousRejectButton = document.querySelector('[data-detail="{{ old('approval_detail') }}"][data-bs-target="#pending-request-reject-modal"]');
                        if (previousRejectButton && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(rejectModal).show(previousRejectButton);
                        }
                    @endif
                }

                updateBulkActions();

                @if (old('decision') === 'rejected' && old('approval_details'))
                    if (bulkRejectModal && window.bootstrap) {
                        bootstrap.Modal.getOrCreateInstance(bulkRejectModal).show();
                    }
                @endif
            });
        </script>
    @endpush
@endif
@endsection
