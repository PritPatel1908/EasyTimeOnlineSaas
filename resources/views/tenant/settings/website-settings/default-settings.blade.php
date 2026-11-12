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
								System Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Default Settings</li>
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
					<a class="nav-link" href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="{{url('default-settings')}}"><i class="ti ti-server-cog me-2"></i>System
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
								<a href="{{url('default-settings')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
									class="ti ti-arrow-badge-right me-2"></i>Default Settings</a>
								<a href="{{url('email-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Email Settings</a>
								<a href="{{url('email-template')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Email Templates</a>
								<a href="{{url('sms-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Settings</a>
								<a href="{{url('sms-template')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Templates</a>
								<a href="{{url('otp-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">OTP</a>
								<a href="{{url('gdpr')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">GDPR Cookies</a>
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
								<h4>Default Settings</h4>
							</div>
							<div class="business-tab-set" data-business-tab-set>
								<div class="profile-tabs-wrap mb-3">
									<div class="nav nav-tabs profile-tabs" role="tablist" aria-label="Default settings sections">
										<button type="button" class="nav-link active" id="business-default-general-tab" role="tab" aria-controls="business-default-general-panel" aria-selected="true" data-business-tab="general" data-business-form="default">Default Formats</button>
										<button type="button" class="nav-link" id="business-default-attendance-tab" role="tab" aria-controls="business-default-attendance-panel" aria-selected="false" data-business-tab="attendance" data-business-form="default">Default Configuration</button>
										<button type="button" class="nav-link" id="business-default-currency-tab" role="tab" aria-controls="business-default-currency-panel" aria-selected="false" data-business-tab="currency" data-business-form="default">Currency</button>
										<button type="button" class="nav-link" id="business-default-country-tab" role="tab" aria-controls="business-default-country-panel" aria-selected="false" data-business-tab="country" data-business-form="default">Country &amp; Files</button>
										<button type="button" class="nav-link" id="business-company-details-tab" role="tab" aria-controls="business-company-details-panel" aria-selected="false" data-business-tab="details" data-business-form="company">Basic Information</button>
										<button type="button" class="nav-link" id="business-company-branding-tab" role="tab" aria-controls="business-company-branding-panel" aria-selected="false" data-business-tab="branding" data-business-form="company">Company Images</button>
										<button type="button" class="nav-link" id="business-company-address-tab" role="tab" aria-controls="business-company-address-panel" aria-selected="false" data-business-tab="address" data-business-form="company">Address</button>
									</div>
								</div>
								<div class="profile-tab-panels">
							@php
								$datetimeFormatValue = old('datetime_format', $defaultSettings['datetime_format'] ?? '');
								$dateFormatValue = old('date_format', $defaultSettings['date_format'] ?? '');
								$timeFormatValue = old('time_format', $defaultSettings['time_format'] ?? '');
								$datetimeFormatValue = is_string($datetimeFormatValue) ? $datetimeFormatValue : '';
								$dateFormatValue = is_string($dateFormatValue) ? $dateFormatValue : '';
								$timeFormatValue = is_string($timeFormatValue) ? $timeFormatValue : '';
								$datetimeFormatSelection = array_key_exists($datetimeFormatValue, $formatPresets['datetime_format']) ? $datetimeFormatValue : ($datetimeFormatValue === '' ? '' : 'custom');
								$dateFormatSelection = array_key_exists($dateFormatValue, $formatPresets['date_format']) ? $dateFormatValue : ($dateFormatValue === '' ? '' : 'custom');
								$timeFormatSelection = array_key_exists($timeFormatValue, $formatPresets['time_format']) ? $timeFormatValue : ($timeFormatValue === '' ? '' : 'custom');
								$customDatetimeFormatValue = old('custom_datetime_format', $datetimeFormatSelection === 'custom' && $datetimeFormatValue !== 'custom' ? $datetimeFormatValue : '');
								$customDateFormatValue = old('custom_date_format', $dateFormatSelection === 'custom' && $dateFormatValue !== 'custom' ? $dateFormatValue : '');
								$customTimeFormatValue = old('custom_time_format', $timeFormatSelection === 'custom' && $timeFormatValue !== 'custom' ? $timeFormatValue : '');
								$customDatetimeFormatValue = is_string($customDatetimeFormatValue) ? $customDatetimeFormatValue : '';
								$customDateFormatValue = is_string($customDateFormatValue) ? $customDateFormatValue : '';
								$customTimeFormatValue = is_string($customTimeFormatValue) ? $customTimeFormatValue : '';
							@endphp
							<form action="{{url('default-settings')}}" method="POST" data-business-tab-form="default">
								@csrf
								@method('PUT')
								<section class="profile-tab-panel" id="business-default-general-panel" role="tabpanel" aria-labelledby="business-default-general-tab" data-business-panel="general">
									<h6 class="mb-3">Default Formats</h6>
									<div class="row">
										{{-- <div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Language</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>English</option>
														<option>French</option>
														<option>Spanish</option>
													</select>
													<p class="fs-13 fw-normal mt-2 form-check form-check-md form-switch me-2">
														<input class="form-check-input me-2" type="checkbox" role="switch">Language Switcher
													</p>
												</div>
											</div>
										</div> --}}
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-2">
													<label class="form-label mb-md-0" for="datetime-format-select">DateTime Format</label>
												</div>
												<div class="col-12 col-md-10" id="datetime-format-select-column">
													<select class="select" id="datetime-format-select" name="datetime_format">
														<option value="" @selected($datetimeFormatSelection === '')>Select</option>
														@foreach ($formatPresets['datetime_format'] as $formatValue => $formatLabel)
															<option value="{{ $formatValue }}" @selected($datetimeFormatSelection === $formatValue)>{{ $formatLabel }}</option>
														@endforeach
														<option value="custom" @selected($datetimeFormatSelection === 'custom')>Custom</option>
													</select>
												</div>
												<div class="col-12 col-md-5" id="custom-datetime-format-field" hidden>
													<label class="visually-hidden" for="custom-datetime-format">Custom DateTime Format</label>
													<input class="form-control" id="custom-datetime-format" name="custom_datetime_format" type="text" placeholder="e.g. d/m/Y H:i" value="{{ $customDatetimeFormatValue }}">
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-2">
													<label class="form-label mb-md-0" for="time-format-select">Time Format</label>
												</div>
												<div class="col-12 col-md-10" id="time-format-select-column">
													<select class="select" id="time-format-select" name="time_format">
														<option value="" @selected($timeFormatSelection === '')>Select</option>
														@foreach ($formatPresets['time_format'] as $formatValue => $formatLabel)
															<option value="{{ $formatValue }}" @selected($timeFormatSelection === $formatValue)>{{ $formatLabel }}</option>
														@endforeach
														<option value="custom" @selected($timeFormatSelection === 'custom')>Custom</option>
													</select>
												</div>
												<div class="col-12 col-md-5" id="custom-time-format-field" hidden>
													<label class="visually-hidden" for="custom-time-format">Custom Time Format</label>
													<input class="form-control" id="custom-time-format" name="custom_time_format" type="text" placeholder="e.g. H:i" value="{{ $customTimeFormatValue }}">
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-2">
													<label class="form-label mb-md-0" for="date-format-select">Date Format</label>
												</div>
												<div class="col-12 col-md-10" id="date-format-select-column">
													<select class="select" id="date-format-select" name="date_format">
														<option value="" @selected($dateFormatSelection === '')>Select</option>
														@foreach ($formatPresets['date_format'] as $formatValue => $formatLabel)
															<option value="{{ $formatValue }}" @selected($dateFormatSelection === $formatValue)>{{ $formatLabel }}</option>
														@endforeach
														<option value="custom" @selected($dateFormatSelection === 'custom')>Custom</option>
													</select>
												</div>
												<div class="col-12 col-md-5" id="custom-date-format-field" hidden>
													<label class="visually-hidden" for="custom-date-format">Custom Date Format</label>
													<input class="form-control" id="custom-date-format" name="custom_date_format" type="text" placeholder="e.g. d/m/Y" value="{{ $customDateFormatValue }}">
												</div>
											</div>
										</div>
									</div>
								</section>
								<section class="profile-tab-panel" id="business-default-attendance-panel" role="tabpanel" aria-labelledby="business-default-attendance-tab" data-business-panel="attendance" hidden>
									<h6 class="mb-3">Default Configuration</h6>
									<div class="row">
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-3">
													<label class="form-label mb-md-0" for="punch-direction-consider-same">Duplicate Punch Period in minutes</label>
												</div>
												<div class="col-12 col-md-9">
													<input class="form-control" id="punch-direction-consider-same" name="punch_direction_consider_same" type="number" min="0" step="1" value="{{ old('punch_direction_consider_same', $defaultSettings['punch_direction_consider_same'] ?? '') }}" required>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-3">
													<label class="form-label mb-md-0" for="attendance-schedule-time">Daily Attendance Generate Time</label>
												</div>
												<div class="col-12 col-md-9">
													<input class="form-control" id="attendance-schedule-time" name="attendance_schedule_time" type="time" step="1" value="{{ old('attendance_schedule_time', $defaultSettings['attendance_schedule_time'] ?? '') }}" required>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-3">
													<label class="form-label mb-md-0" for="monthly-leave-check-date">Monthly Auto Leave Credit Date</label>
												</div>
												<div class="col-12 col-md-9">
													<input class="form-control" id="monthly-leave-check-date" name="monthly_leave_check_date" type="number" min="1" max="31" step="1" value="{{ old('monthly_leave_check_date', $defaultSettings['monthly_leave_check_date'] ?? '') }}">
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-3">
													<label class="form-label mb-md-0" for="yearly-leave-check-date">Yearly Auto Leave Credit Date</label>
												</div>
												<div class="col-12 col-md-9">
													<input class="form-control" id="yearly-leave-check-date" name="yearly_leave_check_date" type="number" min="1" max="31" step="1" value="{{ old('yearly_leave_check_date', $defaultSettings['yearly_leave_check_date'] ?? '') }}">
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="row align-items-center mb-3">
												<div class="col-12 col-md-3">
													<label class="form-label mb-md-0" for="password-attempts-count">Maximum Wrong Password Attempts Allowed</label>
												</div>
												<div class="col-12 col-md-9">
													<input class="form-control" id="password-attempts-count" name="password_attempts_count" type="number" min="1" step="1" value="{{ old('password_attempts_count', $defaultSettings['password_attempts_count'] ?? '') }}" required>
												</div>
											</div>
										</div>
									</div>
								</section>
								<section class="profile-tab-panel" id="business-default-currency-panel" role="tabpanel" aria-labelledby="business-default-currency-tab" data-business-panel="currency" hidden>
									<h6 class="mb-3">Currency Information</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Currency</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>USD</option>
														<option>EURO</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Currency Symbol</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>$</option>
														<option>?</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Currency Position</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>100$</option>
														<option>$100</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Decimal Seperator</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>.</option>
														<option>,</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Thousand Seperator</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>.</option>
														<option>,</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</section>
								<section class="profile-tab-panel" id="business-default-country-panel" role="tabpanel" aria-labelledby="business-default-country-tab" data-business-panel="country" hidden>
									<h6 class="mb-3">Country Settings</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Countries Restriction</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>Allow All Countries</option>
														<option>Deny All Countries</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								<div class="border-bottom mb-3">
									<h6 class="mb-3">File Settings</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Allowed Files</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>jpg</option>
														<option>gif</option>
														<option>png</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Max File Size</label>
												</div>
												<div class="col-md-7">
													<input type="text" class="form-control" placeholder="5000 MB">
												</div>
											</div>
										</div>
										</div>
								</div>
								</section>
								<div class="d-flex align-items-center justify-content-end">
									<button type="button" class="btn btn-outline-light border me-3">Cancel</button>
									<button type="submit" class="btn btn-primary">Save</button>
								</div>
							</form>
							<form action="{{url('default-settings')}}" data-business-tab-form="company" hidden>
								<section class="profile-tab-panel" id="business-company-details-panel" role="tabpanel" aria-labelledby="business-company-details-tab" data-business-panel="details">
									<div class="row">
										<div class="col-md-12">
											<div>
												<h6 class="mb-3">Basic Information</h6>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">Company Name</label>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">Email Address</label>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">Phone</label>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">Fax</label>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="row align-items-center mb-3">
												<div class="col-md-2">
													<label class="form-label mb-md-0">Web</label>
												</div>
												<div class="col-md-10">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
									</div>
								</section>
								<section class="profile-tab-panel" id="business-company-branding-panel" role="tabpanel" aria-labelledby="business-company-branding-tab" data-business-panel="branding" hidden>
									<h6 class="mb-3">Company Images</h6>
									<div class="row">
										<div class="col-md-6">
											<div
												class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
												<div
													class="d-flex align-items-center justify-content-center avatar avatar-xxl bg-white rounded border border-dashed me-2 flex-shrink-0 text-dark frames px-2">
													<img src="{{URL::asset('build/img/logo.svg')}}" class="img-fluid" alt="logo">
												</div>
												<div class="profile-upload">
													<div class="mb-2">
														<h6 class="mb-1">White Logo</h6>
														<p class="fs-12">Recommended image size is 160px x 50px</p>
													</div>
													<div class="profile-uploader d-flex align-items-center">
														<div class="drag-upload-btn btn btn-sm btn-primary me-2">
															Change
															<input type="file" class="form-control image-sign"
																multiple="">
														</div>
														<a href="javascript:void(0);"
															class="btn btn-light btn-sm">Cancel</a>
													</div>

												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div
												class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
												<div
													class="d-flex align-items-center justify-content-center avatar bg-dark avatar-xxl rounded border border-dashed me-2 px-2 flex-shrink-0 text-dark frames">
													<img src="{{URL::asset('build/img/logo-white.svg')}}"
														class="img-fluid text-white" alt="logo">
												</div>
												<div class="profile-upload">
													<div class="mb-2">
														<h6 class="mb-1">Dark Logo</h6>
														<p class="fs-12">Recommended image size is 160px x 50px</p>
													</div>
													<div class="profile-uploader d-flex align-items-center">
														<div class="drag-upload-btn btn btn-sm btn-primary me-2">
															Change
															<input type="file" class="form-control image-sign"
																multiple="">
														</div>
														<a href="javascript:void(0);"
															class="btn btn-light btn-sm">Cancel</a>
													</div>

												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div
												class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
												<div
													class="d-flex align-items-center justify-content-center avatar avatar-xxl bg-white rounded border border-dashed me-2 p-3 flex-shrink-0 text-dark frames">
													<img src="{{URL::asset('build/img/logo-small.svg')}}" class="img-fluid"
														alt="logo">
												</div>
												<div class="profile-upload">
													<div class="mb-2">
														<h6 class="mb-1">White Mini Logo</h6>
														<p class="fs-12">Recommended image size is 80px x 80px</p>
													</div>
													<div class="profile-uploader d-flex align-items-center">
														<div class="drag-upload-btn btn btn-sm btn-primary me-2">
															Change
															<input type="file" class="form-control image-sign"
																multiple="">
														</div>
														<a href="javascript:void(0);"
															class="btn btn-light btn-sm">Cancel</a>
													</div>

												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div
												class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
												<div
													class="d-flex align-items-center justify-content-center avatar avatar-xxl bg-dark rounded border border-dashed me-2 flex-shrink-0 text-dark frames">
													<i class="ti ti-photo text-gray-3 fs-16"></i>
												</div>
												<div class="profile-upload">
													<div class="mb-2">
														<h6 class="mb-1">Dark Mini Logo</h6>
														<p class="fs-12">Recommended image size is 80px x 80px</p>
													</div>
													<div class="profile-uploader d-flex align-items-center">
														<div class="drag-upload-btn btn btn-sm btn-primary me-2">
															Upload
															<input type="file" class="form-control image-sign"
																multiple="">
														</div>
														<a href="javascript:void(0);"
															class="btn btn-light btn-sm">Cancel</a>
													</div>

												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div
												class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
												<div
													class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded bg-white p-3 border border-dashed me-2 flex-shrink-0 text-dark frames">
													<img src="{{URL::asset('build/img/logo-small.svg')}}" class="img-fluid"
														alt="logo">
												</div>
												<div class="profile-upload">
													<div class="mb-2">
														<h6 class="mb-1">Favicon</h6>
														<p class="fs-12">Recommended image size is 128px x 128px</p>
													</div>
													<div class="profile-uploader d-flex align-items-center">
														<div class="drag-upload-btn btn btn-sm btn-primary me-2">
															Change
															<input type="file" class="form-control image-sign"
																multiple="">
														</div>
														<a href="javascript:void(0);"
															class="btn btn-light btn-sm">Cancel</a>
													</div>

												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div
												class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
												<div
													class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded bg-white p-3 border border-dashed me-2 flex-shrink-0 text-dark frames">
													<img src="{{URL::asset('build/img/logo-small.svg')}}" class="img-fluid"
														alt="logo">
												</div>
												<div class="profile-upload">
													<div class="mb-2">
														<h6 class="mb-1">Apple Icon</h6>
														<p class="fs-12">Recommended image size is 180px x 180px</p>
													</div>
													<div class="profile-uploader d-flex align-items-center">
														<div class="drag-upload-btn btn btn-sm btn-primary me-2">
															Change
															<input type="file" class="form-control image-sign"
																multiple="">
														</div>
														<a href="javascript:void(0);"
															class="btn btn-light btn-sm">Cancel</a>
													</div>

												</div>
											</div>
										</div>
									</div>
								</section>
								<section class="profile-tab-panel" id="business-company-address-panel" role="tabpanel" aria-labelledby="business-company-address-tab" data-business-panel="address" hidden>
									<h6 class="mb-3">Address Information</h6>
									<div class="row">
										<div class="col-md-12">
											<div class="row align-items-center mb-3">
												<div class="col-md-2">
													<label class="form-label mb-md-0">Address</label>
												</div>
												<div class="col-md-10">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">Country</label>
												</div>
												<div class="col-md-8">
													<div>
														<select class="select">
															<option>Select</option>
															<option>USA</option>
															<option>Canada</option>
															<option>Germany</option>
															<option>France</option>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">State</label>
												</div>
												<div class="col-md-8">
													<div>
														<select class="select">
															<option>Select</option>
															<option>California</option>
															<option>New York</option>
															<option>Texas</option>
															<option>Florida</option>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">City</label>
												</div>
												<div class="col-md-8">
													<select class="select">
														<option>Select</option>
														<option>Los Angeles</option>
														<option>San Diego</option>
														<option>Fresno</option>
														<option>San Francisco</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-4">
													<label class="form-label mb-md-0">Postal Code</label>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
								</div>
									</section>
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
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

	<script>
		document.querySelectorAll('[data-business-tab-set]').forEach(function (tabSet) {
			const buttons = Array.from(tabSet.querySelectorAll('[data-business-tab]'));
			const panels = Array.from(tabSet.querySelectorAll('[data-business-panel]'));
			const forms = Array.from(tabSet.querySelectorAll('[data-business-tab-form]'));

			function setActiveBusinessTab(tab, formName) {
				buttons.forEach(function (button) {
					const active = button.dataset.businessTab === tab;
					button.classList.toggle('active', active);
					button.setAttribute('aria-selected', active ? 'true' : 'false');
					button.tabIndex = active ? 0 : -1;
				});
				panels.forEach(function (panel) {
					const active = panel.dataset.businessPanel === tab;
					panel.hidden = !active;
					panel.classList.toggle('active', active);
				});
				forms.forEach(function (form) {
					form.hidden = form.dataset.businessTabForm !== formName;
				});
			}

			buttons.forEach(function (button, index) {
				button.addEventListener('click', function () {
					setActiveBusinessTab(button.dataset.businessTab, button.dataset.businessForm);
				});
				button.addEventListener('keydown', function (event) {
					let nextIndex = index;
					if (event.key === 'ArrowRight') {
						nextIndex = (index + 1) % buttons.length;
					} else if (event.key === 'ArrowLeft') {
						nextIndex = (index - 1 + buttons.length) % buttons.length;
					} else if (event.key === 'Home') {
						nextIndex = 0;
					} else if (event.key === 'End') {
						nextIndex = buttons.length - 1;
					} else {
						return;
					}

					event.preventDefault();
					buttons[nextIndex].focus();
					setActiveBusinessTab(buttons[nextIndex].dataset.businessTab, buttons[nextIndex].dataset.businessForm);
				});
			});

			if (buttons.length > 0) {
				setActiveBusinessTab(buttons[0].dataset.businessTab, buttons[0].dataset.businessForm);
			}
		});

		[
			['datetime-format-select', 'datetime-format-select-column', 'custom-datetime-format-field', 'custom-datetime-format'],
			['time-format-select', 'time-format-select-column', 'custom-time-format-field', 'custom-time-format'],
			['date-format-select', 'date-format-select-column', 'custom-date-format-field', 'custom-date-format']
		].forEach(function (ids) {
			const formatSelect = document.getElementById(ids[0]);
			const formatSelectColumn = document.getElementById(ids[1]);
			const customFormatField = document.getElementById(ids[2]);
			const customFormatInput = document.getElementById(ids[3]);

			if (formatSelect && formatSelectColumn && customFormatField && customFormatInput) {
				function toggleCustomFormat() {
					const isCustom = formatSelect.value === 'custom';
					customFormatField.hidden = !isCustom;
					customFormatInput.required = isCustom;
					formatSelectColumn.classList.toggle('col-md-10', !isCustom);
					formatSelectColumn.classList.toggle('col-md-5', isCustom);
				}

				formatSelect.addEventListener('change', toggleCustomFormat);
				toggleCustomFormat();
			}
		});
	</script>
	<style>
		.business-tab-set .profile-tabs-wrap {
			overflow: visible;
		}

		.business-tab-set .profile-tabs {
			display: flex;
			flex-wrap: wrap;
			gap: 0.35rem;
			border-bottom: 1px solid #dee2e6;
			overflow: visible;
		}

		.business-tab-set .profile-tabs .nav-link {
			flex: 0 1 auto;
			white-space: normal;
			border-radius: 0.375rem 0.375rem 0 0;
		}

		.business-tab-set .profile-tab-panel .row {
			--bs-gutter-x: 1rem;
		}

		.business-tab-set .profile-tab-panel input,
		.business-tab-set .profile-tab-panel select {
			min-width: 0;
		}

		.business-tab-set .profile-tab-panel[hidden] {
			display: none !important;
		}

		.business-tab-set [data-business-tab-form][hidden] {
			display: none !important;
		}
	</style>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection