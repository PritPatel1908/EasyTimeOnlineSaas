@forelse ($companies as $company)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>
            <h6 class="fw-medium fs-14">{{ $company->name }}</h6>
        </td>
        <td>{{ $company->code }}</td>
        <td>{{ $company->email ?? '—' }}</td>
        <td>{{ $company->locations->pluck('name')->join(', ') ?: '—' }}</td>
        <td>
            <span class="badge {{ $company->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>
                <span class="company-status-label">{{ $company->status === 1 ? 'Active' : 'Inactive' }}</span>
            </span>
        </td>
        <td>
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('Company', 'write'))
                <a href="{{ url('company-structure/companies/'.$company->id.'/edit') }}"
                    class="me-2" title="Edit">
                    <i class="ti ti-edit"></i>
                </a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('Company', 'delete'))
                <a href="javascript:void(0);"
                    class="text-danger delete-company-btn"
                    data-id="{{ $company->id }}"
                    data-url="{{ url('company-structure/companies/'.$company->id) }}"
                    data-name="{{ $company->name }}"
                    data-bs-toggle="modal"
                    data-bs-target="#delete_company_modal"
                    title="Delete">
                    <i class="ti ti-trash"></i>
                </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-4 text-muted">
            <i class="ti ti-building-off fs-24 d-block mb-2"></i>
            No companies found.
        </td>
    </tr>
@endforelse
