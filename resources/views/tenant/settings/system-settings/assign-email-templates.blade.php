@extends('layout.mainlayout')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            @include('partials.flash-alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">System Settings</li>
                            <li class="breadcrumb-item active" aria-current="page">Assign Email Templates</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('profile-settings') }}"><i class="ti ti-settings me-2"></i>General Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('salary-settings') }}"><i class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ url('default-settings') }}"><i class="ti ti-server-cog me-2"></i>System Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('payment-gateways') }}"><i class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('custom-css') }}"><i class="ti ti-settings-2 me-2"></i>Other Settings</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-xl-3 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column list-group settings-list">
                                <a href="{{ url('default-settings') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Default Settings</a>
                                <a href="{{ url('email-settings') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Email Settings</a>
                                <a href="{{ url('email-template') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Email Templates</a>
                                <a href="{{ url('assign-email-templates') }}" class="d-inline-flex align-items-center rounded active py-2 px-3">
                                    <i class="ti ti-arrow-badge-right me-2"></i>Assign Email Templates
                                </a>
                                <a href="{{ url('sms-settings') }}" class="d-inline-flex align-items-center rounded py-2 px-3">SMS Settings</a>
                                <a href="{{ url('sms-template') }}" class="d-inline-flex align-items-center rounded py-2 px-3">SMS Templates</a>
                                <a href="{{ url('otp-settings') }}" class="d-inline-flex align-items-center rounded py-2 px-3">OTP</a>
                                <a href="{{ url('gdpr') }}" class="d-inline-flex align-items-center rounded py-2 px-3">GDPR Cookies</a>
                                <a href="{{ url('maintenance-mode') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Maintenance Mode</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom d-flex align-items-center justify-content-between pb-3 mb-3">
                                <h4>Assign Email Templates</h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-assignment">
                                    <i class="ti ti-circle-plus me-2"></i>Add Assignment
                                </button>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    Please review the assignment details and try again.
                                </div>
                            @endif

                            <div class="row g-3">
                                @forelse ($emailTemplateAssignments as $assignment)
                                    <div class="col-md-6 col-xxl-4">
                                        <div class="card border h-100 mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <div class="me-2">
                                                        <h6 class="mb-1">{{ \Illuminate\Support\Str::headline($assignment->application_type) }}</h6>
                                                        <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::headline($assignment->application_action) }}</p>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button" class="btn btn-sm btn-icon"
                                                            data-bs-toggle="modal" data-bs-target="#edit-assignment"
                                                            data-assignment-id="{{ $assignment->id }}"
                                                            data-application-type="{{ $assignment->application_type }}"
                                                            data-application-action="{{ $assignment->application_action }}"
                                                            data-email-template-id="{{ $assignment->email_template_id }}"
                                                            data-status="{{ $assignment->status }}"
                                                            data-send-to-approver="{{ $assignment->send_to_approver ? '1' : '0' }}"
                                                            data-send-to-another-approver="{{ $assignment->send_to_another_approver ? '1' : '0' }}"
                                                            data-send-to-employee="{{ $assignment->send_to_employee ? '1' : '0' }}"
                                                            aria-label="Edit assignment">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                        <form action="{{ url('assign-email-templates/'.$assignment->id) }}" method="POST" class="d-inline"
                                                            onsubmit="return confirm('Delete this email template assignment?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-icon" aria-label="Delete assignment">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="border-top mt-3 pt-3">
                                                    <small class="text-muted d-block mb-1">Email Template</small>
                                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                                        <span class="text-break">{{ $assignment->emailTemplate->title ?: \Illuminate\Support\Str::headline($assignment->emailTemplate->template_type) }}</span>
                                                        <span class="badge {{ $assignment->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                                            {{ \Illuminate\Support\Str::headline($assignment->status) }}
                                                        </span>
                                                    </div>
                                                    <div class="small text-muted mt-2">
                                                        @if ($assignment->application_action === 'initial')
                                                            Send to approver: {{ $assignment->send_to_approver ? 'Yes' : 'No' }}
                                                        @else
                                                            Another approver: {{ $assignment->send_to_another_approver ? 'Yes' : 'No' }}
                                                            <span class="mx-1">|</span>
                                                            Employee: {{ $assignment->send_to_employee ? 'Yes' : 'No' }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="border rounded text-center text-muted py-4">No email template assignments found.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.footer')
    </div>

    <div class="modal fade" id="add-assignment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ url('assign-email-templates') }}" method="POST" class="modal-content" data-assignment-form>
                @csrf
                <input type="hidden" name="assignment_form" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Email Template Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="add-application-type">Application Type</label>
                        <select id="add-application-type" name="application_type" class="form-select" required>
                            <option value="">Select Application Type</option>
                            @foreach ($applicationTypes as $applicationType)
                                <option value="{{ $applicationType }}" @selected(old('assignment_form') === 'add' && old('application_type') === $applicationType)>
                                    {{ \Illuminate\Support\Str::headline($applicationType) }}
                                </option>
                            @endforeach
                        </select>
                        @error('application_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add-application-action">Application Action</label>
                        <select id="add-application-action" name="application_action" class="form-select" required>
                            <option value="">Select Application Action</option>
                            <option value="initial" @selected(old('assignment_form') === 'add' && old('application_action') === 'initial')>Initial</option>
                            <option value="approve" @selected(old('assignment_form') === 'add' && old('application_action') === 'approve')>Approve</option>
                            <option value="reject" @selected(old('assignment_form') === 'add' && old('application_action') === 'reject')>Reject</option>
                            <option value="cancel" @selected(old('assignment_form') === 'add' && old('application_action') === 'cancel')>Cancel</option>
                        </select>
                        @error('application_action') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add-email-template">Email Template</label>
                        <select id="add-email-template" name="email_template_id" class="form-select" data-template-select required>
                            <option value="">Select Email Template</option>
                            @foreach ($emailTemplates as $emailTemplate)
                                <option value="{{ $emailTemplate->id }}" data-template-type="{{ substr($emailTemplate->template_type, 0, -strlen('_approve_reject')) }}"
                                    @selected(old('assignment_form') === 'add' && old('email_template_id') == $emailTemplate->id)>
                                    {{ $emailTemplate->title ?: \Illuminate\Support\Str::headline($emailTemplate->template_type) }}
                                    ({{ \Illuminate\Support\Str::headline($emailTemplate->status) }})
                                </option>
                            @endforeach
                        </select>
                        @error('email_template_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3 d-none" data-initial-flags>
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="add-send-to-approver" name="send_to_approver" type="checkbox" value="1"
                                @checked(old('assignment_form') === 'add' && old('send_to_approver'))>
                            <label class="form-check-label" for="add-send-to-approver">Send to approver</label>
                        </div>
                    </div>
                    <div class="mb-3 d-none" data-approval-flags>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" id="add-send-to-another-approver" name="send_to_another_approver" type="checkbox" value="1"
                                @checked(old('assignment_form') === 'add' && old('send_to_another_approver'))>
                            <label class="form-check-label" for="add-send-to-another-approver">Send to another approver</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="add-send-to-employee" name="send_to_employee" type="checkbox" value="1"
                                @checked(old('assignment_form') === 'add' && old('send_to_employee'))>
                            <label class="form-check-label" for="add-send-to-employee">Send to employee</label>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="add-assignment-status">Status</label>
                        <select id="add-assignment-status" name="status" class="form-select" required>
                            <option value="active" @selected(old('assignment_form') !== 'add' || old('status') === 'active')>Active</option>
                            <option value="inactive" @selected(old('assignment_form') === 'add' && old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="edit-assignment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="#" method="POST" class="modal-content" data-edit-assignment-form data-assignment-form>
                @csrf
                @method('PUT')
                <input type="hidden" name="assignment_form" value="edit">
                <input type="hidden" name="assignment_id" value="{{ old('assignment_id') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Email Template Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="edit-application-type">Application Type</label>
                        <select id="edit-application-type" name="application_type" class="form-select" required>
                            <option value="">Select Application Type</option>
                            @foreach ($applicationTypes as $applicationType)
                                <option value="{{ $applicationType }}">{{ \Illuminate\Support\Str::headline($applicationType) }}</option>
                            @endforeach
                        </select>
                        @error('application_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="edit-application-action">Application Action</label>
                        <select id="edit-application-action" name="application_action" class="form-select" required>
                            <option value="">Select Application Action</option>
                            <option value="initial">{{ __('Initial') }}</option>
                            <option value="approve">{{ __('Approve') }}</option>
                            <option value="reject">{{ __('Reject') }}</option>
                            <option value="cancel">{{ __('Cancel') }}</option>
                        </select>
                        @error('application_action') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="edit-email-template">Email Template</label>
                        <select id="edit-email-template" name="email_template_id" class="form-select" data-template-select required>
                            <option value="">Select Email Template</option>
                            @foreach ($emailTemplates as $emailTemplate)
                                <option value="{{ $emailTemplate->id }}" data-template-type="{{ substr($emailTemplate->template_type, 0, -strlen('_approve_reject')) }}">
                                    {{ $emailTemplate->title ?: \Illuminate\Support\Str::headline($emailTemplate->template_type) }}
                                    ({{ \Illuminate\Support\Str::headline($emailTemplate->status) }})
                                </option>
                            @endforeach
                        </select>
                        @error('email_template_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3 d-none" data-initial-flags>
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="edit-send-to-approver" name="send_to_approver" type="checkbox" value="1"
                                @checked(old('assignment_form') === 'edit' && old('send_to_approver'))>
                            <label class="form-check-label" for="edit-send-to-approver">Send to approver</label>
                        </div>
                    </div>
                    <div class="mb-3 d-none" data-approval-flags>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" id="edit-send-to-another-approver" name="send_to_another_approver" type="checkbox" value="1"
                                @checked(old('assignment_form') === 'edit' && old('send_to_another_approver'))>
                            <label class="form-check-label" for="edit-send-to-another-approver">Send to another approver</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="edit-send-to-employee" name="send_to_employee" type="checkbox" value="1"
                                @checked(old('assignment_form') === 'edit' && old('send_to_employee'))>
                            <label class="form-check-label" for="edit-send-to-employee">Send to employee</label>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="edit-assignment-status">Status</label>
                        <select id="edit-assignment-status" name="status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const addForm = document.querySelector('#add-assignment form');
            const editModal = document.getElementById('edit-assignment');
            const editForm = editModal.querySelector('[data-edit-assignment-form]');
            const baseUrl = @json(url('assign-email-templates'));

            function setValue(form, name, value) {
                const field = form.querySelector('[name="' + name + '"]');
                if (field.type === 'checkbox') {
                    field.checked = value === true || value === 1 || value === '1' || value === 'on';
                } else if (field.tomselect) {
                    field.tomselect.setValue(value === null || value === undefined ? '' : value, true);
                } else {
                    field.value = value;
                }
            }

            function configureForm(form) {
                const applicationType = form.querySelector('[name="application_type"]');
                const applicationAction = form.querySelector('[name="application_action"]');
                const emailTemplate = form.querySelector('[data-template-select]');
                const initialFlags = form.querySelector('[data-initial-flags]');
                const approvalFlags = form.querySelector('[data-approval-flags]');

                function filterTemplates() {
                    const selectedValue = emailTemplate.tomselect
                        ? emailTemplate.tomselect.getValue()
                        : emailTemplate.value;
                    let clearSelection = false;

                    Array.from(emailTemplate.options).forEach(function (option) {
                        if (!option.value) {
                            return;
                        }

                        const matchesType = !applicationType.value
                            || option.dataset.templateType === applicationType.value;
                        option.hidden = !matchesType;
                        option.disabled = !matchesType;

                        if (!matchesType && option.value === selectedValue) {
                            clearSelection = true;
                        }
                    });

                    if (clearSelection) {
                        if (emailTemplate.tomselect) {
                            emailTemplate.tomselect.clear(true);
                        } else {
                            emailTemplate.value = '';
                        }
                    }

                    if (emailTemplate.tomselect) {
                        emailTemplate.tomselect.sync();
                        emailTemplate.tomselect.refreshOptions(false);
                    }
                }

                function updateActionFields() {
                    const isInitial = applicationAction.value === 'initial';
                    const isDecision = ['approve', 'reject', 'cancel'].includes(applicationAction.value);
                    initialFlags.classList.toggle('d-none', !isInitial);
                    approvalFlags.classList.toggle('d-none', !isDecision);

                    if (!isInitial) {
                        setValue(form, 'send_to_approver', false);
                    }
                    if (!isDecision) {
                        setValue(form, 'send_to_another_approver', false);
                        setValue(form, 'send_to_employee', false);
                    }
                }

                applicationType.addEventListener('change', filterTemplates);
                applicationAction.addEventListener('change', updateActionFields);
                filterTemplates();
                updateActionFields();

                return { filterTemplates, updateActionFields };
            }

            const addControls = configureForm(addForm);
            const editControls = configureForm(editForm);

            document.querySelectorAll('[data-assignment-id]').forEach(function (button) {
                button.addEventListener('click', function () {
                    editForm.action = baseUrl + '/' + encodeURIComponent(button.dataset.assignmentId);
                    editForm.querySelector('[name="assignment_id"]').value = button.dataset.assignmentId;
                    setValue(editForm, 'application_type', button.dataset.applicationType);
                    setValue(editForm, 'application_action', button.dataset.applicationAction);
                    editControls.filterTemplates();
                    setValue(editForm, 'email_template_id', button.dataset.emailTemplateId);
                    setValue(editForm, 'status', button.dataset.status);
                    editControls.updateActionFields();
                    setValue(editForm, 'send_to_approver', button.dataset.sendToApprover);
                    setValue(editForm, 'send_to_another_approver', button.dataset.sendToAnotherApprover);
                    setValue(editForm, 'send_to_employee', button.dataset.sendToEmployee);
                });
            });

            @if ($errors->any() && old('assignment_form') === 'add')
                setValue(addForm, 'application_type', @json(old('application_type')));
                setValue(addForm, 'application_action', @json(old('application_action')));
                addControls.filterTemplates();
                setValue(addForm, 'email_template_id', @json(old('email_template_id')));
                addControls.updateActionFields();
                setValue(addForm, 'send_to_approver', @json(old('send_to_approver')));
                setValue(addForm, 'send_to_another_approver', @json(old('send_to_another_approver')));
                setValue(addForm, 'send_to_employee', @json(old('send_to_employee')));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('add-assignment')).show();
            @endif

            @if ($errors->any() && old('assignment_form') === 'edit')
                editForm.action = baseUrl + '/' + encodeURIComponent(@json(old('assignment_id')));
                setValue(editForm, 'application_type', @json(old('application_type')));
                setValue(editForm, 'application_action', @json(old('application_action')));
                editControls.filterTemplates();
                setValue(editForm, 'email_template_id', @json(old('email_template_id')));
                setValue(editForm, 'status', @json(old('status')));
                editControls.updateActionFields();
                setValue(editForm, 'send_to_approver', @json(old('send_to_approver')));
                setValue(editForm, 'send_to_another_approver', @json(old('send_to_another_approver')));
                setValue(editForm, 'send_to_employee', @json(old('send_to_employee')));
                bootstrap.Modal.getOrCreateInstance(editModal).show();
            @endif
        })();
    </script>
@endpush
