@forelse($approvalFlows as $approvalFlow)
    <tr>
        <td><input id="approval_flow_{{ $approvalFlow->id }}" class="form-check-input" type="checkbox"></td>
        <td><h6 class="fw-medium fs-14 mb-0">{{ $approvalFlow->approval_flow_code }}</h6></td>
        <td><span class="badge {{ $approvalFlow->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $approvalFlow->status === 1 ? 'Active' : 'Inactive' }}</span></td>
        <td>{{ $approvalFlow->approval_flow_details_count }}</td>
        <td>
            @forelse($approvalFlow->approval_flow_details->sortBy('level') as $detail)
                <div><span class="fw-medium">Level {{ $detail->level }}:</span> {{ $detail->users->pluck('name')->join(', ') ?: 'No approvers' }}</div>
            @empty
                <span class="text-muted">No approvers assigned</span>
            @endforelse
        </td>
        <td class="text-center"><div class="action-icon d-inline-flex">
            @if(\App\Support\TenantPermissions::userCan('ApprovalFlow', 'read'))<a class="me-2" href="{{ url('employee-structure/approval-flows/'.$approvalFlow->id) }}" title="View"><i class="ti ti-eye"></i></a>@endif
            @if(\App\Support\TenantPermissions::userCan('ApprovalFlow', 'write'))<a class="me-2" href="{{ url('employee-structure/approval-flows/'.$approvalFlow->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif
            @if(\App\Support\TenantPermissions::userCan('ApprovalFlow', 'delete'))<a href="javascript:void(0)" class="text-danger delete-approval-flow-btn" data-url="{{ url('employee-structure/approval-flows/'.$approvalFlow->id) }}" data-code="{{ $approvalFlow->approval_flow_code }}" data-bs-toggle="modal" data-bs-target="#delete_approval_flow_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="6" class="py-4 text-center text-muted">No approval flows found.</td></tr>
@endforelse
