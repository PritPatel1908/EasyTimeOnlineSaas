@forelse ($shifts as $shift)
    @php
        $toIds = static function ($value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
            }

            return array_values(array_filter((array) $value, static fn ($id) => is_int($id) || ctype_digit((string) $id)));
        };
        $locationNames = \App\Models\Tenant\Location::query()->whereIn('id', $toIds($shift->location_id))->orderBy('name')->pluck('name')->join(', ');
        $companyNames = \App\Models\Tenant\Company::query()->whereIn('id', $toIds($shift->company_id))->orderBy('name')->pluck('name')->join(', ');
    @endphp
    <tr>
        <td><input id="shift_{{ $shift->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $shift->name }}</h6></td>
        <td class="text-center">{{ $shift->code }}</td>
        <td class="text-center">{{ $companyNames ?: '—' }}</td>
        <td class="text-center">{{ $locationNames ?: '—' }}</td>
        <td class="text-center">
            {{ $shift->in_time?->format('H:i') ?? '—' }} – {{ $shift->out_time?->format('H:i') ?? '—' }}
        </td>
        <td class="text-center">{{ $shift->is_night_shift ? 'Yes' : 'No' }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $shift->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $shift->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('Shift', 'read'))
                    <a href="{{ url('time-management/shifts/'.$shift->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('Shift', 'write'))
                    <a href="{{ url('time-management/shifts/'.$shift->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('Shift', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-shift-btn"
                        data-url="{{ url('time-management/shifts/'.$shift->id) }}"
                        data-name="{{ $shift->name }}"
                        data-bs-toggle="modal" data-bs-target="#delete_shift_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="py-4 text-muted text-center">
            <i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No shifts found.
        </td>
    </tr>
@endforelse
