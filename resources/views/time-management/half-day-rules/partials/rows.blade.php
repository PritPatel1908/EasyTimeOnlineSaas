@forelse ($halfDayRules as $halfDayRule)
    <tr>
        <td><input id="half_day_rule_{{ $halfDayRule->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $halfDayRule->code }}</h6></td>
        <td class="text-center">{{ $halfDayRule->description ?: '—' }}</td>
        <td class="text-center">{{ $halfDayRule->work_hr_less_than_minutes ?? '—' }}</td>
        <td class="text-center">{{ $halfDayRule->single_punch_allowed ? 'Yes' : 'No' }}</td>
        <td class="text-center">{{ $halfDayRule->late_coming_minutes ?? '—' }}</td>
        <td class="text-center">{{ $halfDayRule->early_going_minutes ?? '—' }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $halfDayRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $halfDayRule->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('HalfDayRule', 'read'))
                    <a href="{{ url('time-management/half-day-rules/'.$halfDayRule->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('HalfDayRule', 'write'))
                    <a href="{{ url('time-management/half-day-rules/'.$halfDayRule->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('HalfDayRule', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-half-day-rule-btn"
                        data-url="{{ url('time-management/half-day-rules/'.$halfDayRule->id) }}"
                        data-name="{{ $halfDayRule->code }}"
                        data-bs-toggle="modal" data-bs-target="#delete_half_day_rule_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="py-4 text-muted text-center">
            <i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No half-day rules found.
        </td>
    </tr>
@endforelse
