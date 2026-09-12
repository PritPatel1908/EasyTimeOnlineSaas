@extends('layouts.admin')

@section('title', 'Tenants | EasyTime Online SaaS')

@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12 mt-3"><div class="sub-header py-3 px-3 d-sm-flex rounded"><div class="mr-auto"><h4 class="mb-0">Tenants</h4><b>Manage tenant workspaces</b></div><a href="{{ route('admin.tenants.create') }}" class="btn btn-primary align-self-center"><i class="icon-plus"></i> Add Tenant</a></div></div></div>
    <div class="row"><div class="col-12 mt-3"><div class="card"><div class="card-body">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if (session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
        @include('admin.tenants.partials.pagination')
    </div></div></div></div>
</div>
@endsection
