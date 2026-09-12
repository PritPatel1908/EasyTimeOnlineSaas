@extends('layouts.admin')

@section('title', ($user ? 'Edit User' : 'Add User').' | EasyTime Online SaaS')

@section('content')
<div class="container-fluid"><div class="row"><div class="col-12 mt-3"><div class="sub-header py-3 px-3"><h4 class="mb-0">{{ $user ? 'Edit User' : 'Add User' }}</h4></div></div></div><div class="row"><div class="col-12 mt-3"><div class="card"><div class="card-body"><form method="POST" action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}">@csrf @if ($user) @method('PUT') @endif<x-form-input name="name" label="Name" value="{{ old('name', $user->name ?? '') }}" required /><x-form-input name="email" label="Email" type="email" value="{{ old('email', $user->email ?? '') }}" required /><x-form-input name="password" label="Password" type="password" /><x-form-input name="password_confirmation" label="Confirm password" type="password" /><small class="text-muted">{{ $user ? 'Leave password blank to keep the current password.' : 'Use at least 8 characters.' }}</small><div class="mt-4"><a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a><button type="submit" class="btn btn-primary">{{ $user ? 'Update User' : 'Create User' }}</button></div></form></div></div></div></div></div>
@endsection

