@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Add Manual Attendance</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/manual-attendances') }}">Manual Attendances</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol></nav>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-edit me-2 text-primary"></i>Manual Attendance Details</h5></div>
            <div class="card-body">
                <form action="{{ url('time-management/manual-attendances') }}" method="POST">
                    @csrf
                    @include('time-management.manual-attendances.partials.form-fields')
                    <div class="d-flex align-items-center justify-content-end mt-3 pt-2 border-top">
                        <a href="{{ url('time-management/manual-attendances') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Manual Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
