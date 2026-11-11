@extends('layout.mainlayout')
@section('content')
	@php
		$activeTransport = old('transport', $emailConfiguration['transport'] ?? 'mail');
	@endphp

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
								System Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page"> Email Settings</li>
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
				<!-- <li class="nav-item">
					<a class="nav-link" href="{{url('business-settings')}}"><i class="ti ti-world-cog me-2"></i>Website
						Settings</a>
				</li> -->
				<li class="nav-item">
					<a class="nav-link" href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="{{url('email-settings')}}"><i
							class="ti ti-server-cog me-2"></i>System Settings</a>
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
								<a href="{{url('business-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Default Settings</a>
									<a href="{{url('email-settings')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Email Settings</a>								<a href="{{url('email-template')}}"
									class="d-inline-flex align-items-center rounded   py-2 px-3">Email Templates</a>
								<a href="{{url('sms-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Settings</a>
								<a href="{{url('sms-template')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Templates</a>
								<a href="{{url('otp-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">OTP</a>
								<a href="{{url('gdpr')}}" class="d-inline-flex align-items-center rounded py-2 px-3">GDPR
									Cookies</a>
								<a href="{{url('maintenance-mode')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Maintenance Mode</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3 pb-3">
								<h4>Email Settings</h4>
							</div>
							<form id="email-transport-form" method="POST" action="{{url('email-settings')}}">
								@csrf
								@method('PUT')
								<input type="hidden" name="transport" id="email-transport-value" value="{{ $activeTransport }}">
								<input type="hidden" name="from_address" value="{{ $emailConfiguration['from_address'] ?? '' }}">
								<input type="hidden" name="from_name" value="{{ $emailConfiguration['from_name'] ?? '' }}">
								<input type="hidden" name="host" value="{{ $emailConfiguration['host'] ?? '' }}">
								<input type="hidden" name="port" value="{{ $emailConfiguration['port'] ?? '' }}">
								<input type="hidden" name="username" value="{{ $emailConfiguration['username'] ?? '' }}">
								<input type="hidden" name="encryption" value="{{ $emailConfiguration['encryption'] ?? '' }}">
								<div class="border-bottom mb-3">
									<div class="row">

									</div>
									<div class="row">
										<div class="col-md-6 d-flex">
											<div class="card flex-fill">
												<div class="card-body">
													<div class="border-bottom pb-3 mb-3">
														<div
															class="d-flex justify-content-between align-items-center mb-2">
															<div class="d-flex align-items-center">
																<span
																	class="avatar avatar-xl p-2 me-2 bg-light flex-shrink-0">
																	<img src="{{URL::asset('build/img/settings/phpmail.svg')}}"
																		alt="Profile">
																</span>
																<h5>PHP Mailer</h5>
															</div>
															<div class="form-check form-switch">
																<input class="form-check-input" type="checkbox"
																	role="switch" id="flexSwitchCheckDefault"
																	@checked($activeTransport === 'mail')>
															</div>
														</div>
														<p>Used to send emails safely and easily via PHP code from a
															web server.</p>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<a href="#" data-bs-toggle="modal"
															data-bs-target="#phpmailersettings"
															class="btn btn-icon btn-sm text-gray-5 fs-20"><i
																class="ti ti-settings"></i></a>
													</div>

												</div>
											</div>
										</div>
										<div class="col-md-6 d-flex">
											<div class="card flex-fill">
												<div class="card-body">
													<div class="border-bottom pb-3 mb-3">
														<div
															class="d-flex justify-content-between align-items-center mb-2">
															<div class="d-flex align-items-center">
																<span
																	class="avatar avatar-xl me-2 p-2 bg-light flex-shrink-0">
																	<img src="{{URL::asset('build/img/settings/smtp.svg')}}"
																		alt="Profile">
																</span>
																<h5>SMTP</h5>
															</div>
															<div class="form-check form-switch">
																<input class="form-check-input" type="checkbox"
																	role="switch" id="flexSwitchCheckDefault2"
																	@checked($activeTransport === 'smtp')>
															</div>
														</div>
														<p>SMTP is used to send, relay or forward messages from a
															mail client.</p>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<a href="#" data-bs-toggle="modal"
															data-bs-target="#smtpsettings"
															class="btn btn-icon btn-sm text-gray-5 fs-20"><i
																class="ti ti-settings"></i></a>
													</div>

												</div>
											</div>
										</div>

									</div>
								</div>

							</form>
							<div class="d-flex align-items-center justify-content-end">
								<button type="submit" form="delete-email-settings-form" class="btn btn-outline-danger">Delete Configuration</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>
	<script>
		(function () {
			const phpMailerSwitch = document.getElementById('flexSwitchCheckDefault');
			const smtpSwitch = document.getElementById('flexSwitchCheckDefault2');
			const transportInput = document.getElementById('email-transport-value');

			function selectTransport(selectedSwitch, otherSwitch, transport) {
				selectedSwitch.addEventListener('change', function () {
					if (!selectedSwitch.checked && !otherSwitch.checked) {
						selectedSwitch.checked = true;
						return;
					}

					if (selectedSwitch.checked) {
						otherSwitch.checked = false;
						transportInput.value = transport;
					}
				});
			}

			selectTransport(phpMailerSwitch, smtpSwitch, 'mail');
			selectTransport(smtpSwitch, phpMailerSwitch, 'smtp');
		})();
	</script>
	<form id="delete-email-settings-form" method="POST" action="{{url('email-settings')}}" onsubmit="return confirm('Delete the saved email configuration?')">
		@csrf
		@method('DELETE')
	</form>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection