@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">
			<div class="row align-items-center">
				<div class="col-md-10 mx-auto">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-4">
								<a href="{{url('invoices')}}"
									class="back-icon align-items-center fs-14 d-inline-flex fw-medium">
									<span
										class=" d-flex justify-content-center align-items-center rounded-circle me-2">
										<i class="ti ti-arrow-left fs-12"></i>
									</span>
									Back to List
								</a>
								<a href="#" class="text-primary text-decoration-underline" data-bs-toggle="modal"
									data-bs-target="#invoice_preview">
									Preview
								</a>
							</div>

							<!-- My details -->
							<div class="bg-light p-3 rounded mb-3">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5>From</h5>
									<a href="#" class="text-dark fw-medium"><span class="text-gray me-2"><i
												class="ti ti-edit"></i></span>Edit Details</a>
								</div>
								<div>
									<h4 class="mb-1">Thomas Lawler</h4>
									<p class="mb-1">2077 Chicago Avenue Orosi, CA 93647</p>
									<p class="mb-1">Email : <span class="text-dark">Tarala2445@example.com</span>
									</p>
									<p>Phone : <span class="text-dark">+1 987 654 3210</span></p>
								</div>
							</div>
							<!-- /My details -->

							<!-- Invoice Details-->
							<div class="border-bottom mb-3">
								<h4 class="mb-2">Invoice Details</h4>
								<div class="mb-2">
									<label class="form-label">Invoice Title</label>
									<input type="text" class="form-control">
								</div>
								<div class="row">
									<div class="col-md-4 col-sm-12">
										<div class="mb-3">
											<label class="form-label">Invoice No</label>
											<input type="text" class="form-control">
										</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<div class="mb-3">
											<label class="form-label">Invoice Date</label>
											<div class="input-icon position-relative w-100 me-2">
												<span class="input-icon-addon">
													<i class="ti ti-calendar"></i>
												</span>
												<input type="text" class="form-control datetimepicker"
													placeholder="dd/mm/yyyy">
											</div>
										</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<div class="mb-3">
											<label class="form-label">Due Date</label>
											<div class="input-icon position-relative w-100 me-2">
												<span class="input-icon-addon">
													<i class="ti ti-calendar"></i>
												</span>
												<input type="text" class="form-control datetimepicker"
													placeholder="dd/mm/yyyy">
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- /Invoice Details-->

							<!-- Payment Details-->
							<div class="border-bottom mb-3">
								<h4 class="mb-2">Payment Details</h4>

								<div class="row">
									<div class="col-lg-3 col-md-6 col-sm-12">
										<div class="mb-3">
											<div class="d-flex justify-content-between align-items-center">
												<label class="form-label">Customer</label>
												<a href="#" class="text-primary fw-medium d-flex align-items-center"
													data-bs-toggle="modal" data-bs-target="#add_customer">
													<i class="ti ti-plus me-2"></i>Add New
												</a>
											</div>
											<input type="text" class="form-control">
										</div>
									</div>
									<div class="col-lg-3 col-md-6 col-sm-12">
										<div class="mb-3">
											<label class="form-label">Reference Number</label>
											<input type="text" class="form-control">
										</div>
									</div>
									<div class="col-lg-3 col-md-6 col-sm-12">
										<div class="mb-3">
											<label class="form-label">Select Payment Type</label>
											<select class="select">
												<option>Select</option>
												<option>Credit</option>
												<option>Debit</option>
											</select>
										</div>
									</div>
									<div class="col-lg-3 col-md-6 col-sm-12">
										<div class="mb-3">
											<label class="form-label">Bank Details</label>
											<select class="select">
												<option>Select</option>
												<option>Bank of America</option>
												<option>U.S. Bank</option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<!-- /Payment Details-->

							<!-- Add Items-->
							<div class="border-bottom mb-3">
								<h4 class="mb-2">Add Items</h4>
								<div class="border rounded p-3 mb-3">
									<div class="add-description-info">
										<div class="row">
											<div class="col-md-6">
												<div class="mb-3">
													<label class="form-label">Description</label>
													<input type="text" class="form-control">
												</div>
											</div>
											<div class="col-md-6">
												<div class="row">
													<div class="col-md-4">
														<div class="mb-3">
															<label class="form-label">Qty</label>
															<input type="text" class="form-control">
														</div>
													</div>
													<div class="col-md-4">
														<div class="mb-3">
															<label class="form-label">Discount</label>
															<input type="text" class="form-control">
														</div>
													</div>
													<div class="col-md-4">
														<div class="mb-3">
															<label class="form-label">Rate</label>
															<input type="text" class="form-control">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<a href="javascript:void(0);"
										class="text-primary add-more-description fw-medium d-flex align-items-center"><i
											class="ti ti-plus me-2"></i>Add New</a>
								</div>
							</div>
							<!-- /Add Items-->

							<!-- Additional Details-->
							<div>
								<h4 class="mb-2">Additional Details</h4>
								<div class="mb-3">
									<label class="form-label"> Description</label>
									<textarea class="form-control" rows="3"></textarea>
								</div>
								<div class="mb-3">
									<label class="form-label">Notes</label>
									<textarea class="form-control" rows="3"></textarea>
								</div>
							</div>
							<!-- Additional Details-->

							<div class="d-flex justify-content-end align-items-center flex-wrap row-gap-3">
								<a href="#" class="btn btn-dark d-flex justify-content-center align-items-center"><i
										class="ti ti-printer me-2"></i>Save as Draft</a>
								<a href="#"
									class="btn btn-primary d-flex justify-content-center align-items-center  ms-2"><i
										class="ti ti-copy me-2"></i>Save & Send</a>
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