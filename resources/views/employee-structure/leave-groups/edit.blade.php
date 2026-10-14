@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3"><div class="my-auto mb-2"><h2 class="mb-1">Edit Leave Group</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Employee Structure</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/leave-groups') }}">Leave Groups</a></li><li class="breadcrumb-item active">Edit Leave Group</li></ol></nav></div></div>
    <div class="row"><div class="col-12"><div class="card"><div class="card-header"><h5 class="mb-0"><i class="ti ti-users-group me-2 text-primary"></i>{{ $leaveGroup->name }}</h5></div><div class="card-body"><form action="{{ url('employee-structure/leave-groups/'.$leaveGroup->id) }}" method="POST" class="leave-group-form" novalidate>@csrf @method('PUT')<input type="hidden" name="monthly_rules_present" value="1">@include('employee-structure.leave-groups.partials.form-fields')<div class="d-flex align-items-center justify-content-end mt-3 pt-2 border-top"><a href="{{ url('employee-structure/leave-groups') }}" class="btn btn-light me-2"><i class="ti ti-arrow-left me-1"></i>Cancel</a><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save Changes</button></div></form></div></div></div></div>
</div>@include('partials.footer')</div>
@endsection
