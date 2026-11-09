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
								Financial Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Currencies</li>
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
					<a class="nav-link" href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('email-settings')}}"><i class="ti ti-server-cog me-2"></i>System
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="{{url('payment-gateways')}}"><i
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
								<a href="{{url('payment-gateways')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Payment Gateways</a>
								<a href="{{url('tax-rates')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Tax Rates</a>
								<a href="{{url('currencies')}}"
									class="d-inline-flex align-items-center active rounded py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Currencies</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-header px-0 mx-3">
							<div class="row g-3 align-items-center">
								<div class="col-md-6 col-sm-4">
									<h4>Currencies</h4>
								</div>
								<div class="col-md-6 col-sm-8">
									<div
										class="d-flex justify-content-sm-end align-items-center flex-wrap row-gap-2">
										<a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal"
											data-bs-target="#add_new_currency"><i
												class="ti ti-circle-plus me-2"></i>Add Currency</a>
									</div>
								</div>
							</div>
						</div>
						<div class="card-body pb-0">
							<div class="card mb-3">
								<div
									class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
									<h5>Currencies List</h5>
								</div>
								<div class="card-body p-0">
									<div class="table-responsive">
										<table class="table">
											<thead class="thead-light">
												<tr>
													<th class="no-sort">
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox"
																id="select-all">
														</div>
													</th>
													<th>Currency</th>
													<th>Currency Symbol</th>
													<th>Currency Position</th>
													<th>Currency Code</th>
													<th>Status</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="d-flex align-items-center fw-medium">
															Dollar
														</h6>
													</td>
													<td>$</td>
													<td>Front</td>
													<td>USD</td>
													<td>
														<span
															class="badge badge-success d-inline-flex align-items-center badge-xs">
															<i class="ti ti-point-filled me-1"></i>Active
														</span>
													</td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_new_currency"><i
																	class="ti ti-edit"></i></a>
															<a href="javascript:void(0);" data-bs-toggle="modal"
																data-bs-target="#delete_modal"><i
																	class="ti ti-trash"></i></a>
														</div>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="d-flex align-items-center fw-medium">
															Rupee
														</h6>
													</td>
													<td>₹</td>
													<td>Front</td>
													<td>INR</td>
													<td>
														<span
															class="badge badge-success d-inline-flex align-items-center badge-xs">
															<i class="ti ti-point-filled me-1"></i>Active
														</span>
													</td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_new_currency"><i
																	class="ti ti-edit"></i></a>
															<a href="javascript:void(0);" data-bs-toggle="modal"
																data-bs-target="#delete_modal"><i
																	class="ti ti-trash"></i></a>
														</div>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
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