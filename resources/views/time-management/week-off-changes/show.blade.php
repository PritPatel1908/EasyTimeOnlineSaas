@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Week-off Change</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/week-off-changes') }}">Manual Structure</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $weekOffChange->from_date?->format('Y-m-d') ?? 'Week-off Change' }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('WeekOffChange', 'write'))
                    <a href="{{ url('time-management/week-off-changes/'.$weekOffChange->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/week-off-changes') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Week-off Change Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Employees</small><span>{{ $weekOffChange->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">From Date</small><span>{{ $weekOffChange->from_date?->format('Y-m-d') ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">To Date</small><span>{{ $weekOffChange->is_forever ? 'No end date' : ($weekOffChange->to_date?->format('Y-m-d') ?? '—') }}</span></div>
                <div class="col-md-12 mb-3"><small class="text-muted d-block">Reason</small><span>{{ $weekOffChange->reason ?: '—' }}</span></div>
            </div></div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Weekly Off Details</h5></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table mb-0">
                    <thead class="thead-light"><tr><th>Day</th><th>Week-off Type</th><th>Applicable Weeks</th></tr></thead>
                    <tbody>
                        @forelse ($weekOffChange->WeekOffChangeDetails as $detail)
                            @php($weeks = collect(['first_week' => '1st Week', 'second_week' => '2nd Week', 'third_week' => '3rd Week', 'fourth_week' => '4th Week', 'fifth_week' => '5th Week'])->filter(fn ($label, $field) => $detail->$field)->values())
                            <tr>
                                <td>{{ $weekDays[$detail->week_days] ?? '—' }}</td>
                                <td>{{ $weekOffTypes[$detail->wo_type?->value ?? $detail->wo_type] ?? '—' }}</td>
                                <td>{{ $weeks->join(', ') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-muted text-center">No weekly off details found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
        @include('partials.audit-users', ['record' => $weekOffChange])
    </div>
    @include('partials.footer')
</div>
@endsection
