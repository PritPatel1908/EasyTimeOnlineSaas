@forelse ($absentRules as $absentRule)
    <tr>
        <td><input id="absent_rule_{{ $absentRule->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $absentRule->code }}</h6></td>
        <td class="text-center">{{ $absentRule->description ?: '—' }}</td>
        <td class="text-center">{{ $absentRule->work_hr_less_than_minutes }}</td>
        <td class="text-center">{{ $absentRule->allow_single_punch ? 'Yes' : 'No' }}</td>
        <td class="text-center">{{ $absentRule->late_coming_minutes ?? '—' }}</td>
        <td class="text-center">{{ $absentRule->early_going_minutes ?? '—' }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $absentRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $absentRule->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('AbsentRule', 'read'))
                    <a href="{{ url('time-management/absent-rules/'.$absentRule->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('AbsentRule', 'write'))
                    <a href="{{ url('time-management/absent-rules/'.$absentRule->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('AbsentRule', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-absent-rule-btn"
                        data-url="{{ url('time-management/absent-rules/'.$absentRule->id) }}"
                        data-name="{{ $absentRule->code }}"
                        data-bs-toggle="modal" data-bs-target="#delete_absent_rule_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="py-4 text-muted text-center">
            <i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No absent rules found.
        </td>
    </tr>
@endforelse
