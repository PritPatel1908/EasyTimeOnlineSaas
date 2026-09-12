@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">
		<div class="content">

			<div class="page-wrapper cardhead">


				<!-- Start Content -->
				<div class="content">

					<!-- Breadcrumb -->
					<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
						<div class="my-auto mb-2">
							<h2 class="mb-1">Form Wizard</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Forms
									</li>
									<li class="breadcrumb-item active" aria-current="page">Form Wizard</li>
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

					<div class="row">

						<!-- Lightbox -->
						<div class="col-lg-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title mb-0">Basic Wizard</h4>
								</div>
								<div class="card-body twitter-bs-wizard">
									<div class="wizard twitter-bs-wizard-nav">
										<ul class="nav nav-tabs form-tab justify-content-center mb-3" id="myTab"
											role="tablist">
											<li class="nav-item flex-fill" role="presentation" data-bs-toggle="tooltip"
												data-bs-placement="top" title="Seller Details">
												<a class="nav-link active rounded mx-auto d-flex align-items-center justify-content-center"
													href="#step11" id="step1-tab" data-bs-toggle="tab" role="tab"
													aria-controls="step1" aria-selected="true">
													<div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top"
														title="Seller Details">
														<i class="far fa-user"></i>
													</div>
												</a>
											</li>
											<li class="nav-item flex-fill" role="presentation" data-bs-toggle="tooltip"
												data-bs-placement="top" title="Company Document">
												<a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
													href="#step21" id="step2-tab" data-bs-toggle="tab" role="tab"
													aria-controls="step2" aria-selected="false">
													<div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top"
														title="Company Document">
														<i class="fas fa-map-pin"></i>
													</div>
												</a>
											</li>
											<li class="nav-item flex-fill" role="presentation" data-bs-toggle="tooltip"
												data-bs-placement="top" title="Bank Details">
												<a class="nav-link rounded mx-auto d-flex align-items-center justify-content-center"
													href="#step31" id="step3-tab" data-bs-toggle="tab" role="tab"
													aria-controls="step3" aria-selected="false">
													<div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top"
														title="Bank Details">
														<i class="fas fa-credit-card"></i>
													</div>
												</a>
											</li>
										</ul>
										<div class="tab-content" id="myTabContent">
											<div class="tab-pane fade show active" role="tabpanel" id="step11"
												aria-labelledby="step1-tab">
												<div class="mb-4">
													<h5>Enter Your Personal Details</h5>
												</div>
												<form>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">First name</label>
																<input type="text" class="form-control">
															</div>
														</div>
														<div class="col-lg-6">
															<div class="mb-3">
																<label for="basicpill-lastname-input"
																	class="form-label">Last name</label>
																<input type="text" class="form-control"
																	id="basicpill-lastname-input">
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Phone</label>
																<input type="text" class="form-control">
															</div>
														</div>
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Email</label>
																<input type="email" class="form-control">
															</div>
														</div>
													</div>
												</form>
												<div class="d-flex align-items-center justify-content-end">
													<a class="btn btn btn-primary next">Next</a>
												</div>
											</div>
											<div class="tab-pane fade" role="tabpanel" id="step21"
												aria-labelledby="step2-tab">
												<div class="mb-4">
													<h5>Enter Your Address</h5>
												</div>
												<form>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Address 1</label>
																<input type="text" class="form-control">
															</div>
														</div>
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Address 2</label>
																<input type="text" class="form-control">
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Landmark</label>
																<input type="text" class="form-control">
															</div>
														</div>
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Town</label>
																<input type="text" class="form-control">
															</div>
														</div>
													</div>
												</form>
												<div
													class="d-flex align-items-center justify-content-between flex-wrap gap-2">
													<a class="btn btn btn-primary previous me-2"> Back</a>
													<a class="btn btn btn-primary next">Continue</a>
												</div>
											</div>
											<div class="tab-pane fade" role="tabpanel" id="step31"
												aria-labelledby="step3-tab">
												<div class="mb-4">
													<h5>Payment Details</h5>
												</div>
												<form>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Name on Card</label>
																<input type="text" class="form-control">
															</div>
														</div>
														<div class="col-lg-6">
															<div class="mb-3">
																<label class="form-label">Credit Card Type</label>
																<select class="form-select">
																	<option selected>Select Card Type</option>
																	<option value="AE">American Express</option>
																	<option value="VI">Visa</option>
																	<option value="MC">MasterCard</option>
																	<option value="DI">Discover</option>
																</select>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label for="basicpill-cardno-input"
																	class="form-label">Credit Card Number</label>
																<input type="text" class="form-control"
																	id="basicpill-cardno-input">
															</div>
														</div>
														<div class="col-lg-6">
															<div class="mb-3">
																<label for="basicpill-card-verification-input"
																	class="form-label">Card Verification Number</label>
																<input type="text" class="form-control"
																	id="basicpill-card-verification-input">
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-lg-6">
															<div class="mb-3">
																<label for="basicpill-expiration-input"
																	class="form-label">Expiration Date</label>
																<input type="text" class="form-control"
																	id="basicpill-expiration-input">
															</div>
														</div>
													</div>
												</form>
												<div
													class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
													<a class="btn btn-primary previous me-2">Previous</a>
													<a class="btn btn-primary next" data-bs-toggle="modal"
														data-bs-target="#save_modal">Save Changes</a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- end card body -->
							</div>
						</div>
						<!-- /Wizard -->

					</div>

				</div>
				<!-- End Content -->

				@include('partials.footer')

			</div>

		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection