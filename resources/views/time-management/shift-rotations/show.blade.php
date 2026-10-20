@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Shift Rotation</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/shift-rotations') }}">Shift Rotations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $shiftRotation->code }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('ShiftRotation', 'write'))
                    <a href="{{ url('time-management/shift-rotations/'.$shiftRotation->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/shift-rotations') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-calendar-repeat me-2 text-primary"></i>Basic Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Rotation Code</small><span>{{ $shiftRotation->code }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Start Date</small><span>{{ $shiftRotation->start_date?->format('Y-m-d') ?? '—' }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">End Date</small><span>{{ $shiftRotation->is_forever ? 'Forever' : ($shiftRotation->end_date?->format('Y-m-d') ?? '—') }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Forever</small><span>{{ $shiftRotation->is_forever ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Skip remaining days at month end</small><span>{{ $shiftRotation->skip_days_on_month_end ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Skip remaining days at year end</small><span>{{ $shiftRotation->skip_days_on_year_end ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Shift Status Flag</small><span>{{ $shiftRotation->shift_status ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge {{ (int) $shiftRotation->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                        <i class="ti ti-point-filled me-1"></i>{{ (int) $shiftRotation->status === 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Rotation Steps</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light"><tr><th>Order</th><th>Shift</th><th>Shift Code</th><th>Days</th></tr></thead>
                        <tbody>
                            @forelse ($shiftRotation->rotation_values as $index => $rotationValue)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $rotationValue->shift?->name ?? '—' }}</td>
                                    <td>{{ $rotationValue->shift?->code ?? '—' }}</td>
                                    <td>{{ $rotationValue->days }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-muted text-center">No shifts are configured for this rotation.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @include('partials.audit-users', ['record' => $shiftRotation])
    </div>
    @include('partials.footer')
</div>
@endsection
