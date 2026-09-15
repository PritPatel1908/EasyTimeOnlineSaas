@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">Add Team</h2>
                <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Company Structure</li><li class="breadcrumb-item"><a href="{{ url('company-structure/teams') }}">Teams</a></li><li class="breadcrumb-item active">Add</li></ol></nav>
            </div>
        </div>
        <form method="POST" action="{{ url('company-structure/teams') }}">
            @csrf
            @include('company-structure.teams.partials.form-fields')
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ url('company-structure/teams') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Team</button>
            </div>
        </form>
    </div>
</div>
@endsection
