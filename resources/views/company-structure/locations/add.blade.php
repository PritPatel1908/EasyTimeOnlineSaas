@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3"><div class="my-auto mb-2"><h2 class="mb-1">Add Location</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Company Structure</li><li class="breadcrumb-item"><a href="{{ url('company-structure/locations') }}">Locations</a></li><li class="breadcrumb-item active">Add Location</li></ol></nav></div></div>
    <div class="row"><div class="col-12"><div class="card"><div class="card-header"><h5 class="mb-0"><i class="ti ti-map-pin me-2 text-primary"></i>Location Details</h5></div><div class="card-body"><form action="{{ url('company-structure/locations') }}" method="POST">@csrf @include('company-structure.locations.partials.form-fields', ['location' => null])<div class="d-flex align-items-center justify-content-end mt-3 pt-2 border-top"><a href="{{ url('company-structure/locations') }}" class="btn btn-light me-2"><i class="ti ti-arrow-left me-1"></i>Cancel</a><button type="submit" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Location</button></div></form></div></div></div></div>
</div>@include('partials.footer')</div>
@endsection
