@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="page-breadcrumb mb-3">
            <h2 class="mb-1">Edit COFF Application</h2>
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                <li class="breadcrumb-item">Time Management</li>
                <li class="breadcrumb-item"><a href="{{ url('time-management/coff-applications') }}">COFF Applications</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol></nav>
        </div>
        <div class="card"><div class="card-header"><h5 class="mb-0"><i class="ti ti-calendar-edit me-2 text-primary"></i>Application Details</h5></div><div class="card-body">
            <form action="{{ url('time-management/coff-applications/'.$application->id) }}" method="POST"
                data-live-validation-url="{{ url('time-management/coff-applications/'.$application->id.'/validate') }}">
                @csrf
                @method('PUT')
                @include('time-management.coff-applications.partials.form-fields')
                <div class="d-flex align-items-center justify-content-end mt-3 pt-2 border-top">
                    <a href="{{ url('time-management/coff-applications') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save Changes</button>
                </div>
            </form>
        </div></div>
    </div>
    @include('partials.footer')
</div>
@endsection
