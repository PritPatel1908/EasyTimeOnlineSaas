@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="d-md-flex align-items-center justify-content-between page-breadcrumb mb-3">
        <div><h2 class="mb-1">Edit Financial Year</h2><nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
            <li class="breadcrumb-item">Time Management</li>
            <li class="breadcrumb-item"><a href="{{ url('time-management/financial-years') }}">Financial Years</a></li>
            <li class="breadcrumb-item active">{{ $financialYear->name ?: $financialYear->code }}</li>
        </ol></nav></div>
    </div>
    <div class="card"><div class="card-header"><h5 class="mb-0">{{ $financialYear->name ?: $financialYear->code }}</h5></div><div class="card-body">
        <form action="{{ url('time-management/financial-years/'.$financialYear->id) }}" method="POST">
            @csrf @method('PUT')
            @include('time-management.financial-years.partials.form-fields')
            <div class="d-flex justify-content-end mt-3 pt-2 border-top">
                <a href="{{ url('time-management/financial-years') }}" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save Changes</button>
            </div>
        </form>
    </div></div>
</div>@include('partials.footer')</div>
@endsection
