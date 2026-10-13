@php
    $selectedUsers = old("details.{$index}.users", $detail['users'] ?? []);
    $selectedUsers = array_map('strval', (array) $selectedUsers);
@endphp
<div class="approval-level-row border rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Level <span class="approval-level-number"></span></h6>
        <button type="button" class="btn btn-sm btn-outline-danger remove-approval-level"><i class="ti ti-trash me-1"></i>Remove</button>
    </div>
    <div class="row align-items-end">
        <div class="col-12 mb-3">
            <label class="form-label" for="approval_users_{{ $index }}">Approvers <span class="text-danger">*</span></label>
            <select id="approval_users_{{ $index }}" name="details[{{ $index }}][users][]" class="form-control approval-approver-select" placeholder="Select" multiple required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(in_array((string) $user->id, $selectedUsers, true))>{{ $user->name }}{{ $user->email ? ' — '.$user->email : '' }}{{ $user->code ? ' ('.$user->code.')' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
