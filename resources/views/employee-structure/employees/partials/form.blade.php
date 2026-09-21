@php
    $value = fn (string $field, mixed $default = '') => old($field, data_get($employee, $field, $default));
    $dateValue = fn (string $field): string => ($date = $value($field)) ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d') : '';
    $selected = fn (string $field): array => array_map('strval', (array) $value($field, []));
    $relationFields = [
        'company_id' => ['label' => 'Company', 'options' => $companies],
        'location_id' => ['label' => 'Location', 'options' => $locations],
        'department_id' => ['label' => 'Department', 'options' => $departments],
        'sub_department_id' => ['label' => 'Sub Department', 'options' => $subDepartments],
        'category_id' => ['label' => 'Category', 'options' => $categories],
        'sub_category_id' => ['label' => 'Sub Category', 'options' => $subCategories],
        'designation_id' => ['label' => 'Designation', 'options' => $designations],
        'grade_id' => ['label' => 'Grade', 'options' => $grades],
        'unit_id' => ['label' => 'Unit', 'options' => $units],
        'bus_route_id' => ['label' => 'Bus Route', 'options' => $busRoutes],
    ];
    $approvalFields = [
        'shift_change_id' => 'Shift Change ID',
        'week_off_change_id' => 'Week Off Change ID',
        'shift_change_approval_flow_id' => 'Shift Change Approval Flow ID',
        'week_off_change_approval_flow_id' => 'Week Off Change Approval Flow ID',
        'week_off_swap_approval_flow_id' => 'Week Off Swap Approval Flow ID',
        'manual_punch_approval_flow_id' => 'Manual Punch Approval Flow ID',
        'manual_attendance_approval_flow_id' => 'Manual Attendance Approval Flow ID',
        'leave_approval_flow_id' => 'Leave Approval Flow ID',
        'short_leave_approval_flow_id' => 'Short Leave Approval Flow ID',
        'grade_wise_leave_id' => 'Grade Wise Leave ID',
        'coff_approval_flow_id' => 'COFF Approval Flow ID',
        'od_approval_flow_id' => 'OD Approval Flow ID',
    ];
@endphp

<div class="employee-form-wizard twitter-bs-wizard">
    <div class="wizard twitter-bs-wizard-nav mb-4">
        <ul class="nav nav-tabs form-tab justify-content-center mb-3" role="tablist">
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link active rounded mx-auto d-flex align-items-center justify-content-center" href="#employee-step-1" id="employee-step-1-tab" data-bs-toggle="tab" role="tab" aria-controls="employee-step-1" aria-selected="true">
                    <div class="step-icon"><i class="far fa-user"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center" href="#employee-step-2" id="employee-step-2-tab" data-bs-toggle="tab" role="tab" aria-controls="employee-step-2" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-building"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center" href="#employee-step-3" id="employee-step-3-tab" data-bs-toggle="tab" role="tab" aria-controls="employee-step-3" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="employee-step-1" role="tabpanel" aria-labelledby="employee-step-1-tab">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Identity & Contact</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Employee Code *</label><input name="code" class="form-control" value="{{ $value('code') }}" required>@error('code')<small class="text-danger">{{ $message }}</small>@enderror</div>
                            <div class="col-md-6 mb-3"><label class="form-label">Card</label><input name="card" class="form-control" value="{{ $value('card') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">First Name *</label><input name="fname" class="form-control" value="{{ $value('fname') }}" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Middle Name</label><input name="mname" class="form-control" value="{{ $value('mname') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Last Name</label><input name="lname" class="form-control" value="{{ $value('lname') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $value('email') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Phone</label><input name="number" class="form-control" value="{{ $value('number') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Gender</label><select name="gender" class="form-control"><option value="">Select</option>@foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $key => $label)<option value="{{ $key }}" @selected($value('gender') === $key)>{{ $label }}</option>@endforeach</select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="{{ $dateValue('dob') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Profile Picture</label><input name="profile_pic" class="form-control" value="{{ $value('profile_pic') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Aadhar Number</label><input name="aadhar_number" class="form-control" value="{{ $value('aadhar_number') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">UAN Number</label><input name="uan_number" class="form-control" value="{{ $value('uan_number') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">ESIC Number</label><input name="esic_number" class="form-control" value="{{ $value('esic_number') }}"></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-2" role="tabpanel" aria-labelledby="employee-step-2-tab">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Organization & Access</h5></div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($relationFields as $field => $relation)<div class="col-md-6 mb-3"><label class="form-label">{{ $relation['label'] }}</label><select name="{{ $field }}[]" class="form-control" multiple>@foreach($relation['options'] as $option)<option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selected($field), true))>{{ $option->name }}</option>@endforeach</select></div>@endforeach
                            <div class="col-md-4 mb-3"><label class="form-label">Data Policy</label><select name="data_policy_id" class="form-control"><option value="">Select</option>@foreach($dataPolicies as $option)<option value="{{ $option->id }}" @selected((string) $value('data_policy_id') === (string) $option->id)>{{ $option->name }}</option>@endforeach</select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Role</label><select name="role_id" class="form-control"><option value="">Select</option>@foreach($roles as $option)<option value="{{ $option->id }}" @selected((string) $value('role_id') === (string) $option->id)>{{ $option->name }}</option>@endforeach</select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">User Type *</label><select name="user_type" class="form-control" required><option value="employee" @selected($value('user_type', 'employee') === 'employee')>Employee</option><option value="guest" @selected($value('user_type') === 'guest')>Guest</option></select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control"><small class="text-muted">Leave blank to keep the current password.</small></div>
                            <div class="col-md-4 mb-3"><label class="form-label">DMS User ID</label><input name="dms_user_id" class="form-control" value="{{ $value('dms_user_id') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Shift Type</label><input name="shift_type" class="form-control" value="{{ $value('shift_type') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Password Policy ID</label><input type="number" name="password_policy_id" class="form-control" value="{{ $value('password_policy_id') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Shift Rotation ID</label><input type="number" name="shift_rotation_id" class="form-control" value="{{ $value('shift_rotation_id') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Approval Flow ID</label><input type="number" name="approval_flow_id" class="form-control" value="{{ $value('approval_flow_id') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Login Attempts</label><input type="number" name="login_attempts" class="form-control" value="{{ $value('login_attempts', 0) }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Password Changed At</label><input type="datetime-local" name="password_changed_at" class="form-control" value="{{ $value('password_changed_at') ? \Illuminate\Support\Carbon::parse($value('password_changed_at'))->format('Y-m-d\\TH:i') : '' }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Status *</label><select name="status" class="form-control" required><option value="1" @selected((string) $value('status', 1) === '1')>Active</option><option value="0" @selected((string) $value('status') === '0')>Inactive</option></select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Locked</label><div class="form-check mt-2"><input type="hidden" name="is_locked" value="0"><input type="checkbox" name="is_locked" value="1" class="form-check-input" @checked($value('is_locked'))><label class="form-check-label">User is locked</label></div></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Phone Login</label><div class="form-check mt-2"><input type="hidden" name="allow_phone_login" value="0"><input type="checkbox" name="allow_phone_login" value="1" class="form-check-input" @checked($value('allow_phone_login'))><label class="form-check-label">Allow phone login</label></div></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Back</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-3" role="tabpanel" aria-labelledby="employee-step-3-tab">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Employment & Attendance</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Join Date</label><input type="date" name="join_date" class="form-control" value="{{ $dateValue('join_date') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Rejoin Date</label><input type="date" name="rejoin_date" class="form-control" value="{{ $dateValue('rejoin_date') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Rejoin Reason</label><input name="rejoin_reason" class="form-control" value="{{ $value('rejoin_reason') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Left Date</label><input type="date" name="left_date" class="form-control" value="{{ $dateValue('left_date') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Left Reason</label><input name="left_reason" class="form-control" value="{{ $value('left_reason') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Inactive Date</label><input type="date" name="inactive_date" class="form-control" value="{{ $dateValue('inactive_date') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Inactive Days</label><input type="number" name="inactive_days" class="form-control" value="{{ $value('inactive_days', 10) }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Reference Name</label><input name="reference_name" class="form-control" value="{{ $value('reference_name') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Reference Number</label><input name="reference_number" class="form-control" value="{{ $value('reference_number') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Short Leave Minutes</label><input type="number" name="short_leave_minutes" class="form-control" value="{{ $value('short_leave_minutes') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Last Check ID</label><input type="number" name="last_check_id" class="form-control" value="{{ $value('last_check_id') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Last Check Date</label><input type="date" name="last_check_date" class="form-control" value="{{ $dateValue('last_check_date') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Last Active At</label><input type="datetime-local" name="last_active_at" class="form-control" value="{{ $value('last_active_at') ? \Illuminate\Support\Carbon::parse($value('last_active_at'))->format('Y-m-d\\TH:i') : '' }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Last Login At</label><input type="datetime-local" name="last_login_at" class="form-control" value="{{ $value('last_login_at') ? \Illuminate\Support\Carbon::parse($value('last_login_at'))->format('Y-m-d\\TH:i') : '' }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Shift Status</label><div class="form-check mt-2"><input type="hidden" name="shift_status" value="0"><input type="checkbox" name="shift_status" value="1" class="form-check-input" @checked($value('shift_status'))><label class="form-check-label">Shift is active</label></div></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Inactive</label><div class="form-check mt-2"><input type="hidden" name="is_inactive" value="0"><input type="checkbox" name="is_inactive" value="1" class="form-check-input" @checked($value('is_inactive'))><label class="form-check-label">Mark as inactive</label></div></div>
                        </div>
                        <hr>
                        <div class="row">
                            @foreach($approvalFields as $field => $label)<div class="col-md-4 mb-3"><label class="form-label">{{ $label }}</label><input type="number" name="{{ $field }}" class="form-control" value="{{ $value($field) }}"></div>@endforeach
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wizardRoot = document.querySelector('.employee-form-wizard');
        if (!wizardRoot || typeof bootstrap === 'undefined') return;

        const getActiveLi = function () {
            const activeTab = wizardRoot.querySelector('.form-tab .active');
            return activeTab ? activeTab.closest('li') : null;
        };

        wizardRoot.querySelectorAll('.next').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const activeLi = getActiveLi();
                const nextLi = activeLi ? activeLi.nextElementSibling : null;
                const nextTab = nextLi ? nextLi.querySelector('a') : null;

                if (nextTab) {
                    bootstrap.Tab.getOrCreateInstance(nextTab).show();
                }
            });
        });

        wizardRoot.querySelectorAll('.previous').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const activeLi = getActiveLi();
                const prevLi = activeLi ? activeLi.previousElementSibling : null;
                const prevTab = prevLi ? prevLi.querySelector('a') : null;

                if (prevTab) {
                    bootstrap.Tab.getOrCreateInstance(prevTab).show();
                }
            });
        });
    });
</script>
