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
					<h2 class="mb-1">Settings</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								App Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Invoice Settings</li>
						</ol>
					</nav>
				</div>
				<div class="head-icons ms-2">
					<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
						data-bs-original-title="Collapse" id="collapse-header">
						<i class="ti ti-chevrons-up"></i>
					</a>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
				<li class="nav-item">
					<a class="nav-link" href="{{url('profile-settings')}}"><i class="ti ti-settings me-2"></i>General
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('business-settings')}}"><i class="ti ti-world-cog me-2"></i>Website
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('email-settings')}}"><i class="ti ti-server-cog me-2"></i>System
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('payment-gateways')}}"><i
							class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('custom-css')}}"><i class="ti ti-settings-2 me-2"></i>Other
						Settings</a>
				</li>
			</ul>
			<div class="row">
				<div class="col-xl-3 theiaStickySidebar">
					<div class="card">
						<div class="card-body">
							<div class="d-flex flex-column list-group settings-list">
								<a href="{{url('salary-settings')}}"
									class="d-inline-flex align-items-center rounded  py-2 px-3">Profile Settings</a>
								<a href="{{url('approval-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
								<a href="{{url('invoice-settings')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Invoice Settings</a>
								<a href="{{url('leave-type')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Leave Type</a>
								<a href="{{url('custom-fields')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Custom Fields</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3 pb-3">
								<h4>Invoice Settings</h4>
							</div>
							<form action="{{url('profile-settings')}}">
								<div class="border-bottom mb-3">
									<div class="row">
										<div class="col-md-12">
											<div>

												<div class="row">
													<div class=" col-md-3">
														<div class="mb-3">
															<h6>Invoice Logo</h6>
														</div>
													</div>
													<div class=" col-md-9">
														<div
															class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
															<div
																class="d-flex align-items-center justify-content-center og-upload bg-white rounded border border-dashed me-2 flex-shrink-0 text-dark frames">
																<i class="ti ti-photo text-gray-3 fs-16"></i>
															</div>
															<div class="profile-upload">
																<div class="mb-2">
																	<h6 class="mb-1">Logo</h6>
																	<p class="fs-12">Recommended image size is 40px
																		x 40px</p>
																</div>
																<div
																	class="profile-uploader d-flex align-items-center">
																	<div
																		class="drag-upload-btn btn btn-sm btn-primary me-2">
																		Upload
																		<input type="file"
																			class="form-control image-sign"
																			multiple="">
																	</div>
																	<a href="javascript:void(0);"
																		class="btn btn-light btn-sm">Cancel</a>
																</div>

															</div>
														</div>
													</div>
												</div>
												<div class="row align-items-center">
													<div class=" col-md-3">
														<div class="mb-3">
															<h6>Invoice Prefix</h6>
														</div>
													</div>
													<div class=" col-md-5">
														<div class="mb-3">
															<input type="text" class="form-control">
														</div>
													</div>
												</div>
												<div class="row align-items-center">
													<div class="col-md-3">
														<div class="mb-3">
															<h6>Invoice Due</h6>
														</div>
													</div>
													<div class=" col-md-5">
														<div class="mb-3">
															<select class="select">
																<option>Select</option>
																<option>5</option>
																<option>7</option>
															</select>
														</div>
													</div>
													<div class=" col-md-5">
														<h6 class="mb-3">Days</h6>
													</div>
												</div>
												<div class="row align-items-center">
													<div class=" col-md-3">
														<div class="mb-3 d-flex">
															<h6>Invoice Round Off</h6>
														</div>
													</div>
													<div class=" col-md-5">
														<div class="mb-3">
															<select class="select">
																<option>Select</option>
																<option>RoundOff Up</option>
																<option>RoundOff Down</option>
															</select>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch" id="flexSwitchCheckDefault">
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-3">
														<div class="mb-3 d-flex">
															<h6>Show Company Details</h6>
														</div>
													</div>
													<div class="col-md-5">
														<div class="mb-3">
															<div class="form-check form-switch">
																<input class="form-check-input" type="checkbox"
																	role="switch" id="flexSwitchCheckDefault2">
															</div>
														</div>
													</div>
												</div>
												<div class="row align-items-center">
													<div class="col-md-3">
														<div class="mb-3">
															<h6>Invoice Terms</h6>
														</div>
													</div>
													<div class="col-md-9">
														<div class="mb-3">
															<div class="summernote"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

								</div>

								<div class="d-flex align-items-center justify-content-end">
									<button type="button" class="btn btn-outline-light border me-3">Cancel</button>
									<button type="submit" class="btn btn-primary">Save</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection