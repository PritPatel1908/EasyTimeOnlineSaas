@forelse ($lateComingRules as $lateComingRule)
    <tr>
        <td><input id="late_coming_rule_{{ $lateComingRule->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $lateComingRule->code }}</h6></td>
        <td class="text-center">{{ $lateComingRule->description ?: '—' }}</td>
        <td class="text-center">{{ $lateComingRule->ignore_late_coming_minutes }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $lateComingRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $lateComingRule->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('LateComingRule', 'read'))
                    <a href="{{ url('time-management/late-coming-rules/'.$lateComingRule->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('LateComingRule', 'write'))
                    <a href="{{ url('time-management/late-coming-rules/'.$lateComingRule->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('LateComingRule', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-late-coming-rule-btn"
                        data-url="{{ url('time-management/late-coming-rules/'.$lateComingRule->id) }}"
                        data-name="{{ $lateComingRule->code }}"
                        data-bs-toggle="modal" data-bs-target="#delete_late_coming_rule_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="py-4 text-muted text-center">
            <i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No late coming rules found.
        </td>
    </tr>
@endforelse
