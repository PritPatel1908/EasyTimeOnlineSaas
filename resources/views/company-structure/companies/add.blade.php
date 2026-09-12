@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Add Company</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Company Structure</li>
                        <li class="breadcrumb-item">
                            <a href="{{ url('company-structure/companies') }}">Companies</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Add Company</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ti ti-building me-2 text-primary"></i>
                            Company Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('company-structure/companies') }}" method="POST">
                            @csrf

                            @include('company-structure.companies.partials.form-fields', [
                                'company' => null,
                                'locations' => $locations,
                            ])

                            <div class="d-flex align-items-center justify-content-end mt-3 pt-2 border-top">
                                <a href="{{ url('company-structure/companies') }}" class="btn btn-light me-2">
                                    <i class="ti ti-arrow-left me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-circle-plus me-1"></i>Add Company
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.footer')
</div>

@endsection
