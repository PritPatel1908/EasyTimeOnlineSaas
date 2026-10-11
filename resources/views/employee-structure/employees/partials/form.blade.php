@php
    $value = fn(string $field, mixed $default = '') => old($field, data_get($employee, $field, $default));
    $allowLogin = filter_var($value('is_locked', false), FILTER_VALIDATE_BOOLEAN);
    $dateValue = fn(string $field): string => ($date = $value($field))
        ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d')
        : '';
    $profilePic = $value('profile_pic');
    $personalInfo = $employee?->personalInfo;
    $personalValue = fn(string $field, mixed $default = '') => old($field, data_get($personalInfo, $field, $default));
    $listValues = function (string $field) use ($personalInfo): array {
        $items = old($field, data_get($personalInfo, $field, []));
        if (is_string($items)) {
            $items = preg_split('/[\r\n,]+/', $items, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return array_values(
            array_filter(
                array_map(static fn($item): string => is_scalar($item) ? trim((string) $item) : '', (array) $items),
                static fn(string $item): bool => $item !== '',
            ),
        );
    };
    $emergencyRows = function () use ($personalInfo): array {
        $fields = ['emergency_name', 'emergency_number', 'emergency_address'];
        $values = [];

        foreach ($fields as $field) {
            $items = old($field, data_get($personalInfo, $field));
            if (is_string($items)) {
                $decoded = json_decode($items, true);
                $items = is_array($decoded) ? $decoded : ($items !== '' ? [$items] : []);
            }

            $values[$field] = array_map(
                static fn($item): string => is_scalar($item) ? trim((string) $item) : '',
                is_array($items) ? $items : [],
            );
        }

        $rowCount = 1;
        foreach ($values as $items) {
            $rowCount = max($rowCount, count($items));
        }
        $rows = [];

        for ($index = 0; $index < $rowCount; $index++) {
            $rows[] = array_combine(
                $fields,
                array_map(
                    static fn(string $field): string => $values[$field][$index] ?? '',
                    $fields,
                ),
            );
        }

        return $rows;
    };
    $address = $employee?->addresses?->firstWhere('is_current_address', true) ?? $employee?->addresses?->first();
    $addressValue = fn(string $field, mixed $default = '') => old($field, data_get($address, $field, $default));
    $educationRows = old('educations');
    if (!is_array($educationRows)) {
        $educationRows = $employee?->education?->map(fn($education) => $education->only(['education', 'year', 'university_name']))->all() ?? [];
    }
    $educationRows = $educationRows ?: [[]];
    $familyDetailRows = old('family_details');
    if (!is_array($familyDetailRows)) {
        $familyDetailRows = $employee?->family_details?->map(fn($familyDetail) => $familyDetail->only(['relation', 'name', 'dob', 'is_nominee', 'occupation']))->all() ?? [];
    }
    $familyDetailRows = $familyDetailRows ?: [[]];
    $experienceRows = old('experiences');
    if (!is_array($experienceRows)) {
        $experienceRows = $employee?->experiences?->map(fn($experience) => $experience->only(['previous_employeer', 'designation', 'join_date', 'left_date', 'ctc']))->all() ?? [];
    }
    $experienceRows = $experienceRows ?: [[]];
    $rowDateValue = fn($row, string $field): string => ($date = data_get($row, $field))
        ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d')
        : '';
    $profilePicUrl = $profilePic
        ? (\Illuminate\Support\Str::startsWith($profilePic, ['http://', 'https://'])
            ? $profilePic
            : tenant_asset($profilePic))
        : '';
    $selected = fn(string $field): array => array_map('strval', (array) $value($field, []));
    $selectedShiftIds = array_map('strval', (array) old('shift_ids', $employee?->shifts?->pluck('id')->all() ?? []));
    $shiftType = $value('shift_type');
    $singleSelectFields = ['category_id', 'designation_id'];
    $relationFields = [
        'location_id' => ['label' => 'Location', 'options' => $locations],
        'company_id' => ['label' => 'Company', 'options' => $companies],
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
        'grade_wise_leave_id' => 'Grade Wise Leave ID',
    ];
    $approvalFlowFields = [
        'shift_change_approval_flow_id' => 'Shift Change',
        'week_off_change_approval_flow_id' => 'Week Off Change',
        'week_off_swap_approval_flow_id' => 'Week Off Swap',
        'manual_punch_approval_flow_id' => 'Manual Punch',
        'manual_attendance_approval_flow_id' => 'Manual Attendance',
        'leave_approval_flow_id' => 'Leave',
        'short_leave_approval_flow_id' => 'Short Leave',
        'coff_approval_flow_id' => 'COFF',
        'od_approval_flow_id' => 'OD',
    ];
    $approvalFlowGroups = [
        'Attendance & Punch Approvals' => [
            'shift_change_approval_flow_id',
            'week_off_change_approval_flow_id',
            'week_off_swap_approval_flow_id',
            'manual_punch_approval_flow_id',
            'manual_attendance_approval_flow_id',
        ],
        'Leave & Other Approvals' => [
            'leave_approval_flow_id',
            'short_leave_approval_flow_id',
            'coff_approval_flow_id',
            'od_approval_flow_id',
        ],
    ];
    $timingRuleFields = [
        'late_coming_rule_id' => ['label' => 'Late Coming Rule', 'options' => $lateComingRules],
        'early_going_rule_id' => ['label' => 'Early Going Rule', 'options' => $earlyGoingRules],
        'half_day_rule_id' => ['label' => 'Half Day Rule', 'options' => $halfDayRules],
        'absent_rule_id' => ['label' => 'Absent Rule', 'options' => $absentRules],
        'overtime_rule_id' => ['label' => 'Overtime Rule', 'options' => $overtimeRules],
    ];
    $timingRuleGroups = [
        'Attendance Timing' => [
            'late_coming_rule_id',
            'early_going_rule_id',
            'half_day_rule_id',
            'absent_rule_id',
            'overtime_rule_id',
        ],
    ];
@endphp

<style>
    #employee-step-8-tab,
    #employee-step-8-tab:not(.active):not(:hover) {
        background-color: #f5f0ff !important;
        border-color: #e0d2ff !important;
    }

    #employee-step-9-tab,
    #employee-step-9-tab:not(.active):not(:hover) {
        background-color: #eef8f1 !important;
        border-color: #cfe9d7 !important;
    }

    #employee-step-10-tab,
    #employee-step-10-tab:not(.active):not(:hover) {
        background-color: #fff8e8 !important;
        border-color: #f2dfb0 !important;
    }

    #employee-step-11-tab,
    #employee-step-11-tab:not(.active):not(:hover) {
        background-color: #eaf2ff !important;
        border-color: #cbdcf7 !important;
    }

    #employee-step-8-tab:hover,
    #employee-step-8-tab.active {
        background-color: #7952cc !important;
        border-color: #7952cc !important;
        color: #fff !important;
    }

    #employee-step-9-tab:hover,
    #employee-step-9-tab.active {
        background-color: #388e5a !important;
        border-color: #388e5a !important;
        color: #fff !important;
    }

    #employee-step-10-tab:hover,
    #employee-step-10-tab.active {
        background-color: #b58422 !important;
        border-color: #b58422 !important;
        color: #fff !important;
    }

    #employee-step-11-tab:hover,
    #employee-step-11-tab.active {
        background-color: #3f6fb5 !important;
        border-color: #3f6fb5 !important;
        color: #fff !important;
    }

</style>

<div class="employee-form-wizard twitter-bs-wizard">
    <input type="hidden" name="user_type" value="{{ $value('user_type', 'employee') }}">
    <input type="hidden" name="status" value="{{ $value('status', 1) }}">
    <div class="wizard twitter-bs-wizard-nav mb-4">
        <ul class="nav nav-tabs form-tab justify-content-center mb-3" role="tablist">
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link active rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-1" id="employee-step-1-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-1" aria-selected="true">
                    <div class="step-icon"><i class="far fa-user"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-3" id="employee-step-3-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-3" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-building"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-6" id="employee-step-6-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-6" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-sitemap"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-7" id="employee-step-7-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-7" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-clock"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-8" id="employee-step-8-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-8" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-heart"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-9" id="employee-step-9-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-9" aria-label="Bank Details" title="Bank Details"
                    aria-selected="false">
                    <div class="step-icon"><i class="fas fa-university"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-10" id="employee-step-10-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-10" aria-label="Education & Experience"
                    title="Education & Experience" aria-selected="false">
                    <div class="step-icon"><i class="fas fa-graduation-cap"></i></div>
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
                    href="#employee-step-11" id="employee-step-11-tab" data-bs-toggle="tab" role="tab"
                    aria-controls="employee-step-11" aria-label="Family Details" title="Family Details"
                    aria-selected="false">
                    <div class="step-icon"><i class="fas fa-users"></i></div>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="employee-step-1" role="tabpanel"
                aria-labelledby="employee-step-1-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Employee Code *</label><input
                                    name="code" class="form-control" value="{{ $value('code') }}" required>
                                @error('code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Employee Card</label><input
                                    name="card" class="form-control" value="{{ $value('card') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">First Name *</label><input
                                    name="fname" class="form-control" value="{{ $value('fname') }}" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Middle Name</label><input
                                    name="mname" class="form-control" value="{{ $value('mname') }}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Last Name</label><input
                                    name="lname" class="form-control" value="{{ $value('lname') }}"></div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Login & Mobile Access</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label d-block" for="is_locked">Allow
                                    Login?</label>
                                <div class="form-check form-check-lg form-switch ps-0"><input type="hidden"
                                        name="is_locked" value="0"><input type="checkbox" name="is_locked"
                                        value="1" id="is_locked" class="form-check-input ms-0" role="switch"
                                        @checked($allowLogin)></div>
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label d-block"
                                    for="allow_mobile_login">Allow Mobile Login?</label>
                                <div class="form-check form-check-lg form-switch ps-0"><input type="hidden"
                                        name="allow_mobile_login" value="0"><input type="checkbox"
                                        name="allow_mobile_login" value="1" id="allow_mobile_login"
                                        class="form-check-input ms-0" role="switch" @checked($value('allow_mobile_login'))>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label d-block"
                                    for="allow_mobile_punch">Allow Mobile Punch?</label>
                                <div class="form-check form-check-lg form-switch ps-0"><input type="hidden"
                                        name="allow_mobile_punch" value="0"><input type="checkbox"
                                        name="allow_mobile_punch" value="1" id="allow_mobile_punch"
                                        class="form-check-input ms-0" role="switch" @checked($value('allow_mobile_punch'))>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 login-dependent-field @class(['d-none' => !$allowLogin])"><label
                                    class="form-label">Role</label><select name="role_id" class="form-control"
                                    @disabled(!$allowLogin)>
                                    <option value="">Select</option>
                                    @foreach ($roles as $option)
                                        <option value="{{ $option->id }}" @selected((string) $value('role_id') === (string) $option->id)>
                                            {{ $option->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 login-dependent-field @class(['d-none' => !$allowLogin])"><label
                                    class="form-label">Data Policy</label><select name="data_policy_id"
                                    class="form-control" @disabled(!$allowLogin)>
                                    <option value="">Select</option>
                                    @foreach ($dataPolicies as $option)
                                        <option value="{{ $option->id }}" @selected((string) $value('data_policy_id') === (string) $option->id)>
                                            {{ $option->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 login-dependent-field @class(['d-none' => !$allowLogin])"><label
                                    class="form-label">Password</label><input type="password" name="password"
                                    class="form-control" @disabled(!$allowLogin)><small class="text-muted">Leave
                                    blank to keep the current password.</small></div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Employment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3"><label class="form-label">Aadhar Card</label><input
                                    name="aadhar_number" class="form-control" value="{{ $value('aadhar_number') }}">
                            </div>
                            <div class="col-md-3 mb-3"><label class="form-label">UAN No</label><input
                                    name="uan_number" class="form-control" value="{{ $value('uan_number') }}"></div>
                            <div class="col-md-3 mb-3"><label class="form-label">ESIC No</label><input
                                    name="esic_number" class="form-control" value="{{ $value('esic_number') }}">
                            </div>
                            <div class="col-md-3 mb-3"><label class="form-label">PAN No</label><input
                                    name="pan_number" class="form-control" value="{{ $value('pan_number') }}"></div>
                            <div class="col-md-3 mb-3"><label class="form-label">Joining Date</label><input
                                    type="date" name="join_date" class="form-control"
                                    value="{{ $dateValue('join_date') }}"></div>
                            <div class="col-md-3 mb-3"><label class="form-label d-block" for="is_left">Is
                                    Left?</label>
                                <div class="form-check form-check-lg form-switch ps-0"><input type="hidden"
                                        name="is_left" value="0"><input type="checkbox" name="is_left"
                                        value="1" id="is_left" class="form-check-input ms-0" role="switch"
                                        @checked($value('left_date') || $value('left_reason'))></div>
                            </div>
                            <div class="col-md-3 mb-3 left-dependent-field @class(['d-none' => !$value('left_date') && !$value('left_reason')])"><label
                                    class="form-label">Left Date</label><input type="date" name="left_date"
                                    class="form-control" value="{{ $dateValue('left_date') }}"></div>
                            <div class="col-md-3 mb-3 left-dependent-field @class(['d-none' => !$value('left_date') && !$value('left_reason')])"><label
                                    class="form-label">Left Remarks</label><input name="left_reason"
                                    class="form-control" value="{{ $value('left_reason') }}"></div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Profile Picture</label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3">
                                    <div class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded-circle border border-dashed me-2 flex-shrink-0 text-dark frames overflow-hidden"
                                        style="width: 80px; height: 80px;">
                                        <img id="profile_pic_preview" src="{{ $profilePicUrl }}"
                                            data-original-src="{{ $profilePicUrl }}" alt="Profile preview"
                                            class="w-100 h-100 object-fit-cover{{ $profilePicUrl ? '' : ' d-none' }}">
                                        <i id="profile_pic_placeholder"
                                            class="ti ti-photo text-gray-3 fs-16{{ $profilePicUrl ? ' d-none' : '' }}"></i>
                                    </div>
                                    <div class="profile-upload">
                                        <div class="mb-2">
                                            <h6 class="mb-1">Profile Photo</h6>
                                            <p class="fs-12 mb-0">Recommended image size is 40px x 40px</p>
                                        </div>
                                        <div class="profile-uploader d-flex align-items-center">
                                            <div class="drag-upload-btn btn btn-sm btn-primary me-2">
                                                Upload
                                                <input type="file" name="profile_pic" id="profile_pic"
                                                    class="form-control image-sign"
                                                    accept="image/jpeg,image/png,image/webp">
                                            </div>
                                            <button type="button" id="profile_pic_cancel"
                                                class="btn btn-light btn-sm">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-6" role="tabpanel" aria-labelledby="employee-step-6-tab">
                @foreach ($approvalFlowGroups as $groupLabel => $fields)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ $groupLabel }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($fields as $field)
                                    @php($columnClass = $groupLabel === 'Leave & Other Approvals' ? 'col-md-3' : (in_array($field, ['manual_punch_approval_flow_id', 'manual_attendance_approval_flow_id'], true) ? 'col-md-6' : 'col-md-4'))
                                    <div class="{{ $columnClass }} mb-3">
                                        <label class="form-label"
                                            for="{{ $field }}">{{ $approvalFlowFields[$field] }} Approval
                                            Flow</label>
                                        <select id="{{ $field }}" name="{{ $field }}"
                                            class="form-control">
                                            <option value="">Select</option>
                                            @foreach ($approvalFlows as $approvalFlow)
                                                <option value="{{ $approvalFlow->id }}" @selected((string) $value($field) === (string) $approvalFlow->id)>
                                                    {{ $approvalFlow->approval_flow_code }}</option>
                                            @endforeach
                                        </select>
                                        @error($field)
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-7" role="tabpanel" aria-labelledby="employee-step-7-tab">
                @foreach ($timingRuleGroups as $groupLabel => $fields)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ $groupLabel }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($fields as $field)
                                    @php($rule = $timingRuleFields[$field])
                                    <div
                                        class="{{ in_array($field, ['late_coming_rule_id', 'early_going_rule_id'], true) ? 'col-md-6' : 'col-md-4' }} mb-3">
                                        <label class="form-label"
                                            for="{{ $field }}">{{ $rule['label'] }}</label>
                                        <select id="{{ $field }}" name="{{ $field }}"
                                            class="form-control">
                                            <option value="">Select</option>
                                            @foreach ($rule['options'] as $option)
                                                <option value="{{ $option->id }}" @selected((string) $value($field) === (string) $option->id)>
                                                    {{ $option->code }}{{ $option->description ? ' - ' . $option->description : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error($field)
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-8" role="tabpanel" aria-labelledby="employee-step-8-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label" for="email">Email</label><input
                                    type="email" id="email" name="email" class="form-control"
                                    maxlength="150" value="{{ $value('email') }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label" for="number">Phone</label><input
                                    id="number" name="number" class="form-control" maxlength="30"
                                    value="{{ $value('number') }}">
                                @error('number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label" for="gender">Gender</label><select
                                    id="gender" name="gender" class="form-control">
                                    <option value="">Select</option>
                                    @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $key => $label)
                                        <option value="{{ $key }}" @selected($value('gender') === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label" for="dob">Birth
                                    Date</label><input type="date" id="dob" name="dob"
                                    class="form-control" value="{{ $dateValue('dob') }}">
                                @error('dob')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="blood_group_id">Blood
                                    Group</label><select id="blood_group_id" name="blood_group_id"
                                    class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($bloodGroups as $bloodGroup)
                                        <option value="{{ $bloodGroup->id }}" @selected((string) $value('blood_group_id') === (string) $bloodGroup->id)>
                                            {{ $bloodGroup->name }}</option>
                                    @endforeach
                                </select>
                                @error('blood_group_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="height">Height</label><input
                                    type="number" step="0.01" min="0" max="300" id="height"
                                    name="height" class="form-control" value="{{ $personalValue('height') }}">
                                @error('height')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="weight">Weight</label><input
                                    type="number" step="0.01" min="0" max="500" id="weight"
                                    name="weight" class="form-control" value="{{ $personalValue('weight') }}">
                                @error('weight')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Languages & Hobbies</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="languages">Languages</label>
                                <div data-list-input data-field-label="Language" data-field-name="languages[]">
                                    @forelse($listValues('languages') as $language)
                                        <div class="input-group mb-2" data-list-item>
                                            <input @if ($loop->first) id="languages" @endif
                                                name="languages[]" class="form-control" maxlength="2000"
                                                value="{{ $language }}" aria-label="Language">
                                            <button type="button" class="btn btn-outline-danger"
                                                data-remove-item>Remove</button>
                                        </div>
                                    @empty
                                        <div class="input-group mb-2" data-list-item>
                                            <input id="languages" name="languages[]" class="form-control"
                                                maxlength="2000" aria-label="Language">
                                            <button type="button" class="btn btn-outline-danger"
                                                data-remove-item>Remove</button>
                                        </div>
                                    @endforelse
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-add-item>Add
                                        New</button>
                                </div>
                                @error('languages')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                                @foreach ($errors->getMessages() as $key => $messages)
                                    @if (str_starts_with($key, 'languages.'))
                                        @foreach ($messages as $message)
                                            <small class="text-danger d-block">{{ $message }}</small>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="hobbies">Hobbies</label>
                                <div data-list-input data-field-label="Hobby" data-field-name="hobbies[]">
                                    @forelse($listValues('hobbies') as $hobby)
                                        <div class="input-group mb-2" data-list-item>
                                            <input @if ($loop->first) id="hobbies" @endif
                                                name="hobbies[]" class="form-control" maxlength="2000"
                                                value="{{ $hobby }}" aria-label="Hobby">
                                            <button type="button" class="btn btn-outline-danger"
                                                data-remove-item>Remove</button>
                                        </div>
                                    @empty
                                        <div class="input-group mb-2" data-list-item>
                                            <input id="hobbies" name="hobbies[]" class="form-control"
                                                maxlength="2000" aria-label="Hobby">
                                            <button type="button" class="btn btn-outline-danger"
                                                data-remove-item>Remove</button>
                                        </div>
                                    @endforelse
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-add-item>Add
                                        New</button>
                                </div>
                                @error('hobbies')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                                @foreach ($errors->getMessages() as $key => $messages)
                                    @if (str_starts_with($key, 'hobbies.'))
                                        @foreach ($messages as $message)
                                            <small class="text-danger d-block">{{ $message }}</small>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Emergency Contact</h5>
                    </div>
                    <div class="card-body">
                        <div data-emergency-contacts>
                            <div data-emergency-rows>
                                @foreach ($emergencyRows() as $index => $row)
                                    <div class="row align-items-end mb-3" data-emergency-row>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label" for="emergency_name_{{ $index }}">Emergency
                                                Contact Name</label>
                                            <input id="emergency_name_{{ $index }}" name="emergency_name[]"
                                                class="form-control" value="{{ $row['emergency_name'] }}">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label" for="emergency_number_{{ $index }}">Emergency
                                                Contact Number</label>
                                            <input id="emergency_number_{{ $index }}" name="emergency_number[]"
                                                class="form-control" value="{{ $row['emergency_number'] }}">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label" for="emergency_address_{{ $index }}">Emergency
                                                Contact Address</label>
                                            <input id="emergency_address_{{ $index }}" name="emergency_address[]"
                                                class="form-control" value="{{ $row['emergency_address'] }}">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <button type="button" class="btn btn-outline-danger w-100"
                                                data-remove-emergency>Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <template data-emergency-template>
                                <div class="row align-items-end mb-3" data-emergency-row>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input name="emergency_name[]" class="form-control">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Emergency Contact Number</label>
                                        <input name="emergency_number[]" class="form-control">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Emergency Contact Address</label>
                                        <input name="emergency_address[]" class="form-control">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-danger w-100"
                                            data-remove-emergency>Remove</button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-add-emergency>Add New</button>
                        </div>
                        @foreach ($errors->getMessages() as $key => $messages)
                            @if (in_array($key, ['emergency_name', 'emergency_number', 'emergency_address'], true) ||
                                    str_starts_with($key, 'emergency_name.') ||
                                    str_starts_with($key, 'emergency_number.') ||
                                    str_starts_with($key, 'emergency_address.'))
                                @foreach ($messages as $message)
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label" for="flat_building">Flat /
                                    Building</label><input id="flat_building" name="flat_building"
                                    class="form-control" maxlength="255"
                                    value="{{ $addressValue('flat_building') }}">
                                @error('flat_building')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="house_no">House
                                    Number</label><input id="house_no" name="house_no" class="form-control"
                                    maxlength="255" value="{{ $addressValue('house_no') }}">
                                @error('house_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="flore">Floor</label><input
                                    id="flore" name="flore" class="form-control" maxlength="255"
                                    value="{{ $addressValue('flore') }}">
                                @error('flore')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="street">Street</label><input
                                    id="street" name="street" class="form-control" maxlength="255"
                                    value="{{ $addressValue('street') }}">
                                @error('street')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label"
                                    for="landmark">Landmark</label><input id="landmark" name="landmark"
                                    class="form-control" maxlength="255" value="{{ $addressValue('landmark') }}">
                                @error('landmark')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="city">City</label><input
                                    id="city" name="city" class="form-control" maxlength="255"
                                    value="{{ $addressValue('city') }}">
                                @error('city')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="pincode">Pincode</label><input
                                    id="pincode" name="pincode" class="form-control" maxlength="255"
                                    value="{{ $addressValue('pincode') }}">
                                @error('pincode')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="state">State</label><input
                                    id="state" name="state" class="form-control" maxlength="255"
                                    value="{{ $addressValue('state') }}">
                                @error('state')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3"><label class="form-label" for="country">Country</label><input
                                    id="country" name="country" class="form-control" maxlength="255"
                                    value="{{ $addressValue('country') }}">
                                @error('country')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-check-lg form-switch d-flex align-items-center gap-2 ps-0 mt-2">
                                    <input type="hidden" name="is_current_address" value="0">
                                    <input id="is_current_address" type="checkbox" name="is_current_address"
                                        value="1" class="form-check-input ms-0" role="switch"
                                        @checked($addressValue('is_current_address'))>
                                    <label class="form-check-label" for="is_current_address">This is my current address</label>
                                </div>
                                @error('is_current_address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="current-address-fields" @class(['d-none' => (bool) $addressValue('is_current_address')])>
                            <h6 class="mt-3 mb-3">Current Address</h6>
                            <div class="row">
                                @foreach ([
                                    'flat_building' => 'Flat / Building',
                                    'house_no' => 'House Number',
                                    'flore' => 'Floor',
                                    'street' => 'Street',
                                    'landmark' => 'Landmark',
                                    'city' => 'City',
                                    'pincode' => 'Pincode',
                                    'state' => 'State',
                                    'country' => 'Country',
                                ] as $field => $label)
                                    @php($currentField = 'current_' . $field)
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="{{ $currentField }}">{{ $label }}</label>
                                        <input id="{{ $currentField }}" name="{{ $currentField }}" class="form-control"
                                            maxlength="255" value="{{ $addressValue($currentField) }}">
                                        @error($currentField)
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-9" role="tabpanel" aria-labelledby="employee-step-9-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Bank Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ([
                                'bank_name' => 'Bank Name',
                                'bank_account_number' => 'Bank Account Number',
                                'bank_ifsc_code' => 'Bank IFSC Code',
                                'bank_account_branch_name' => 'Bank Account Branch Name',
                            ] as $field => $label)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                    <input id="{{ $field }}" name="{{ $field }}" class="form-control"
                                        maxlength="255" value="{{ $value($field) }}">
                                    @error($field)
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-10" role="tabpanel"
                aria-labelledby="employee-step-10-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Education</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="educations_submitted" value="1">
                        <div data-repeatable-rows="educations">
                            @foreach ($educationRows as $index => $row)
                                <div class="row align-items-end mb-3" data-repeatable-row>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Education</label>
                                        <input name="educations[{{ $index }}][education]" class="form-control"
                                            maxlength="255" value="{{ data_get($row, 'education') }}">
                                        @error("educations.$index.education")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Year</label>
                                        <input name="educations[{{ $index }}][year]" class="form-control"
                                            maxlength="20" value="{{ data_get($row, 'year') }}">
                                        @error("educations.$index.year")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">University Name</label>
                                        <input name="educations[{{ $index }}][university_name]"
                                            class="form-control" maxlength="255"
                                            value="{{ data_get($row, 'university_name') }}">
                                        @error("educations.$index.university_name")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-danger w-100"
                                            data-remove-row>Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template data-row-template="educations">
                            <div class="row align-items-end mb-3" data-repeatable-row>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Education</label>
                                    <input name="educations[__INDEX__][education]" class="form-control"
                                        maxlength="255">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Year</label>
                                    <input name="educations[__INDEX__][year]" class="form-control" maxlength="20">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">University Name</label>
                                    <input name="educations[__INDEX__][university_name]" class="form-control"
                                        maxlength="255">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="button" class="btn btn-outline-danger w-100"
                                        data-remove-row>Remove</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-row="educations">
                            Add New
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Experience</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="experiences_submitted" value="1">
                        <div data-repeatable-rows="experiences">
                            @foreach ($experienceRows as $index => $row)
                                <div class="row align-items-end mb-3" data-repeatable-row>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Previous Employer</label>
                                        <input name="experiences[{{ $index }}][previous_employeer]"
                                            class="form-control" maxlength="255"
                                            value="{{ data_get($row, 'previous_employeer') }}">
                                        @error("experiences.$index.previous_employeer")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Designation</label>
                                        <input name="experiences[{{ $index }}][designation]" class="form-control"
                                            maxlength="255" value="{{ data_get($row, 'designation') }}">
                                        @error("experiences.$index.designation")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Join Date</label>
                                        <input type="date" name="experiences[{{ $index }}][join_date]"
                                            class="form-control" value="{{ $rowDateValue($row, 'join_date') }}">
                                        @error("experiences.$index.join_date")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Left Date</label>
                                        <input type="date" name="experiences[{{ $index }}][left_date]"
                                            class="form-control" value="{{ $rowDateValue($row, 'left_date') }}">
                                        @error("experiences.$index.left_date")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-1 mb-2">
                                        <label class="form-label">CTC</label>
                                        <input type="number" name="experiences[{{ $index }}][ctc]"
                                            class="form-control" min="0" step="0.01"
                                            value="{{ data_get($row, 'ctc') }}">
                                        @error("experiences.$index.ctc")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-danger w-100"
                                            data-remove-row>Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template data-row-template="experiences">
                            <div class="row align-items-end mb-3" data-repeatable-row>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Previous Employer</label>
                                    <input name="experiences[__INDEX__][previous_employeer]" class="form-control"
                                        maxlength="255">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Designation</label>
                                    <input name="experiences[__INDEX__][designation]" class="form-control"
                                        maxlength="255">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Join Date</label>
                                    <input type="date" name="experiences[__INDEX__][join_date]"
                                        class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Left Date</label>
                                    <input type="date" name="experiences[__INDEX__][left_date]"
                                        class="form-control">
                                </div>
                                <div class="col-md-1 mb-2">
                                    <label class="form-label">CTC</label>
                                    <input type="number" name="experiences[__INDEX__][ctc]" class="form-control"
                                        min="0" step="0.01">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="button" class="btn btn-outline-danger w-100"
                                        data-remove-row>Remove</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-row="experiences">
                            Add New
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-11" role="tabpanel"
                aria-labelledby="employee-step-11-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Family Details</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="family_details_submitted" value="1">
                        <div data-repeatable-rows="family_details">
                            @foreach ($familyDetailRows as $index => $row)
                                <div class="row align-items-end mb-3" data-repeatable-row>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Relation</label>
                                        <input name="family_details[{{ $index }}][relation]" class="form-control"
                                            maxlength="255" value="{{ data_get($row, 'relation') }}">
                                        @error("family_details.$index.relation")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Name</label>
                                        <input name="family_details[{{ $index }}][name]" class="form-control"
                                            maxlength="255" value="{{ data_get($row, 'name') }}">
                                        @error("family_details.$index.name")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="family_details[{{ $index }}][dob]"
                                            class="form-control" value="{{ $rowDateValue($row, 'dob') }}">
                                        @error("family_details.$index.dob")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <input type="hidden" name="family_details[{{ $index }}][is_nominee]"
                                            value="0">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" id="family_details_{{ $index }}_is_nominee"
                                                name="family_details[{{ $index }}][is_nominee]"
                                                class="form-check-input" role="switch" value="1"
                                                @checked((bool) data_get($row, 'is_nominee'))>
                                            <label class="form-check-label"
                                                for="family_details_{{ $index }}_is_nominee">Nominee</label>
                                        </div>
                                        @error("family_details.$index.is_nominee")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Occupation</label>
                                        <input name="family_details[{{ $index }}][occupation]" class="form-control"
                                            maxlength="255" value="{{ data_get($row, 'occupation') }}">
                                        @error("family_details.$index.occupation")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-1 mb-2">
                                        <button type="button" class="btn btn-outline-danger w-100"
                                            data-remove-row>Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template data-row-template="family_details">
                            <div class="row align-items-end mb-3" data-repeatable-row>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Relation</label>
                                    <input name="family_details[__INDEX__][relation]" class="form-control"
                                        maxlength="255">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Name</label>
                                    <input name="family_details[__INDEX__][name]" class="form-control" maxlength="255">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="family_details[__INDEX__][dob]" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <input type="hidden" name="family_details[__INDEX__][is_nominee]" value="0">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" id="family_details___INDEX___is_nominee"
                                            name="family_details[__INDEX__][is_nominee]"
                                            class="form-check-input" role="switch" value="1">
                                        <label class="form-check-label"
                                            for="family_details___INDEX___is_nominee">Nominee</label>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Occupation</label>
                                    <input name="family_details[__INDEX__][occupation]" class="form-control"
                                        maxlength="255">
                                </div>
                                <div class="col-md-1 mb-2">
                                    <button type="button" class="btn btn-outline-danger w-100"
                                        data-remove-row>Remove</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-row="family_details">
                            Add New
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Previous</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </div>

            <div class="tab-pane fade" id="employee-step-3" role="tabpanel" aria-labelledby="employee-step-3-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Organization</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($relationFields as $field => $relation)
                                <div class="col-md-6 mb-3"><label
                                        class="form-label">{{ $relation['label'] }}</label><select
                                        name="{{ $field }}{{ in_array($field, $singleSelectFields, true) ? '' : '[]' }}"
                                        class="form-control" @unless (in_array($field, $singleSelectFields, true)) multiple @endunless>
                                        <option value="">Select</option>
                                        @foreach ($relation['options'] as $option)
                                            <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selected($field), true))>
                                                {{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            <div class="col-md-6 mb-3"><label class="form-label">Leave Group</label><select
                                    name="leave_group_id" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($leaveGroups as $leaveGroup)
                                        <option value="{{ $leaveGroup->id }}" @selected((string) $value('leave_group_id') === (string) $leaveGroup->id)>
                                            {{ $leaveGroup->code }}{{ $leaveGroup->description ? ' - ' . $leaveGroup->description : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Team</label><select name="team_id"
                                    class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected((string) $value('team_id') === (string) $team->id)>
                                            {{ $team->name }}{{ $team->code ? ' - ' . $team->code : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Canteen Facility</label><select
                                    name="canteen_facility_id" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($canteenFacilities as $canteenFacility)
                                        <option value="{{ $canteenFacility->id }}" @selected((string) $value('canteen_facility_id') === (string) $canteenFacility->id)>
                                            {{ $canteenFacility->name }}{{ $canteenFacility->code ? ' - ' . $canteenFacility->code : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shift Assignment</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label" for="shift_type">Shift
                                    Type</label><select id="shift_type" name="shift_type" class="form-control">
                                    <option value="">Select</option>
                                    @foreach (['auto' => 'Auto', 'fixed' => 'Fixed', 'rotational' => 'Rotational'] as $key => $label)
                                        <option value="{{ $key }}" @selected($shiftType === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('shift_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3 shift-selection-field @class(['d-none' => !in_array($shiftType, ['auto', 'fixed'], true)])"><label
                                    class="form-label" for="shift_ids">Shift</label><select id="shift_ids"
                                    name="shift_ids[]" class="form-control"
                                    @if ($shiftType === 'auto') multiple @endif>
                                    <option value="" disabled hidden @selected(!$selectedShiftIds)>Select
                                    </option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}" @selected(in_array((string) $shift->id, $selectedShiftIds, true))>
                                            {{ $shift->name }} ({{ $shift->code }})</option>
                                    @endforeach
                                </select>
                                @error('shift_ids')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3 shift-rotation-field @class(['d-none' => $shiftType !== 'rotational'])"><label
                                    class="form-label" for="shift_rotation_id">Shift Rotation</label><select
                                    id="shift_rotation_id" name="shift_rotation_id" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($shiftRotations as $rotation)
                                        <option value="{{ $rotation->id }}" @selected((string) $value('shift_rotation_id') === (string) $rotation->id)>
                                            {{ $rotation->code }}</option>
                                    @endforeach
                                </select>
                                @error('shift_rotation_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Login Policy</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 login-dependent-field @class(['d-none' => !$allowLogin])"><label
                                    class="form-label">Password Policy</label><select name="password_policy_id"
                                    class="form-control" @disabled(!$allowLogin)>
                                    <option value="">Select</option>
                                    @foreach ($passwordPolicies as $passwordPolicy)
                                        <option value="{{ $passwordPolicy->id }}" @selected((string) $value('password_policy_id') === (string) $passwordPolicy->id)>
                                            {{ $passwordPolicy->policy_name }}</option>
                                    @endforeach
                                </select></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-light previous">Back</button>
                    <button type="button" class="btn btn-primary next">Next</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wizardRoot = document.querySelector('.employee-form-wizard');
        if (!wizardRoot || typeof bootstrap === 'undefined') return;

        wizardRoot.querySelectorAll('[data-list-input]').forEach(function(list) {
            const addButton = list.querySelector('[data-add-item]');
            const fieldLabel = list.dataset.fieldLabel;

            addButton.addEventListener('click', function() {
                const item = document.createElement('div');
                item.className = 'input-group mb-2';
                item.dataset.listItem = '';

                const input = document.createElement('input');
                input.type = 'text';
                input.name = list.dataset.fieldName;
                input.className = 'form-control';
                input.maxLength = 2000;
                input.setAttribute('aria-label', fieldLabel);

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-outline-danger';
                removeButton.dataset.removeItem = '';
                removeButton.textContent = 'Remove';

                item.append(input, removeButton);
                list.insertBefore(item, addButton);
            });

            list.addEventListener('click', function(event) {
                const removeButton = event.target.closest('[data-remove-item]');
                if (removeButton) removeButton.closest('[data-list-item]').remove();
            });
        });

        wizardRoot.querySelectorAll('[data-emergency-contacts]').forEach(function(contacts) {
            const rows = contacts.querySelector('[data-emergency-rows]');
            const template = contacts.querySelector('[data-emergency-template]');

            contacts.querySelector('[data-add-emergency]').addEventListener('click', function() {
                rows.appendChild(template.content.cloneNode(true));
            });

            contacts.addEventListener('click', function(event) {
                const removeButton = event.target.closest('[data-remove-emergency]');
                if (removeButton) removeButton.closest('[data-emergency-row]').remove();
            });
        });

        wizardRoot.addEventListener('click', function(event) {
            const addButton = event.target.closest('[data-add-row]');
            if (addButton) {
                const key = addButton.dataset.addRow;
                const rows = wizardRoot.querySelector('[data-repeatable-rows="' + key + '"]');
                const template = wizardRoot.querySelector('[data-row-template="' + key + '"]');
                if (!rows || !template) return;

                const nextIndex = Array.from(rows.querySelectorAll('input[name]')).reduce(function(maximum, input) {
                    const match = input.name.match(/\[(\d+)\]/);
                    return match ? Math.max(maximum, Number(match[1])) : maximum;
                }, -1) + 1;
                const row = template.content.firstElementChild.cloneNode(true);
                row.querySelectorAll('[name]').forEach(function(input) {
                    input.name = input.name.replace('__INDEX__', String(nextIndex));
                });
                rows.appendChild(row);
                return;
            }

            const removeButton = event.target.closest('[data-remove-row]');
            if (removeButton) removeButton.closest('[data-repeatable-row]').remove();
        });

        const loginToggle = wizardRoot.querySelector('#is_locked');
        const loginFields = wizardRoot.querySelectorAll('.login-dependent-field');
        const updateLoginFields = function() {
            loginFields.forEach(function(field) {
                field.classList.toggle('d-none', !loginToggle.checked);
                field.querySelectorAll('input, select, textarea').forEach(function(control) {
                    control.disabled = !loginToggle.checked;
                });
            });
        };

        if (loginToggle) {
            loginToggle.addEventListener('change', updateLoginFields);
            updateLoginFields();
        }

        const currentAddressToggle = wizardRoot.querySelector('#is_current_address');
        const currentAddressFields = wizardRoot.querySelector('.current-address-fields');
        if (currentAddressToggle && currentAddressFields) {
            const updateCurrentAddressFields = function() {
                currentAddressFields.classList.toggle('d-none', currentAddressToggle.checked);
            };

            currentAddressToggle.addEventListener('change', updateCurrentAddressFields);
            updateCurrentAddressFields();
        }

        const leftToggle = wizardRoot.querySelector('#is_left');
        const leftFields = wizardRoot.querySelectorAll('.left-dependent-field');
        const updateLeftFields = function() {
            leftFields.forEach(function(field) {
                field.classList.toggle('d-none', !leftToggle.checked);
            });

            if (!leftToggle.checked) {
                leftFields.forEach(function(field) {
                    field.querySelectorAll('input').forEach(function(control) {
                        control.value = '';
                    });
                });
            }
        };

        if (leftToggle) {
            leftToggle.addEventListener('change', updateLeftFields);
            updateLeftFields();
        }

        const shiftTypeSelect = wizardRoot.querySelector('#shift_type');
        const shiftSelect = wizardRoot.querySelector('#shift_ids');
        const shiftSelectionField = wizardRoot.querySelector('.shift-selection-field');
        const shiftRotationSelect = wizardRoot.querySelector('#shift_rotation_id');
        const shiftRotationField = wizardRoot.querySelector('.shift-rotation-field');
        const updateShiftFields = function() {
            const shiftType = shiftTypeSelect.value;
            const showShifts = shiftType === 'auto' || shiftType === 'fixed';
            const showRotation = shiftType === 'rotational';

            shiftSelectionField.classList.toggle('d-none', !showShifts);
            shiftRotationField.classList.toggle('d-none', !showRotation);
            shiftSelect.disabled = !showShifts;
            shiftSelect.multiple = shiftType === 'auto';
            shiftSelect.required = showShifts;
            shiftRotationSelect.disabled = !showRotation;
            shiftRotationSelect.required = showRotation;

            if (shiftType === 'fixed') {
                let selectedOne = false;
                Array.from(shiftSelect.options).forEach(function(option) {
                    if (!option.selected) return;
                    if (selectedOne) option.selected = false;
                    selectedOne = true;
                });
            }
        };
        shiftTypeSelect.addEventListener('change', updateShiftFields);
        updateShiftFields();

        const profileInput = wizardRoot.querySelector('#profile_pic');
        const profilePreview = wizardRoot.querySelector('#profile_pic_preview');
        const profilePlaceholder = wizardRoot.querySelector('#profile_pic_placeholder');
        const profileCancel = wizardRoot.querySelector('#profile_pic_cancel');
        if (profileInput) {
            profileInput.addEventListener('change', function() {
                const file = profileInput.files[0];
                if (!file) return;

                profilePreview.src = URL.createObjectURL(file);
                profilePreview.classList.remove('d-none');
                profilePlaceholder.classList.add('d-none');
            });
        }
        if (profileCancel) {
            profileCancel.addEventListener('click', function() {
                profileInput.value = '';
                profilePreview.src = profilePreview.dataset.originalSrc || '';
                profilePreview.classList.toggle('d-none', !profilePreview.dataset.originalSrc);
                profilePlaceholder.classList.toggle('d-none', Boolean(profilePreview.dataset
                    .originalSrc));
            });
        }

        const getActiveLi = function() {
            const activeTab = wizardRoot.querySelector('.form-tab .active');
            return activeTab ? activeTab.closest('li') : null;
        };

        wizardRoot.querySelectorAll('.next').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const activeLi = getActiveLi();
                const nextLi = activeLi ? activeLi.nextElementSibling : null;
                const nextTab = nextLi ? nextLi.querySelector('a') : null;

                if (nextTab) {
                    bootstrap.Tab.getOrCreateInstance(nextTab).show();
                }
            });
        });

        wizardRoot.querySelectorAll('.previous').forEach(function(button) {
            button.addEventListener('click', function(event) {
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
