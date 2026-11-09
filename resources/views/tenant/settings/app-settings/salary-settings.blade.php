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
							<li class="breadcrumb-item active" aria-current="page">Salary Settings</li>
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
					<a class="nav-link " href="{{url('profile-settings')}}"><i class="ti ti-settings me-2"></i>General
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
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Salary Settings</a>
								<a href="{{url('approval-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
								<a href="{{url('invoice-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Invoice Settings</a>
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
								<h4>Salary Settings</h4>
							</div>
							<form action="{{url('profile-settings')}}">
								<div class="border-bottom mb-3">
									<div class="row">

									</div>
									<div class="row">
										<div class="col-md-4 d-flex">
											<div class="card flex-fill">
												<div class="card-body pb-1">
													<div
														class="content-head d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
														<h5>DA & HRA</h5>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch" id="flexSwitchCheckDefault">
														</div>
													</div>
													<div class="mb-3">
														<label class="form-label">DA (%)</label>
														<input type="text" class="form-control">
													</div>
													<div class="mb-3">
														<label class="form-label">HRA (%)</label>
														<input type="text" class="form-control">
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-4 d-flex">
											<div class="card flex-fill">
												<div class="card-body pb-1">
													<div
														class="content-head d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
														<h5>Provident Fund</h5>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch" id="flexSwitchCheckDefault2">
														</div>
													</div>
													<div class="mb-3">
														<label class="form-label">Employee Share (%)</label>
														<input type="text" class="form-control">
													</div>
													<div class="mb-3">
														<label class="form-label">Organization Share (%)</label>
														<input type="text" class="form-control">
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-4 d-flex">
											<div class="card flex-fill">
												<div class="card-body pb-1">
													<div
														class="content-head d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
														<h5>ESI</h5>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch" id="flexSwitchCheckDefault3">
														</div>
													</div>
													<div class="mb-3">
														<label class="form-label">Employee Share (%)</label>
														<input type="text" class="form-control">
													</div>
													<div class="mb-3">
														<label class="form-label">Organization Share (%)</label>
														<input type="text" class="form-control">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-3">

									<div class="row">
										<div class="col-md-12">
											<div class="card">
												<div class="card-body pb-1">
													<div
														class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
														<h5>TDS <span> Annual Salary</span></h5>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch" id="flexSwitchCheckDefault4">
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="add-salary-info">
																<div class="row">
																	<div class="col-md-4">
																		<div class="mb-3">
																			<label class="form-label">Salary
																				From</label>
																			<input type="text" class="form-control">
																		</div>
																	</div>
																	<div class="col-md-4">
																		<div class="mb-3">
																			<label class="form-label">Salary
																				To</label>
																			<input type="text" class="form-control">
																		</div>
																	</div>
																	<div class="col-md-4">
																		<div class="d-flex align-items-center">
																			<div class="mb-3 flex-fill">
																				<label
																					class="form-label">Percentage</label>
																				<input type="text"
																					class="form-control">
																			</div>
																			<div
																				class="d-flex align-items-center pt-3 ms-3">
																				<a href="#"
																					class="avatar avatar-md rounded bg-gray add-salary-btn text-primary"><i
																						class="ti ti-plus"></i></a>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
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