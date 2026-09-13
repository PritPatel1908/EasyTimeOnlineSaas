@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="page-breadcrumb mb-3"><h2 class="mb-1">Add Data Policy</h2><nav><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('data-policy') }}">Data Policies</a></li><li class="breadcrumb-item active">Add</li>
    </ol></nav></div>
    @if ($errors->any()) <div class="alert alert-danger">Please correct the highlighted fields.</div> @endif
    <div class="card"><div class="card-header"><h5 class="mb-0"><i class="ti ti-shield me-2 text-primary"></i>Data Policy Details</h5></div><div class="card-body">
        <form action="{{ url('data-policy') }}" method="POST">@csrf
            @include('company-structure.data-policies.partials.form-fields', ['dataPolicy' => null])
            <div class="d-flex justify-content-end mt-3 pt-3 border-top"><a href="{{ url('data-policy') }}" class="btn btn-light me-2">Cancel</a><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Save Data Policy</button></div>
        </form>
    </div></div>
</div>@include('partials.footer')</div>
@endsection
