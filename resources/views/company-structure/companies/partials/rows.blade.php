@forelse ($companies as $company)
    <tr>
        <td class="text-center">
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td class="text-center">
            <h6 class="fw-medium fs-14">{{ $company->name }}</h6>
        </td>
        <td class="text-center">{{ $company->code }}</td>
        <td class="text-center">{{ $company->email ?? '—' }}</td>
        <td class="text-center">{{ $company->locations->pluck('name')->join(', ') ?: '—' }}</td>
        <td class="text-center">
            <span class="badge {{ $company->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>
                <span class="company-status-label">{{ $company->status === 1 ? 'Active' : 'Inactive' }}</span>
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('Company', 'read'))
                <a href="{{ url('company-structure/companies/'.$company->id) }}" class="me-2" title="View">
                    <i class="ti ti-eye"></i>
                </a>
                @endif
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
        <td colspan="7" class="py-4 text-muted">
            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                <i class="ti ti-building-off fs-24 mb-2"></i>
                <span>No companies found.</span>
            </div>
        </td>
    </tr>
@endforelse
