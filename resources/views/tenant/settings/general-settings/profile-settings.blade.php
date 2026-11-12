@extends('layout.mainlayout')

@section('content')
    @php
        $value = fn(string $field, mixed $default = '') => old($field, data_get($employee, $field, $default));
        $personalInfo = $employee->personalInfo;
        $address = $employee->addresses->firstWhere('is_current_address', true) ?? $employee->addresses->first();
        $addressValue = fn(string $field, mixed $default = '') => old($field, data_get($address, $field, $default));
        $isCurrentAddress = (bool) $addressValue('is_current_address', false);
        $listValues = function (string $field) use ($personalInfo): array {
            $items = old($field, data_get($personalInfo, $field, []));
            if (is_string($items)) {
                $decoded = json_decode($items, true);
                $items = is_array($decoded) ? $decoded : preg_split('/[\r\n,]+/', $items, -1, PREG_SPLIT_NO_EMPTY);
            }

            $items = array_values(array_filter(array_map(
                static fn($item): string => is_scalar($item) ? trim((string) $item) : '',
                (array) $items,
            )));

            return $items ?: [''];
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
                $values[$field] = (array) $items;
            }

            $rows = [];
            $rowCount = max(
                1,
                count($values['emergency_name']),
                count($values['emergency_number']),
                count($values['emergency_address']),
            );
            for ($index = 0; $index < $rowCount; $index++) {
                $rows[] = array_combine($fields, array_map(
                    static fn(string $field): string => (string) ($values[$field][$index] ?? ''),
                    $fields,
                ));
            }

            return $rows;
        };
        $rows = function (string $field, $relation, array $columns): array {
            $items = old($field);
            if (! is_array($items)) {
                $items = $relation->map(fn($item) => $item->only($columns))->all();
            }

            return $items ?: [[]];
        };
        $educationRows = $rows('educations', $employee->education, ['education', 'year', 'university_name']);
        $experienceRows = $rows('experiences', $employee->experiences, ['previous_employeer', 'designation', 'join_date', 'left_date', 'ctc']);
        $familyRows = $rows('family_details', $employee->family_details, ['relation', 'name', 'dob', 'is_nominee', 'occupation']);
        $dateValue = fn(string $field): string => ($date = $value($field))
            ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d')
            : '';
        $rowDate = fn($row, string $field): string => ($date = data_get($row, $field))
            ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d')
            : '';
        $gender = $value('gender');
    @endphp

    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                            <li class="breadcrumb-item">General Settings</li>
                            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
                <li class="nav-item"><a class="nav-link active" href="{{ url('profile-settings') }}"><i class="ti ti-settings me-2"></i>General Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('salary-settings') }}"><i class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('default-settings') }}"><i class="ti ti-server-cog me-2"></i>System Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('payment-gateways') }}"><i class="ti ti-settings-dollar me-2"></i>Financial Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('custom-css') }}"><i class="ti ti-settings-2 me-2"></i>Other Settings</a></li>
            </ul>

            <div class="row">
                <div class="col-xl-3 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column list-group settings-list">
                                <a href="{{ url('profile-settings') }}" class="d-inline-flex align-items-center rounded active py-2 px-3"><i class="ti ti-arrow-badge-right me-2"></i>Profile Settings</a>
                                <a href="{{ url('security-settings') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Security Settings</a>
                                <a href="{{ url('notification-settings') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Notifications</a>
                                {{-- <a href="{{ url('connected-apps') }}" class="d-inline-flex align-items-center rounded py-2 px-3">Connected Apps</a> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <form action="{{ url('profile-settings') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card">
                            <div class="card-body">
                                <div class="border-bottom mb-3 pb-3">
                                    <h4>Profile Settings</h4>
                                    <p class="text-muted mb-0">Update your own employee profile and personal details.</p>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                    </div>
                                @endif

                                @php
                                    $profileTabs = [
                                        'personal' => 'Personal',
                                        'health' => 'Health & Lifestyle',
                                        'emergency' => 'Emergency Contact',
                                        'address' => 'Address',
                                        'bank' => 'Bank Details',
                                        'education' => 'Education',
                                        'experience' => 'Experience',
                                        'family' => 'Family Details',
                                    ];
                                @endphp
                                <div class="profile-tabs-wrap mb-3">
                                    <div class="nav nav-tabs profile-tabs" role="tablist" aria-label="Profile sections">
                                        @foreach ($profileTabs as $tab => $label)
                                            <button type="button" class="nav-link{{ $loop->first ? ' active' : '' }}" id="profile-tab-{{ $tab }}" role="tab" aria-controls="profile-panel-{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-profile-tab="{{ $tab }}">{{ $label }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="profile-tab-panels">
                                    <section class="profile-tab-panel" id="profile-panel-personal" role="tabpanel" aria-labelledby="profile-tab-personal" data-profile-panel="personal">
                                <div class="card border mb-3">
                                    <div class="card-header"><h5 class="mb-0">Employee &amp; Personal Information</h5></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Employee Card</label>
                                                <input class="form-control" value="{{ $employee->card }}" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="{{ $employee->email }}" autocomplete="email" readonly>
                                                <small class="text-muted">Change your email from <a href="{{ url('settings/security-settings') }}">Security Settings</a> and verify the new address.</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                                <input type="text" name="fname" class="form-control" value="{{ $value('fname') }}" required maxlength="100" autocomplete="given-name">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Middle Name</label>
                                                <input type="text" name="mname" class="form-control" value="{{ $value('mname') }}" maxlength="100">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" name="lname" class="form-control" value="{{ $value('lname') }}" maxlength="100" autocomplete="family-name">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" name="number" class="form-control" value="{{ $value('number') }}" maxlength="30" autocomplete="tel">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Gender</label>
                                                <select name="gender" class="form-select select">
                                                    <option value="">Select gender</option>
                                                    @if ($gender && ! in_array(strtolower($gender), ['male', 'female', 'other'], true))
                                                        <option value="{{ $gender }}" selected>{{ $gender }}</option>
                                                    @endif
                                                    @foreach (['Male', 'Female', 'Other'] as $option)
                                                        <option value="{{ $option }}" @selected(strtolower((string) $gender) === strtolower($option))>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Birth Date</label>
                                                <input type="date" name="dob" class="form-control" value="{{ $dateValue('dob') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Blood Group</label>
                                                <select name="blood_group_id" class="form-select select">
                                                    <option value="">Select blood group</option>
                                                    @foreach ($bloodGroups as $bloodGroup)
                                                        <option value="{{ $bloodGroup->id }}" @selected((string) $value('blood_group_id') === (string) $bloodGroup->id)>{{ $bloodGroup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @foreach (['aadhar_number' => 'Aadhar Card', 'uan_number' => 'UAN No', 'esic_number' => 'ESIC No', 'pan_number' => 'PAN No'] as $field => $label)
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">{{ $label }}</label>
                                                    <input type="text" name="{{ $field }}" class="form-control" value="{{ $value($field) }}" maxlength="255">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-health" role="tabpanel" aria-labelledby="profile-tab-health" data-profile-panel="health" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header"><h5 class="mb-0">Health, Languages &amp; Hobbies</h5></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3"><label class="form-label">Height (cm)</label><input type="number" name="height" class="form-control" min="0" max="300" step="0.1" value="{{ old('height', data_get($personalInfo, 'height')) }}"></div>
                                            <div class="col-md-6 mb-3"><label class="form-label">Weight (kg)</label><input type="number" name="weight" class="form-control" min="0" max="500" step="0.1" value="{{ old('weight', data_get($personalInfo, 'weight')) }}"></div>
                                        </div>
                                        @foreach (['languages' => 'Languages', 'hobbies' => 'Hobbies'] as $field => $label)
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label mb-0">{{ $label }}</label>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="{{ $field }}">Add {{ rtrim($label, 's') }}</button>
                                                </div>
                                                <div class="profile-collection" data-collection="{{ $field }}">
                                                    @foreach ($listValues($field) as $item)
                                                        <div class="profile-repeat-row input-group mb-2">
                                                            <input type="text" name="{{ $field }}[]" class="form-control" value="{{ $item }}" maxlength="255" aria-label="{{ $label }}">
                                                            <button type="button" class="btn btn-outline-danger" data-remove-row>Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-emergency" role="tabpanel" aria-labelledby="profile-tab-emergency" data-profile-panel="emergency" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header"><h5 class="mb-0">Emergency Contact</h5></div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-end mb-2"><button type="button" class="btn btn-sm btn-outline-primary" data-add-row="emergency">Add Contact</button></div>
                                        <div class="profile-collection" data-collection="emergency">
                                            @foreach ($emergencyRows() as $index => $row)
                                                <div class="profile-repeat-row row align-items-end mb-2">
                                                    <div class="col-md-4 mb-2"><label class="form-label">Contact Name</label><input name="emergency_name[]" class="form-control" value="{{ $row['emergency_name'] }}" maxlength="255"></div>
                                                    <div class="col-md-3 mb-2"><label class="form-label">Contact Number</label><input type="tel" name="emergency_number[]" class="form-control" value="{{ $row['emergency_number'] }}" maxlength="30"></div>
                                                    <div class="col-md-4 mb-2"><label class="form-label">Contact Address</label><input name="emergency_address[]" class="form-control" value="{{ $row['emergency_address'] }}" maxlength="1000"></div>
                                                    <div class="col-md-1 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove emergency contact">&times;</button></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-address" role="tabpanel" aria-labelledby="profile-tab-address" data-profile-panel="address" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header"><h5 class="mb-0">Address</h5></div>
                                    <div class="card-body">
                                        <h6 class="mb-3">Permanent Address</h6>
                                        <div class="row">
                                            @foreach (['flat_building' => 'Flat / Building', 'house_no' => 'House Number', 'flore' => 'Floor', 'street' => 'Street', 'landmark' => 'Landmark', 'city' => 'City', 'pincode' => 'Pincode', 'state' => 'State', 'country' => 'Country'] as $field => $label)
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label" for="profile-{{ $field }}">{{ $label }}</label>
                                                    <input id="profile-{{ $field }}" type="text" name="{{ $field }}" class="form-control" value="{{ $addressValue($field) }}" maxlength="{{ $field === 'pincode' ? 20 : 255 }}">
                                                </div>
                                            @endforeach
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
                                                    <input type="hidden" name="is_current_address" value="0">
                                                    <input id="profile-is-current-address" type="checkbox" name="is_current_address" value="1" class="form-check-input ms-0" role="switch" @checked($isCurrentAddress)>
                                                    <label class="form-check-label" for="profile-is-current-address">This is my current address</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-current-address-fields{{ $isCurrentAddress ? ' d-none' : '' }}">
                                            <hr>
                                            <h6 class="mb-3">Current Address</h6>
                                            <div class="row">
                                                @foreach (['current_flat_building' => 'Flat / Building', 'current_house_no' => 'House Number', 'current_flore' => 'Floor', 'current_street' => 'Street', 'current_landmark' => 'Landmark', 'current_city' => 'City', 'current_pincode' => 'Pincode', 'current_state' => 'State', 'current_country' => 'Country'] as $field => $label)
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label" for="profile-{{ $field }}">{{ $label }}</label>
                                                        <input id="profile-{{ $field }}" type="text" name="{{ $field }}" class="form-control" value="{{ $addressValue($field) }}" maxlength="{{ $field === 'current_pincode' ? 20 : 255 }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-bank" role="tabpanel" aria-labelledby="profile-tab-bank" data-profile-panel="bank" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header"><h5 class="mb-0">Bank Details</h5></div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach (['bank_name' => 'Bank Name', 'bank_account_number' => 'Bank Account Number', 'bank_ifsc_code' => 'Bank IFSC Code', 'bank_account_branch_name' => 'Bank Account Branch Name'] as $field => $label)
                                                <div class="col-md-6 mb-3"><label class="form-label">{{ $label }}</label><input type="text" name="{{ $field }}" class="form-control" value="{{ $value($field) }}" maxlength="255"></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-education" role="tabpanel" aria-labelledby="profile-tab-education" data-profile-panel="education" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Education</h5>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="educations">Add Education</button>
                                    </div>
                                    <div class="card-body profile-collection" data-collection="educations">
                                        @foreach ($educationRows as $index => $row)
                                            <div class="profile-repeat-row row align-items-end mb-3">
                                                <div class="col-md-4 mb-2"><label class="form-label">Education</label><input name="educations[{{ $index }}][education]" class="form-control" value="{{ data_get($row, 'education') }}" maxlength="255"></div>
                                                <div class="col-md-2 mb-2"><label class="form-label">Year</label><input name="educations[{{ $index }}][year]" class="form-control" value="{{ data_get($row, 'year') }}" maxlength="20"></div>
                                                <div class="col-md-4 mb-2"><label class="form-label">University Name</label><input name="educations[{{ $index }}][university_name]" class="form-control" value="{{ data_get($row, 'university_name') }}" maxlength="255"></div>
                                                <div class="col-md-2 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row>Remove</button></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-experience" role="tabpanel" aria-labelledby="profile-tab-experience" data-profile-panel="experience" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Experience</h5>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="experiences">Add Experience</button>
                                    </div>
                                    <div class="card-body profile-collection" data-collection="experiences">
                                        @foreach ($experienceRows as $index => $row)
                                            <div class="profile-repeat-row row align-items-end mb-3">
                                                <div class="col-md-3 mb-2"><label class="form-label">Previous Employer</label><input name="experiences[{{ $index }}][previous_employeer]" class="form-control" value="{{ data_get($row, 'previous_employeer') }}" maxlength="255"></div>
                                                <div class="col-md-3 mb-2"><label class="form-label">Designation</label><input name="experiences[{{ $index }}][designation]" class="form-control" value="{{ data_get($row, 'designation') }}" maxlength="255"></div>
                                                <div class="col-md-2 mb-2"><label class="form-label">Join Date</label><input type="date" name="experiences[{{ $index }}][join_date]" class="form-control" value="{{ $rowDate($row, 'join_date') }}"></div>
                                                <div class="col-md-2 mb-2"><label class="form-label">Left Date</label><input type="date" name="experiences[{{ $index }}][left_date]" class="form-control" value="{{ $rowDate($row, 'left_date') }}"></div>
                                                <div class="col-md-1 mb-2"><label class="form-label">CTC</label><input type="number" min="0" step="0.01" name="experiences[{{ $index }}][ctc]" class="form-control" value="{{ data_get($row, 'ctc') }}"></div>
                                                <div class="col-md-1 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove experience">&times;</button></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                    </section>
                                    <section class="profile-tab-panel" id="profile-panel-family" role="tabpanel" aria-labelledby="profile-tab-family" data-profile-panel="family" hidden>
                                <div class="card border mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Family Details</h5>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="family_details">Add Family Member</button>
                                    </div>
                                    <div class="card-body profile-collection" data-collection="family_details">
                                        @foreach ($familyRows as $index => $row)
                                            <div class="profile-repeat-row row align-items-end mb-3">
                                                <div class="col-md-2 mb-2"><label class="form-label">Relation</label><input name="family_details[{{ $index }}][relation]" class="form-control" value="{{ data_get($row, 'relation') }}" maxlength="255"></div>
                                                <div class="col-md-3 mb-2"><label class="form-label">Name</label><input name="family_details[{{ $index }}][name]" class="form-control" value="{{ data_get($row, 'name') }}" maxlength="255"></div>
                                                <div class="col-md-2 mb-2"><label class="form-label">Date of Birth</label><input type="date" name="family_details[{{ $index }}][dob]" class="form-control" value="{{ $rowDate($row, 'dob') }}"></div>
                                                <div class="col-md-2 mb-2"><label class="form-label">Occupation</label><input name="family_details[{{ $index }}][occupation]" class="form-control" value="{{ data_get($row, 'occupation') }}" maxlength="255"></div>
                                                <div class="col-md-1 mb-2"><label class="form-label d-block">Nominee</label><input type="checkbox" name="family_details[{{ $index }}][is_nominee]" value="1" class="form-check-input" @checked((bool) data_get($row, 'is_nominee'))></div>
                                                <div class="col-md-2 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row>Remove</button></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                    </section>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                    <button type="button" class="btn btn-outline-light border" data-profile-previous>Previous</button>
                                    <div class="d-flex gap-2">
                                        <a href="{{ url('profile-settings') }}" class="btn btn-outline-light border">Cancel</a>
                                        <button type="button" class="btn btn-outline-primary" data-profile-next>Next</button>
                                        <button type="submit" class="btn btn-primary">Save Profile</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <template id="template-languages"><div class="profile-repeat-row input-group mb-2"><input type="text" name="languages[]" class="form-control" maxlength="255" aria-label="Language"><button type="button" class="btn btn-outline-danger" data-remove-row>Remove</button></div></template>
                        <template id="template-hobbies"><div class="profile-repeat-row input-group mb-2"><input type="text" name="hobbies[]" class="form-control" maxlength="255" aria-label="Hobby"><button type="button" class="btn btn-outline-danger" data-remove-row>Remove</button></div></template>
                        <template id="template-emergency"><div class="profile-repeat-row row align-items-end mb-2"><div class="col-md-4 mb-2"><label class="form-label">Contact Name</label><input name="emergency_name[]" class="form-control" maxlength="255"></div><div class="col-md-3 mb-2"><label class="form-label">Contact Number</label><input type="tel" name="emergency_number[]" class="form-control" maxlength="30"></div><div class="col-md-4 mb-2"><label class="form-label">Contact Address</label><input name="emergency_address[]" class="form-control" maxlength="1000"></div><div class="col-md-1 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove emergency contact">&times;</button></div></div></template>
                        <template id="template-educations"><div class="profile-repeat-row row align-items-end mb-3"><div class="col-md-4 mb-2"><label class="form-label">Education</label><input name="educations[0][education]" class="form-control" maxlength="255"></div><div class="col-md-2 mb-2"><label class="form-label">Year</label><input name="educations[0][year]" class="form-control" maxlength="20"></div><div class="col-md-4 mb-2"><label class="form-label">University Name</label><input name="educations[0][university_name]" class="form-control" maxlength="255"></div><div class="col-md-2 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row>Remove</button></div></div></template>
                        <template id="template-experiences"><div class="profile-repeat-row row align-items-end mb-3"><div class="col-md-3 mb-2"><label class="form-label">Previous Employer</label><input name="experiences[0][previous_employeer]" class="form-control" maxlength="255"></div><div class="col-md-3 mb-2"><label class="form-label">Designation</label><input name="experiences[0][designation]" class="form-control" maxlength="255"></div><div class="col-md-2 mb-2"><label class="form-label">Join Date</label><input type="date" name="experiences[0][join_date]" class="form-control"></div><div class="col-md-2 mb-2"><label class="form-label">Left Date</label><input type="date" name="experiences[0][left_date]" class="form-control"></div><div class="col-md-1 mb-2"><label class="form-label">CTC</label><input type="number" min="0" step="0.01" name="experiences[0][ctc]" class="form-control"></div><div class="col-md-1 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove experience">&times;</button></div></div></template>
                        <template id="template-family_details"><div class="profile-repeat-row row align-items-end mb-3"><div class="col-md-2 mb-2"><label class="form-label">Relation</label><input name="family_details[0][relation]" class="form-control" maxlength="255"></div><div class="col-md-3 mb-2"><label class="form-label">Name</label><input name="family_details[0][name]" class="form-control" maxlength="255"></div><div class="col-md-2 mb-2"><label class="form-label">Date of Birth</label><input type="date" name="family_details[0][dob]" class="form-control"></div><div class="col-md-2 mb-2"><label class="form-label">Occupation</label><input name="family_details[0][occupation]" class="form-control" maxlength="255"></div><div class="col-md-1 mb-2"><label class="form-label d-block">Nominee</label><input type="checkbox" name="family_details[0][is_nominee]" value="1" class="form-check-input"></div><div class="col-md-2 mb-2"><button type="button" class="btn btn-outline-danger w-100" data-remove-row>Remove</button></div></div></template>
                    </form>
                </div>
            </div>
        </div>
        @include('partials.footer')
    </div>

    <script>
        document.addEventListener('click', function (event) {
            const tabButton = event.target.closest('[data-profile-tab]');
            if (tabButton) {
                setActiveProfileTab(tabButton.dataset.profileTab);
                return;
            }

            if (event.target.closest('[data-profile-previous]')) {
                moveProfileTab(-1);
                return;
            }

            if (event.target.closest('[data-profile-next]')) {
                moveProfileTab(1);
                return;
            }

            const addButton = event.target.closest('[data-add-row]');
            if (addButton) {
                const collection = addButton.dataset.addRow;
                const target = document.querySelector('[data-collection="' + collection + '"]');
                const template = document.getElementById('template-' + collection);
                if (target && template) {
                    const row = template.content.firstElementChild.cloneNode(true);
                    let index = 0;
                    target.querySelectorAll('[name]').forEach(function (input) {
                        const match = input.name.match(/\[(\d+)\]/);
                        if (match) {
                            index = Math.max(index, Number(match[1]) + 1);
                        }
                    });
                    row.querySelectorAll('[name]').forEach(function (input) {
                        input.name = input.name.replace(/\[\d+\]/g, '[' + index + ']');
                    });
                    target.appendChild(row);
                }
                return;
            }

            const removeButton = event.target.closest('[data-remove-row]');
            if (removeButton) {
                removeButton.closest('.profile-repeat-row').remove();
            }
        });

        const profileTabButtons = Array.from(document.querySelectorAll('[data-profile-tab]'));
        const profileTabPanels = Array.from(document.querySelectorAll('[data-profile-panel]'));
        let activeProfileTab = profileTabButtons[0]?.dataset.profileTab;

        const currentAddressToggle = document.getElementById('profile-is-current-address');
        const currentAddressFields = document.querySelector('.profile-current-address-fields');
        if (currentAddressToggle && currentAddressFields) {
            currentAddressToggle.addEventListener('change', function () {
                currentAddressFields.classList.toggle('d-none', currentAddressToggle.checked);
            });
        }

        function setActiveProfileTab(tab) {
            activeProfileTab = tab;
            profileTabButtons.forEach(function (button) {
                const active = button.dataset.profileTab === tab;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
                button.tabIndex = active ? 0 : -1;
            });
            profileTabPanels.forEach(function (panel) {
                const active = panel.dataset.profilePanel === tab;
                panel.hidden = !active;
                panel.classList.toggle('active', active);
            });
            const index = profileTabButtons.findIndex(function (button) {
                return button.dataset.profileTab === tab;
            });
            document.querySelector('[data-profile-previous]').disabled = index <= 0;
            document.querySelector('[data-profile-next]').hidden = index >= profileTabButtons.length - 1;
        }

        function moveProfileTab(direction) {
            const currentIndex = profileTabButtons.findIndex(function (button) {
                return button.dataset.profileTab === activeProfileTab;
            });
            const nextButton = profileTabButtons[currentIndex + direction];
            if (nextButton) {
                setActiveProfileTab(nextButton.dataset.profileTab);
            }
        }

        const firstErrorField = @json(array_key_first($errors->messages()));
        if (firstErrorField) {
            const errorSection = firstErrorField.split('.')[0].split('[')[0];
            const errorTab = {
                height: 'health',
                weight: 'health',
                languages: 'health',
                hobbies: 'health',
                emergency_name: 'emergency',
                emergency_number: 'emergency',
                emergency_address: 'emergency',
                flat_building: 'address',
                house_no: 'address',
                flore: 'address',
                street: 'address',
                landmark: 'address',
                country: 'address',
                state: 'address',
                city: 'address',
                pincode: 'address',
                current_flat_building: 'address',
                current_house_no: 'address',
                current_flore: 'address',
                current_street: 'address',
                current_landmark: 'address',
                current_country: 'address',
                current_state: 'address',
                current_city: 'address',
                current_pincode: 'address',
                bank_name: 'bank',
                bank_account_number: 'bank',
                bank_ifsc_code: 'bank',
                bank_account_branch_name: 'bank',
                educations: 'education',
                experiences: 'experience',
                family_details: 'family',
            }[errorSection] || 'personal';
            setActiveProfileTab(errorTab);
        } else {
            setActiveProfileTab(activeProfileTab);
        }
    </script>
    <style>
        .profile-tabs-wrap {
            overflow: visible;
        }

        .profile-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            border-bottom: 1px solid #dee2e6;
        }

        .profile-tabs .nav-link {
            flex: 0 1 auto;
            white-space: normal;
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .profile-tab-panel .card {
            max-width: 100%;
        }

        .profile-tab-panel .row {
            --bs-gutter-x: 1rem;
        }

        .profile-tab-panel input,
        .profile-tab-panel select {
            min-width: 0;
        }

        .profile-tab-panel[hidden] {
            display: none !important;
        }

        @media (max-width: 575.98px) {
            .profile-tabs .nav-link {
                flex: 1 1 calc(50% - 0.35rem);
                text-align: center;
            }
        }
    </style>
@endsection
