@forelse ($machines as $machine)
    <tr>
        <td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
        <td><h6 class="fw-medium fs-14">{{ $machine->name }}</h6></td>
        <td>{{ $machine->machine_serial_number ?? '—' }}</td>
        <td>{{ $machine->machine_ip ?? '—' }}</td>
        <td>{{ $machine->company?->name ?? '—' }}</td>
        <td>{{ $machine->location?->name ?? '—' }}</td>
        <td>{{ $machine->area?->name ?? '—' }}</td>
        <td>{{ ucfirst($machine->network_status) }}</td>
        <td>
            <span class="badge {{ $machine->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i><span>{{ $machine->status === 1 ? 'Active' : 'Inactive' }}</span>
            </span>
        </td>
        <td><div class="action-icon d-inline-flex">
            @if (\App\Support\TenantPermissions::userCan('Machine', 'read'))<a href="{{ url('company-structure/machines/'.$machine->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>@endif
            @if (\App\Support\TenantPermissions::userCan('Machine', 'write'))<a href="{{ url('company-structure/machines/'.$machine->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>@endif
            @if (\App\Support\TenantPermissions::userCan('Machine', 'delete'))<a href="javascript:void(0);" class="text-danger delete-machine-btn" data-url="{{ url('company-structure/machines/'.$machine->id) }}" data-name="{{ $machine->name }}" data-bs-toggle="modal" data-bs-target="#delete_machine_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="10" class="py-4 text-muted"><div class="d-flex flex-column align-items-center justify-content-center text-center"><i class="ti ti-device-desktop-off fs-24 mb-2"></i><span>No machines found.</span></div></td></tr>
@endforelse
