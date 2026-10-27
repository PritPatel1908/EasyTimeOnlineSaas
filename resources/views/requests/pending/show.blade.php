@extends('layout.mainlayout')
@section('content')
@php
    $tenant = request()->route('tenant');
@endphp
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Review Pending Request</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tenant.requests.pending.index', ['tenant' => $tenant]) }}">Pending Requests</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Request #{{ $approvalStatus->approvable_id }}</li>
                </ol></nav>
            </div>
            <a href="{{ route('tenant.requests.pending.index', ['tenant' => $tenant]) }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Original Request</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Request Type</small>
                        <span>{{ \Illuminate\Support\Str::headline(class_basename($record)) }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Employees</small>
                        <span>{{ $employees->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</span>
                    </div>
                    @foreach ($requestFields as $label => $value)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ $label }}</small>
                            <span>{{ $value }}</span>
                        </div>
                    @endforeach
                    @foreach ($relatedDetails as $label => $value)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ $label }}</small>
                            @if (is_array($value))
                                @forelse ($value as $line)
                                    <span class="d-block">{{ $line }}</span>
                                @empty
                                    <span>—</span>
                                @endforelse
                            @else
                                <span>{{ $value }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($record instanceof \App\Models\Tenant\WeekOffChangeApplication)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Weekly Off Details</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="thead-light">
                                <tr><th>Day</th><th>Week-off Type</th><th>Applicable Weeks</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($weekOffDetails as $detail)
                                    <tr>
                                        <td>{{ $detail['day'] }}</td>
                                        <td>{{ $detail['type'] }}</td>
                                        <td>{{ $detail['weeks'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-muted text-center">No weekly off details found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Approval Progress</h5></div>
            <div class="card-body">
                @foreach ($record->approval_statuses as $flowStatus)
                    <div class="mb-4">
                        <h6>{{ $flowStatus->approval_flow?->approval_flow_code ?? 'Approval Flow' }} — {{ ucfirst($flowStatus->approval_status) }}</h6>
                        @foreach ($flowStatus->approval_status_details->sortBy('level') as $level)
                            <div class="border-bottom py-2">
                                <div class="d-flex justify-content-between">
                                    <span>Level {{ $level->level }}{{ $level->user?->name ? ' — '.$level->user->name : '' }}</span>
                                    <span>{{ ucfirst($level->approval_status) }}{{ $level->timestamp ? ' on '.$level->timestamp->format('Y-m-d H:i:s') : '' }}</span>
                                </div>
                                @if ($level->rejection_reason)
                                    <div class="small text-danger mt-1">Rejection reason: {{ $level->rejection_reason }}</div>
                                @endif
                                @if ($level->approval_status === 'pending')
                                    <div class="small text-muted mt-1">
                                        Assigned to: {{ $level->approval_flow_detail?->users->pluck('name')->join(', ') ?: '—' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        @if ($isActionable)
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Your Decision</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tenant.requests.pending.decision', ['tenant' => $tenant, 'detail' => $approval->id]) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection reason <span class="text-muted">(required only when rejecting)</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" maxlength="1000" class="form-control @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="decision" value="approved" class="btn btn-success">Approve</button>
                            <button type="submit" name="decision" value="rejected" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-secondary" role="status">This approval level is no longer actionable.</div>
        @endif
    </div>
    @include('partials.footer')
</div>
@endsection
