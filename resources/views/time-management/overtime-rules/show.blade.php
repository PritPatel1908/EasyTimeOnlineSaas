@extends('layout.mainlayout')
@section('content')
@php
    $formatTime = static fn ($value): string => $value ? substr((string) $value, 0, 5) : '—';
    $formatDuration = static function ($minutes): string {
        $totalMinutes = (int) round((float) $minutes);

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    };
@endphp
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Overtime Rule</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/overtime-rules') }}">Overtime Rules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $overtimeRule->code }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('OvertimeRule', 'write'))
                    <a href="{{ url('time-management/overtime-rules/'.$overtimeRule->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/overtime-rules') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-cog me-2 text-primary"></i>Rule Details</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Rule Code</small><span>{{ $overtimeRule->code }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Status</small>
                        <span class="badge {{ (int) $overtimeRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                            <i class="ti ti-point-filled me-1"></i>{{ (int) $overtimeRule->status === 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-md-12 mb-3"><small class="text-muted d-block">Description</small><span>{{ $overtimeRule->description ?: '—' }}</span></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Coming</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Early Coming</small><span>{{ $formatTime($overtimeRule->ignore_early_come_minutes) }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Late Coming</small><span>{{ $formatTime($overtimeRule->ignore_late_come_minutes) }}</span></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Going</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Late Going?</small><span>{{ $overtimeRule->ignore_late_going ? 'Yes' : 'No' }}</span></div>
                    @if ($overtimeRule->ignore_late_going)
                        <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Late Going Overtime (HH:mm)</small><span>{{ $formatDuration($overtimeRule->ignore_late_going_overtime) }}</span></div>
                    @else
                        <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Overtime (HH:mm)</small><span>{{ $formatDuration($overtimeRule->ignore_overtime) }}</span></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-hour-4 me-2 text-primary"></i>Overtime Slabs</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light"><tr><th>#</th><th>Value From</th><th>Value To</th><th>Head Value</th><th>Decimal Value</th></tr></thead>
                        <tbody>
                            @forelse ($overtimeRule->overtime_slabs as $index => $slab)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $formatTime($slab->value_from) }}</td>
                                    <td>{{ $formatTime($slab->value_to) }}</td>
                                    <td>{{ $formatTime($slab->head_value) }}</td>
                                    <td>{{ $slab->decimal_value ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-muted text-center">No overtime slabs configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @include('partials.audit-users', ['record' => $overtimeRule])
    </div>
    @include('partials.footer')
</div>
@endsection
