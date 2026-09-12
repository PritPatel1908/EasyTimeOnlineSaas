@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">

			<!-- Breadcrumb -->
			<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">Leave Settings</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Leaves
							</li>
							<li class="breadcrumb-item active" aria-current="page">Leave Settings</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#new_custom_policy"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Custom Policy</a>
					</div>
					<div class="head-icons ms-2">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- Leaves Info -->
			<div class="row">
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<div class="form-check form-check-md form-switch me-1">
									<label class="form-check-label">
										<input class="form-check-input" type="checkbox" role="switch" checked>
									</label>
								</div>
								<h6 class="d-flex align-items-center">Annual Leave</h6>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-decoration-underline me-2"
									data-bs-toggle="modal" data-bs-target="#add_custom_policy">Custom Policy</a>
								<a href="javascript:void(0);" data-bs-toggle="modal"
									data-bs-target="#annual_leave_settings"> <i class="ti ti-settings"></i> </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<div class="form-check form-check-md form-switch me-1">
									<label class="form-check-label">
										<input class="form-check-input" type="checkbox" role="switch">
									</label>
								</div>
								<h6 class="d-flex align-items-center">Sick Leave</h6>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-decoration-underline me-2"
									data-bs-toggle="modal" data-bs-target="#add_custom_policy">Custom Policy</a>
								<a href="javascript:void(0);" data-bs-toggle="modal"
									data-bs-target="#sick_leave_settings"> <i class="ti ti-settings"></i> </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<div class="form-check form-check-md form-switch me-1">
									<label class="form-check-label">
										<input class="form-check-input" type="checkbox" role="switch" checked>
									</label>
								</div>
								<h6 class="d-flex align-items-center">Hospitalisation</h6>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-decoration-underline me-2"
									data-bs-toggle="modal" data-bs-target="#add_custom_policy">Custom Policy</a>
								<a href="javascript:void(0);" data-bs-toggle="modal"
									data-bs-target="#hospitalisation_settings"><i class="ti ti-settings"></i> </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<div class="form-check form-check-md form-switch me-1">
									<label class="form-check-label">
										<input class="form-check-input" type="checkbox" role="switch" checked>
									</label>
								</div>
								<h6 class="d-flex align-items-center">Maternity</h6>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-decoration-underline me-2"
									data-bs-toggle="modal" data-bs-target="#add_custom_policy">Custom Policy</a>
								<a href="javascript:void(0);" data-bs-toggle="modal"
									data-bs-target="#maternity_settings"> <i class="ti ti-settings"></i> </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<div class="form-check form-check-md form-switch me-1">
									<label class="form-check-label">
										<input class="form-check-input" type="checkbox" role="switch">
									</label>
								</div>
								<h6 class="d-flex align-items-center">Paternity</h6>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-decoration-underline me-2"
									data-bs-toggle="modal" data-bs-target="#add_custom_policy">Custom Policy</a>
								<a href="javascript:void(0);" data-bs-toggle="modal"
									data-bs-target="#paternity_settings"> <i class="ti ti-settings"></i> </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<div class="form-check form-check-md form-switch me-1">
									<label class="form-check-label">
										<input class="form-check-input" type="checkbox" role="switch">
									</label>
								</div>
								<h6 class="d-flex align-items-center">LOP</h6>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-decoration-underline me-2"
									data-bs-toggle="modal" data-bs-target="#add_custom_policy">Custom Policy</a>
								<a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#lop_settings">
									<i class="ti ti-settings"></i> </a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Leaves Info -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection