@php
    $formatTime = static fn ($value): string => $value ? substr((string) $value, 0, 5) : '—';
    $formatDuration = static function ($minutes): string {
        $totalMinutes = (int) round((float) $minutes);

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    };
@endphp
@forelse ($overtimeRules as $overtimeRule)
    <tr>
        <td><input id="overtime_rule_{{ $overtimeRule->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $overtimeRule->code }}</h6></td>
        <td class="text-center">{{ $overtimeRule->description ?: '—' }}</td>
        <td class="text-center">{{ $formatTime($overtimeRule->ignore_early_come_minutes) }}</td>
        <td class="text-center">{{ $formatTime($overtimeRule->ignore_late_come_minutes) }}</td>
        <td class="text-center">
            {{ $formatDuration($overtimeRule->ignore_late_going ? $overtimeRule->ignore_late_going_overtime : $overtimeRule->ignore_overtime) }}
        </td>
        <td class="text-center">{{ $overtimeRule->overtime_slabs_count }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $overtimeRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $overtimeRule->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('OvertimeRule', 'read'))
                    <a href="{{ url('time-management/overtime-rules/'.$overtimeRule->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('OvertimeRule', 'write'))
                    <a href="{{ url('time-management/overtime-rules/'.$overtimeRule->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('OvertimeRule', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-overtime-rule-btn"
                        data-url="{{ url('time-management/overtime-rules/'.$overtimeRule->id) }}"
                        data-name="{{ $overtimeRule->code }}"
                        data-bs-toggle="modal" data-bs-target="#delete_overtime_rule_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="py-4 text-muted text-center">
            <i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No overtime rules found.
        </td>
    </tr>
@endforelse
