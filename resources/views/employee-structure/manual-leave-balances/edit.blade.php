@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2"><h2 class="mb-1">Edit Manual Leave Balance</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/manual-leave-balances') }}">Manual Leave Balances</a></li><li class="breadcrumb-item active">Edit</li></ol></nav></div>
    </div>
    <div class="row"><div class="col-12"><div class="card"><div class="card-header"><h5 class="mb-0">Balance #{{ $balance->id }}</h5></div><div class="card-body">
        <form action="{{ url('employee-structure/manual-leave-balances/'.$balance->id) }}" method="POST">@csrf @method('PUT')
            @include('employee-structure.manual-leave-balances.partials.form-fields')
            <div class="d-flex justify-content-end mt-3 pt-2 border-top"><a href="{{ url('employee-structure/manual-leave-balances') }}" class="btn btn-light me-2">Cancel</a><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save Changes</button></div>
        </form>
    </div></div></div></div>
</div>@include('partials.footer')</div>
@endsection
