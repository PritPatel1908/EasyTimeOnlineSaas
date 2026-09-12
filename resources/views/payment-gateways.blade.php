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
							<li class="breadcrumb-item active" aria-current="page">Payment Gateways</li>
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
									class="d-inline-flex align-items-center active rounded py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Payment Gateways</a>
								<a href="{{url('tax-rates')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Tax Rates</a>
								<a href="{{url('currencies')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Currencies</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body pb-1">
							<div class="border-bottom mb-3 pb-3">
								<h4>Payment Gateways</h4>
							</div>
							<form action="{{url('payment-gateways')}}">
								<div class="row">
									<div class="col-xxl-6 col-xl-6 d-flex">
										<div class="card mb-3 flex-fill">
											<div
												class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
												<span
													class="d-inline-flex align-items-center justify-content-center border rounded p-2"><img
														src="{{URL::asset('build/img/payment-gateway/payment-gateway-01.svg')}}"
														alt="Img"></span>
												<div class="d-flex align-items-center">
													<div class="status-toggle modal-status">
														<input type="checkbox" id="user1" class="check" checked>
														<label for="user1" class="checktoggle"> </label>
													</div>
												</div>
											</div>
											<div class="card-body pt-0">
												<p>PayPal is the faster, safer way to send and receive money or make
													an
													online payment.</p>
											</div>
											<div
												class="card-footer d-flex align-items-center justify-content-between">
												<a href="javascript:void(0);"
													class="btn btn-sm btn-outline-dark rounded">
													<i class="ti ti-checks me-2"></i>Connected</a>
												<a href="javascript:void(0);" data-bs-toggle="modal"
													data-bs-target="#connect_payment"><i
														class="ti ti-settings fs-24 fw-normal"></i></a>
											</div>
										</div>
									</div>
									<div class="col-xxl-6 col-xl-6 d-flex">
										<div class="card mb-3 flex-fill">
											<div
												class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
												<span
													class="d-inline-flex align-items-center justify-content-center border rounded p-2"><img
														src="{{URL::asset('build/img/payment-gateway/payment-gateway-02.svg')}}"
														alt="Img"></span>
												<div class="d-flex align-items-center">
													<div class="status-toggle modal-status">
														<input type="checkbox" id="user2" class="check">
														<label for="user2" class="checktoggle"> </label>
													</div>
												</div>
											</div>
											<div class="card-body pt-0">
												<p>APIs to accept credit cards, manage subscriptions, send money.
												</p>
											</div>
											<div
												class="card-footer d-flex align-items-center justify-content-between">
												<a href="javascript:void(0);"
													class="btn btn-sm btn-outline-dark rounded">
													<i class="ti ti-checks me-2"></i>Connected</a>
												<a href="javascript:void(0);" data-bs-toggle="modal"
													data-bs-target="#connect_payment"><i
														class="ti ti-settings fs-24 fw-normal"></i></a>
											</div>
										</div>
									</div>
									<div class="col-xxl-6 col-xl-6 d-flex">
										<div class="card mb-3 flex-fill">
											<div
												class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
												<span
													class="d-inline-flex align-items-center justify-content-center border rounded p-2"><img
														src="{{URL::asset('build/img/payment-gateway/payment-gateway-03.svg')}}"
														alt="Img"></span>
												<div class="d-flex align-items-center">
													<div class="status-toggle modal-status">
														<input type="checkbox" id="user6" class="check">
														<label for="user6" class="checktoggle"> </label>
													</div>
												</div>
											</div>
											<div class="card-body pt-0">
												<p>Allows send international money transfers and payments quickly
													with
													low fees.</p>
											</div>
											<div
												class="card-footer d-flex align-items-center justify-content-between">
												<a href="javascript:void(0);"
													class="btn btn-sm btn-outline-dark rounded">
													<i class="ti ti-checks me-2"></i>Connected</a>
												<a href="javascript:void(0);" data-bs-toggle="modal"
													data-bs-target="#connect_payment"><i
														class="ti ti-settings fs-24 fw-normal"></i></a>
											</div>
										</div>
									</div>
									<div class="col-xxl-6 col-xl-6 d-flex">
										<div class="card mb-3 flex-fill">
											<div
												class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
												<span
													class="d-inline-flex align-items-center justify-content-center border rounded p-2"><img
														src="{{URL::asset('build/img/payment-gateway/payment-gateway-04.svg')}}"
														alt="Img"></span>
												<div class="d-flex align-items-center">
													<div class="status-toggle modal-status">
														<input type="checkbox" id="user10" class="check">
														<label for="user10" class="checktoggle"> </label>
													</div>
												</div>
											</div>
											<div class="card-body pt-0">
												<p>Paytm stands for Pay through mobile and it is India's largest
													mobile
													payments.</p>
											</div>
											<div
												class="card-footer d-flex align-items-center justify-content-between">
												<a href="javascript:void(0);"
													class="btn btn-sm btn-outline-dark rounded">
													<i class="ti ti-checks me-2"></i>Connected</a>
												<a href="javascript:void(0);" data-bs-toggle="modal"
													data-bs-target="#connect_payment"><i
														class="ti ti-settings fs-24 fw-normal"></i></a>
											</div>
										</div>
									</div>
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