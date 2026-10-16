@if ($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Balance Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                <select id="leave_type_id" name="leave_type_id" class="form-control select @error('leave_type_id') is-invalid @enderror" required>
                    <option value="">Select leave type</option>
                    @foreach ($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}" @selected((string) old('leave_type_id', $balance?->leave_type_id) === (string) $leaveType->id)>{{ $leaveType->code }} — {{ $leaveType->description }}</option>
                    @endforeach
                </select>
                <x-validation-error field="leave_type_id" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="balance">Balance <span class="text-danger">*</span></label>
                <input id="balance" name="balance" type="number" step="0.01" min="-99999999.99" max="99999999.99" class="form-control @error('balance') is-invalid @enderror" value="{{ old('balance', $balance?->balance) }}" required>
                <x-validation-error field="balance" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="date">Date &amp; Time <span class="text-danger">*</span></label>
                <input id="date" name="date" type="datetime-local" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $balance?->date?->format('Y-m-d\TH:i')) }}" required>
                <x-validation-error field="date" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="users">Employees <span class="text-danger">*</span></label>
                @php($selectedUsers = (array) old('users', $balance?->Users->modelKeys() ?? []))
                <select id="users" name="users[]" class="form-control select @error('users') is-invalid @enderror" multiple required>
                    @foreach ($users as $employee)
                        <option value="{{ $employee->id }}" @selected(in_array($employee->id, $selectedUsers))>{{ $employee->code }} — {{ $employee->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Select one or more employees to receive this manual balance.</small>
                <x-validation-error field="users" />
                <x-validation-error field="users.0" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Organization Scope</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="location_ids">Locations</label>
                @php($selectedLocations = (array) old('location_ids', $balance?->location_id ?? []))
                <select id="location_ids" name="location_ids[]" class="form-control select @error('location_ids') is-invalid @enderror" multiple>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }} ({{ $location->code }})</option>
                    @endforeach
                </select>
                <x-validation-error field="location_ids" />
                <x-validation-error field="location_ids.0" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="company_ids">Companies</label>
                @php($selectedCompanies = (array) old('company_ids', $balance?->company_id ?? []))
                <select id="company_ids" name="company_ids[]" class="form-control select @error('company_ids') is-invalid @enderror" multiple>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array($company->id, $selectedCompanies))>{{ $company->name }} ({{ $company->code }})</option>
                    @endforeach
                </select>
                <x-validation-error field="company_ids" />
                <x-validation-error field="company_ids.0" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="reason">Reason</label>
                <textarea id="reason" name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="Enter a reason for this adjustment">{{ old('reason', $balance?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>
