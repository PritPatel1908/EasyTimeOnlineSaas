@php
    $errorPage = true;
    $dashboardUrl = request()->routeIs('tenant.*') && Route::has('tenant.dashboard')
        ? route('tenant.dashboard', ['tenant' => request()->route('tenant')])
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : url('/'));
@endphp
@extends('layout.mainlayout')

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-xl-8 col-lg-9 d-flex justify-content-center align-items-center mx-auto">
                <div class="w-100 text-center">
                    <div class="p-4">
                        <img src="{{ URL::asset('build/img/logo.svg') }}" alt="{{ config('app.name') }}" class="img-fluid">
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        <div class="avatar avatar-xxl rounded-circle bg-primary d-flex align-items-center justify-content-center text-white shadow-lg">
                            <i class="ti ti-lock fs-64"></i>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-danger-transparent text-danger mb-3">Error 403</span>
                        <h1 class="mb-3">Access restricted</h1>
                        <p class="fs-16 text-muted mb-4">
                            {{ $exception?->getMessage() ?: 'Your account does not have permission to view this page. Please contact your administrator if you believe this is a mistake.' }}
                        </p>
                        <div class="d-flex justify-content-center flex-wrap gap-2 pb-4">
                            <a href="{{ $dashboardUrl }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-layout-dashboard me-2"></i>Back to Dashboard
                            </a>
                            <button type="button" class="btn btn-light d-flex align-items-center" onclick="history.length > 1 ? history.back() : window.location.href = '{{ $dashboardUrl }}'">
                                <i class="ti ti-arrow-left me-2"></i>Go Back
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
