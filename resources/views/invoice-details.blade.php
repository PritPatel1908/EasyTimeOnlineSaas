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
					<h2 class="mb-1">Invoices</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Applications
							</li>
							<li class="breadcrumb-item active" aria-current="page">Invoices</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
						<a href="#" class="btn btn-dark d-flex align-items-center"><i
								class="ti ti-download me-2"></i>Download</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- Invoices -->
			<div>
				<div class="row">
					<div class="col-sm-10 mx-auto">
						<a href="{{url('invoices')}}"
							class="back-icon d-flex align-items-center fs-12 fw-medium mb-3 d-inline-flex">
							<span class=" d-flex justify-content-center align-items-center rounded-circle me-2">
								<i class="ti ti-arrow-left"></i>
							</span>
							Back to List
						</a>
						<div class="card">
							<div class="card-body">
								<div class="row justify-content-between align-items-center border-bottom mb-3">
									<div class="col-md-6">
										<div class="mb-2 invoice-logo">
											<img src="{{URL::asset('build/img/logo.svg')}}" width="130" class="img-fluid logo"
												alt="logo">
											<img src="{{URL::asset('build/img/logo-white.svg')}}" width="130"
												class="img-fluid logo-white" alt="logo">
										</div>
										<p>3099 Kennedy Court Framingham, MA 01702</p>
									</div>
									<div class="col-md-6">
										<div class=" text-end mb-3">
											<h5 class="text-gray mb-1">Invoice No <span
													class="text-primary">#INV0001</span></h5>
											<p class="mb-1 fw-medium">Created Date : <span class="text-dark">Sep 24,
													2023</span> </p>
											<p class="fw-medium">Due Date : <span class="text-dark">Sep 30,
													2023</span> </p>
										</div>
									</div>
								</div>
								<div class="row border-bottom mb-3">
									<div class="col-md-5">
										<p class="text-dark mb-2 fw-semibold">From</p>
										<div>
											<h4 class="mb-1">Thomas Lawler</h4>
											<p class="mb-1">2077 Chicago Avenue Orosi, CA 93647</p>
											<p class="mb-1">Email : <span
													class="text-dark">Tarala2445@example.com</span></p>
											<p>Phone : <span class="text-dark">+1 987 654 3210</span></p>
										</div>
									</div>
									<div class="col-md-5">
										<p class="text-dark mb-2 fw-semibold">To</p>
										<div>
											<h4 class="mb-1">Sara Inc,.</h4>
											<p class="mb-1">3103 Trainer Avenue Peoria, IL 61602</p>
											<p class="mb-1">Email : <span
													class="text-dark">Sara_inc34@example.com</span></p>
											<p>Phone : <span class="text-dark">+1 987 471 6589</span></p>
										</div>
									</div>
									<div class="col-md-2">
										<div class="mb-3">
											<p class="text-title mb-2 fw-medium">Payment Status </p>
											<span class="badge badge-danger align-items-center mb-3"><i
													class="ti ti-point-filled "></i>Due in 10 Days</span>
											<div>
												<img src="{{URL::asset('build/img/qr.svg')}}" class="img-fluid" alt="QR">
											</div>
										</div>
									</div>
								</div>
								<div>
									<p class="fw-medium">Invoice For : <span class="text-dark fw-medium">Design &
											development of Website</span></p>
									<div class="table-responsive mb-3">
										<table class="table">
											<thead class="thead-light">
												<tr>
													<th>Job Description</th>
													<th class="text-end">Qty</th>
													<th class="text-end">Cost</th>
													<th class="text-end">Discount</th>
													<th class="text-end">Total</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>
														<h6>UX Strategy</h6>
													</td>
													<td class="text-gray-9 fw-medium text-end">1</td>
													<td class="text-gray-9 fw-medium text-end">$500</td>
													<td class="text-gray-9 fw-medium text-end">$100</td>
													<td class="text-gray-9 fw-medium text-end">$500</td>
												</tr>
												<tr>
													<td>
														<h6>Design System</h6>
													</td>
													<td class="text-gray-9 fw-medium text-end">1</td>
													<td class="text-gray-9 fw-medium text-end">$5000</td>
													<td class="text-gray-9 fw-medium text-end">$100</td>
													<td class="text-gray-9 fw-medium text-end">$5000</td>
												</tr>
												<tr>
													<td>
														<h6>Brand Guidellines</h6>
													</td>
													<td class="text-gray-9 fw-medium text-end">1</td>
													<td class="text-gray-9 fw-medium text-end">$5000</td>
													<td class="text-gray-9 fw-medium text-end">$100</td>
													<td class="text-gray-9 fw-medium text-end">$5000</td>
												</tr>
												<tr>
													<td>
														<h6>Social Media Template</h6>
													</td>
													<td class="text-gray-9 fw-medium text-end">1</td>
													<td class="text-gray-9 fw-medium text-end">$5000</td>
													<td class="text-gray-9 fw-medium text-end">$100</td>
													<td class="text-gray-9 fw-medium text-end">$5000</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<div class="row border-bottom mb-3">
									<div class="col-md-7">
										<div class="py-4">
											<div class="mb-3">
												<h6 class="mb-1">Terms and Conditions</h6>
												<p>Please pay within 15 days from the date of invoice, overdue
													interest @ 14% will be charged on delayed payments.</p>
											</div>
											<div class="mb-3">
												<h6 class="mb-1">Notes</h6>
												<p>Please quote invoice number when remitting funds.</p>
											</div>
										</div>
									</div>
									<div class="col-md-5">
										<div
											class="d-flex justify-content-between align-items-center border-bottom mb-2 pe-3">
											<p class="mb-0">Sub Total</p>
											<p class="text-dark fw-medium mb-2">$5500</p>
										</div>
										<div
											class="d-flex justify-content-between align-items-center border-bottom mb-2 pe-3">
											<p class="mb-0">Discount(0%)</p>
											<p class="text-dark fw-medium mb-2">$400</p>
										</div>
										<div class="d-flex justify-content-between align-items-center mb-2 pe-3">
											<p class="mb-0">VAT(5%)</p>
											<p class="text-dark fw-medium mb-2">$54</p>
										</div>
										<div class="d-flex justify-content-between align-items-center mb-2 pe-3">
											<h5>Total Amount</h5>
											<h5>$5775</h5>
										</div>
										<p class="fs-12">
											Amount in Words : Dollar Five thousand Seven Seventy Five
										</p>
									</div>
								</div>
								<div class="row justify-content-end align-items-end text-end border-bottom mb-3">
									<div class="col-md-3">
										<div class="text-end signature-img">
											<img src="{{URL::asset('build/img/sign.svg')}}" class="img-fluid" alt="sign">
										</div>
										<div class="text-end mb-3">
											<h6 class="fs-14 fw-medium pe-3">Ted M. Davis</h6>
											<p>Assistant Manager</p>
										</div>
									</div>
								</div>
								<div class="text-center">
									<div class="mb-3 invoice-logo d-flex align-items-center justify-content-center">
										<img src="{{URL::asset('build/img/logo.svg')}}" class="img-fluid logo" alt="logo">
										<img src="{{URL::asset('build/img/logo-white.svg')}}" class="img-fluid logo-white d-none"
											alt="logo">
									</div>
									<p class="text-dark mb-1">Payment Made Via bank transfer / Cheque in the name of
										Thomas Lawler</p>
									<div class="d-flex justify-content-center align-items-center">
										<p class="fs-12 mb-0 me-3">Bank Name : <span class="text-dark">HDFC
												Bank</span></p>
										<p class="fs-12 mb-0 me-3">Account Number : <span
												class="text-dark">45366287987</span></p>
										<p class="fs-12">IFSC : <span class="text-dark">HDFC0018159</span></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Invoices -->

			<div class="d-flex justify-content-center align-items-center mb-4">
				<a href="#" class="btn btn-primary d-flex justify-content-center align-items-center me-2"><i
						class="ti ti-printer me-2"></i>Print Invoice</a>
				<a href="#" class="btn btn-white d-flex justify-content-center align-items-center border"><i
						class="ti ti-copy me-2"></i>Clone Invoice</a>
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection