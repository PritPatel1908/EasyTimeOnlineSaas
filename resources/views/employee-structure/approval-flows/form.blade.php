@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-flex justify-content-between page-breadcrumb mb-3">
        <div>
            <h2 class="mb-1">{{ $approvalFlow ? 'Edit Approval Flow' : 'Add Approval Flow' }}</h2>
            <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Employee Structure</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/approval-flows') }}">Approval Flows</a></li><li class="breadcrumb-item active">{{ $approvalFlow ? 'Edit Approval Flow' : 'Add Approval Flow' }}</li></ol></nav>
        </div>
    </div>
    @php($formDetails = old('details', $details))
    @php($employeeAssignments = old('employee_assignments', $employeeAssignments ?? []))
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Approval Flow Details</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ $approvalFlow ? url('employee-structure/approval-flows/'.$approvalFlow->id) : url('employee-structure/approval-flows') }}" class="approval-flow-form" novalidate>
                @csrf
                @if($approvalFlow) @method('PUT') @endif
                <div class="mb-4">
                    <label for="approval_flow_code" class="form-label">Approval Flow Code <span class="text-danger">*</span></label>
                    <input id="approval_flow_code" name="approval_flow_code" type="text" maxlength="255" required class="form-control @error('approval_flow_code') is-invalid @enderror" value="{{ old('approval_flow_code', $approvalFlow?->approval_flow_code) }}">
                    @error('approval_flow_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="approval_flow_status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select id="approval_flow_status" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                        <option value="1" @selected((string) old('status', $status ?? $approvalFlow?->status ?? 1) === '1')>Active</option>
                        <option value="0" @selected((string) old('status', $status ?? $approvalFlow?->status ?? 1) === '0')>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="border rounded p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Approval Levels &amp; Approvers</h5>
                        <button type="button" id="add-approval-level" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>Add Level</button>
                    </div>
                    <div id="approval-levels">
                        @foreach($formDetails as $index => $detail)
                            @include('employee-structure.approval-flows.partials.level-fields', ['index' => $index, 'detail' => $detail])
                        @endforeach
                    </div>
                    @error('details')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    @foreach($errors->getMessages() as $field => $messages)
                        @if(str_starts_with($field, 'details.'))<div class="text-danger small mt-2">{{ implode(' ', $messages) }}</div>@endif
                    @endforeach
                </div>
                <div class="border rounded p-3 mb-4">
                    <h5 class="mb-3">Assign Employees by Module</h5>
                    <p class="text-muted">Choose the employees who should use this approval flow for each module.</p>
                    <div class="row">
                        @foreach($approvalModules as $moduleField => $moduleLabel)
                            @php($selectedEmployees = array_map('strval', (array) ($employeeAssignments[$moduleField] ?? [])))
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="employee_assignments_{{ $moduleField }}">{{ $moduleLabel }} Employees</label>
                                <select id="employee_assignments_{{ $moduleField }}" name="employee_assignments[{{ $moduleField }}][]" class="form-control approval-employee-select" placeholder="Select employees" multiple>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(in_array((string) $employee->id, $selectedEmployees, true))>{{ $employee->name }}{{ $employee->email ? ' — '.$employee->email : '' }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ url('employee-structure/approval-flows') }}" class="btn btn-light me-2">Cancel</a>
                    <button class="btn btn-primary">{{ $approvalFlow ? 'Save Changes' : 'Add Approval Flow' }}</button>
                </div>
            </form>
        </div>
    </div>
    <template id="approval-level-template">@include('employee-structure.approval-flows.partials.level-fields', ['index' => '__INDEX__', 'detail' => ['level' => '', 'users' => []]])</template>
</div>@include('partials.footer')</div>
@push('scripts')
<script>
(function () {
    var container = document.getElementById('approval-levels');
    var template = document.getElementById('approval-level-template');
    var nextIndex = {{ count($formDetails) }};

    function initializeApproverSelect(select) {
        if (!select.tomselect) {
            new TomSelect(select, {
                placeholder: select.getAttribute('placeholder') || 'Select',
                plugins: ['remove_button']
            });
        }
    }

    function refreshLevelNumbers() {
        container.querySelectorAll('.approval-level-row').forEach(function (row, index) {
            row.querySelector('.approval-level-number').textContent = index + 1;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.approval-approver-select, .approval-employee-select').forEach(initializeApproverSelect);
    });

    document.getElementById('add-approval-level').addEventListener('click', function () {
        container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
        initializeApproverSelect(container.lastElementChild.querySelector('.approval-approver-select'));
        refreshLevelNumbers();
    });
    container.addEventListener('click', function (event) {
        var button = event.target.closest('.remove-approval-level');
        if (button) {
            var row = button.closest('.approval-level-row');
            row.remove();
            refreshLevelNumbers();
        }
    });
    refreshLevelNumbers();
})();
</script>
@endpush
@endsection
