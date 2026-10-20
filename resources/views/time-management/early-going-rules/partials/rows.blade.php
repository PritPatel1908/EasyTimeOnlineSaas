@forelse ($earlyGoingRules as $earlyGoingRule)
    <tr>
        <td><input id="early_going_rule_{{ $earlyGoingRule->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $earlyGoingRule->code }}</h6></td>
        <td class="text-center">{{ $earlyGoingRule->description ?: '—' }}</td>
        <td class="text-center">{{ $earlyGoingRule->ignore_early_going_minutes }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $earlyGoingRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $earlyGoingRule->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('EarlyGoingRule', 'read'))
                    <a href="{{ url('time-management/early-going-rules/'.$earlyGoingRule->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('EarlyGoingRule', 'write'))
                    <a href="{{ url('time-management/early-going-rules/'.$earlyGoingRule->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('EarlyGoingRule', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-early-going-rule-btn"
                        data-url="{{ url('time-management/early-going-rules/'.$earlyGoingRule->id) }}"
                        data-name="{{ $earlyGoingRule->code }}"
                        data-bs-toggle="modal" data-bs-target="#delete_early_going_rule_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="py-4 text-muted text-center">
            <i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No early going rules found.
        </td>
    </tr>
@endforelse
