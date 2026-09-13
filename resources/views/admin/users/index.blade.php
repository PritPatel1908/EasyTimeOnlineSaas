@extends('layouts.admin')

@section('title', 'Users | EasyTime Online SaaS')

@section('content')
<div class="container-fluid">
    @include('partials.flash-alerts')
    <div class="row"><div class="col-12 mt-3"><div class="sub-header py-3 px-3 d-sm-flex"><div class="mr-auto"><h4 class="mb-0">Users</h4><b>Manage platform users</b></div><a href="{{ route('admin.users.create') }}" class="btn btn-primary align-self-center"><i class="icon-plus"></i> Add User</a></div></div></div>
    <div class="row"><div class="col-12 mt-3"><div class="card"><div class="card-body">
        @include('admin.users.partials.pagination')
    </div></div></div></div>
</div>
@endsection
