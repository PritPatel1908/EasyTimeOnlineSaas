@extends('layout.mainlayout')
@section('content')
@php($isEdit = $account !== null)
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2">
            <h2 class="mb-1">{{ $isEdit ? 'Edit Leave Account' : 'Add Leave Account' }}</h2>
            <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/leave-accounts') }}">Leave Accounts</a></li><li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li></ol></nav>
        </div>
    </div>
    <div class="row"><div class="col-12"><div class="card">
        <div class="card-header"><h5 class="mb-0">Leave Account Details</h5></div>
        <div class="card-body">
            <form action="{{ $isEdit ? url('employee-structure/leave-accounts/'.$account->id) : url('employee-structure/leave-accounts') }}" method="POST">
                @csrf
                @if ($isEdit) @method('PUT') @endif
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="user_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Select Employee</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $account?->user_id) == $user->id)>{{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3 pt-2 border-top">
                    <a href="{{ url('employee-structure/leave-accounts') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create Account' }}</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>@include('partials.footer')</div>
@endsection
