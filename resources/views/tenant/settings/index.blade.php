@extends('layout.mainlayout')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Tenant Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('tenant.dashboard', ['tenant' => request()->route('tenant')]) }}">
                                    <i class="ti ti-smart-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="alert alert-info" role="status">
                These settings are currently UI-only. Saving and tenant-specific settings behavior will be added later.
            </div>

            @foreach ($sections as $section => $pages)
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">{{ $section }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($pages as $page => $label)
                                <div class="col-sm-6 col-xl-4 mb-3">
                                    <a class="d-flex align-items-center justify-content-between border rounded p-3 h-100"
                                        href="{{ route('tenant.settings.page', ['tenant' => request()->route('tenant'), 'page' => $page]) }}">
                                        <span>{{ $label }}</span>
                                        <i class="ti ti-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @include('partials.footer')
    </div>
@endsection
