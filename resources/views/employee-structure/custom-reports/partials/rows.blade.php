@forelse($customReports as $customReport)
<tr>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $customReport->report_name }}</h6></td>
    <td>
        @if($customReport->date_range_preset === 'custom')
            {{ $customReport->from_date?->format('Y-m-d') }} – {{ $customReport->to_date?->format('Y-m-d') }}
        @else
            {{ ucwords(str_replace('_', ' ', $customReport->date_range_preset)) }} (dynamic)
        @endif
    </td>
    <td>{{ ucfirst($customReport->report_orientation) }}</td>
    <td><span class="badge {{ $customReport->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $customReport->status === 1 ? 'Active' : 'Inactive' }}</span></td>
    <td class="text-center">
        <div class="action-icon d-inline-flex">
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'read'))
                <a class="me-2" href="javascript:void(0)" title="Generate Report" aria-label="Generate Report"><i class="ti ti-file-analytics"></i></a>
                <a class="me-2" href="{{ url('employee-structure/custom-reports/'.$customReport->id) }}" title="View"><i class="ti ti-eye"></i></a>
            @endif
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'create'))
                @include('partials.clone-action', ['url' => url('employee-structure/custom-reports/create?clone='.$customReport->id)])
            @endif
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'write'))
                <a class="me-2" href="{{ url('employee-structure/custom-reports/'.$customReport->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>
                <form class="d-inline" method="POST" action="{{ url('employee-structure/custom-reports/'.$customReport->id.'/toggle-status') }}">
                    @csrf
                    <button type="submit" class="me-2" title="{{ $customReport->status === 1 ? 'Close report' : 'Reopen report' }}" aria-label="{{ $customReport->status === 1 ? 'Close report '.$customReport->report_name : 'Reopen report '.$customReport->report_name }}">
                        @if($customReport->status === 1)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="m15 9-6 6"></path>
                                <path d="m9 9 6 6"></path>
                            </svg>
                        @else
                            <i class="ti ti-refresh text-success" aria-hidden="true"></i>
                        @endif
                    </button>
                </form>
            @endif
            @if(\App\Support\TenantPermissions::userCan('CustomReport', 'delete'))
                <a href="javascript:void(0)" class="text-danger delete-custom-report-btn" data-url="{{ url('employee-structure/custom-reports/'.$customReport->id) }}" data-name="{{ $customReport->report_name }}" data-bs-toggle="modal" data-bs-target="#delete_custom_report_modal" title="Delete"><i class="ti ti-trash"></i></a>
            @endif
        </div>
    </td>
</tr>
@empty
<tr><td colspan="5" class="py-4 text-center text-muted">No custom reports found.</td></tr>
@endforelse
