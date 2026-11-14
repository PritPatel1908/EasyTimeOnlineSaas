@forelse($customReports as $customReport)
<tr>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $customReport->report_name }}</h6></td>
    <td>{{ $customReport->from_date->format('Y-m-d') }}</td>
    <td>{{ $customReport->to_date->format('Y-m-d') }}</td>
    <td>{{ ucfirst($customReport->report_orientation) }}</td>
    <td><span class="badge {{ $customReport->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $customReport->status === 1 ? 'Active' : 'Inactive' }}</span></td>
    <td class="text-center">
        <div class="action-icon d-inline-flex">
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'read'))
                <a class="me-2" href="javascript:void(0)" title="Generate Report" aria-label="Generate Report"><i class="ti ti-file-analytics"></i></a>
                <a class="me-2" href="{{ url('employee-structure/custom-reports/'.$customReport->id) }}" title="View"><i class="ti ti-eye"></i></a>
            @endif
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'write'))
                <a class="me-2" href="{{ url('employee-structure/custom-reports/'.$customReport->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>
            @endif
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'delete'))
                <a href="javascript:void(0)" class="text-danger delete-custom-report-btn" data-url="{{ url('employee-structure/custom-reports/'.$customReport->id) }}" data-name="{{ $customReport->report_name }}" data-bs-toggle="modal" data-bs-target="#delete_custom_report_modal" title="Delete"><i class="ti ti-trash"></i></a>
            @endif
        </div>
    </td>
</tr>
@empty
<tr><td colspan="6" class="py-4 text-center text-muted">No custom reports found.</td></tr>
@endforelse
