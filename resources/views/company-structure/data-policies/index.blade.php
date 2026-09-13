@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Data Policies</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Company Structure</li>
                    <li class="breadcrumb-item active">Data Policies</li>
                </ol></nav>
            </div>
            <a href="{{ url('data-policy/create') }}" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-circle-plus me-2"></i>Add Data Policy
            </a>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Data Policy List</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable mb-0">
                        <thead class="thead-light"><tr>
                            <th>Name</th><th>Code</th><th>Scope</th><th>Status</th><th class="text-end">Actions</th>
                        </tr></thead>
                        <tbody>@include('company-structure.data-policies.partials.rows', ['dataPolicies' => $dataPolicies])</tbody>
                    </table>
                </div>
            </div>
            @if ($dataPolicies->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="text-muted fs-13">Showing {{ $dataPolicies->firstItem() }}–{{ $dataPolicies->lastItem() }} of {{ $dataPolicies->total() }} data policies</span>
                    {{ $dataPolicies->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
