@forelse ($balances as $balance)
    @php
        $locationNames = collect((array) $balance->location_id)->map(fn($id) => $locationsById[$id]->name ?? null)->filter()->join(', ');
        $companyNames = collect((array) $balance->company_id)->map(fn($id) => $companiesById[$id]->name ?? null)->filter()->join(', ');
    @endphp
    <tr>
        <td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
        <td><h6 class="fw-medium fs-14 mb-0">{{ $balance->Users->pluck('name')->join(', ') ?: '—' }}</h6><small class="text-muted">{{ $balance->Users->pluck('code')->filter()->join(', ') }}</small></td>
        <td>{{ $balance->leave_type?->code }} — {{ $balance->leave_type?->description }}</td>
        <td>{{ number_format((float) $balance->balance, 2) }}</td>
        <td>{{ $balance->date?->format('d-m-Y h:i A') ?? '—' }}</td>
        <td>{{ $locationNames ?: '—' }}</td>
        <td>{{ $companyNames ?: '—' }}</td>
        <td>{{ \Illuminate\Support\Str::limit($balance->reason ?? '—', 55) }}</td>
        <td><div class="action-icon d-inline-flex">
            @if (\App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'read'))
                <a href="{{ url('employee-structure/manual-leave-balances/'.$balance->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
            @endif
            @if (\App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'write'))
                <a href="{{ url('employee-structure/manual-leave-balances/'.$balance->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
            @endif
            @if (\App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'delete'))
                <a href="javascript:void(0);" class="text-danger delete-manual-leave-balance-btn" data-url="{{ url('employee-structure/manual-leave-balances/'.$balance->id) }}" data-name="Balance #{{ $balance->id }}" data-bs-toggle="modal" data-bs-target="#delete_manual_leave_balance_modal" title="Delete"><i class="ti ti-trash"></i></a>
            @endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ti ti-calendar-off fs-24 d-block mb-2"></i>No manual leave balances found.</td></tr>
@endforelse
