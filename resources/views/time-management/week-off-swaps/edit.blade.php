@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Edit Week-off Swap</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/week-off-swaps') }}">Manual Structure</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol></nav>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-calendar-edit me-2 text-primary"></i>Week-off Swap Details</h5></div>
            <div class="card-body">
                <form action="{{ url('time-management/week-off-swaps/'.$weekOffSwap->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('time-management.week-off-swaps.partials.form-fields')
                    <div class="d-flex align-items-center justify-content-end mt-3 pt-2 border-top">
                        <a href="{{ url('time-management/week-off-swaps') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
