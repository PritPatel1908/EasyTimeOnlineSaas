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
				<div class="mb-2">
					<h6 class="fw-medium d-flex align-items-center"><a href="{{url('tenant-support-tickets')}}"><i
								class="ti ti-arrow-left me-2"></i>Ticket Details</a></h6>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
					<div class="me-2 mb-2">
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-file-export me-1"></i>Export
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
								</li>
							</ul>
						</div>
					</div>
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_ticket"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add New Ticket</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="row">
				<div class="col-xl-9 col-md-8">
					<div class="card">
						<div
							class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
							<h5 class="text-info fw-medium">Access Issue</h5>
							<div class="d-flex align-items-center">
								<span class="badge bg-danger me-3"><i
										class="ti ti-circle-filled fs-5 me-1"></i>High</span>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle px-2 py-1 btn btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										Mark as Private
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Mark as
												Private</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Mark as
												Public </a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body">
							<div>
								<div class="d-flex justify-content-between flex-wrap border-bottom mb-3">
									<div class="d-flex align-items-center flex-wrap">
										<div class="mb-3">
											<span class="badge badge-info rounded-pill mb-2">Tic - 001</span>
											<div class="d-flex align-items-center mb-2">
												<h5 class="fw-semibold me-2">Login not working</h5>
												<span
													class="badge bg-outline-pink d-flex align-items-center ms-1"><i
														class="ti ti-circle-filled fs-5 me-1"></i>Open</span>
											</div>
											<div class="d-flex align-items-center flex-wrap row-gap-2">
												<p class="d-flex align-items-center mb-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
														class="avatar avatar-xs rounded-circle me-2"
														alt="img">Assigned to <span class="text-dark ms-1">Edgar
														Hansel</span>
												</p>
												<p class="d-flex align-items-center mb-0 me-2"><i
														class="ti ti-calendar-bolt me-1"></i>Updated 10 hours ago
												</p>
												<p class="d-flex align-items-center mb-0"><i
														class="ti ti-message-circle-share me-1"></i>9 Comments</p>
											</div>
										</div>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-dark"><i
												class="ti ti-arrow-forward-up me-1"></i>Post Reply</a>
									</div>
								</div>
								<div class="border-bottom mb-3 pb-3">
									<div>
										<p class="mb-0">For the past two days, we have been unable to log in to our
											accounts. Every time we enter our correct email and password, the page
											either refreshes without any message or shows “Invalid Credentials.” We
											have confirmed that the credentials are correct, but the issue
											continues.</p>
										<p>We also attempted to reset our passwords, but none of us received the
											password reset email. We checked our inboxes, spam folders, and
											promotion folders no email arrived. This issue is preventing our team
											from accessing the dashboard and is affecting our daily operations.</p>
									</div>
									<div class="mt-4">
										<div class="d-flex align-items-center mb-3">
											<span class="avatar avatar-lg avatar-rounded me-2 flex-shrink-0"><img
													src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="Img"></span>
											<div>
												<h6 class="fw-medium mb-1">Michael Coleman</h6>
												<p><i class="ti ti-calendar-bolt me-1"></i>Updated 5 hours ago</p>
											</div>
										</div>
										<div>
											<div class="mb-3">
												<p>This issue is preventing access to the dashboard and blocking
													daily operations for our team</p>
											</div>
											<span class="badge bg-light fw-normal">Screenshot.png<i
													class="ti ti-download ms-1"></i></span>
											<div class="d-flex align-items-center mt-3">
												<a href="#"
													class="d-inline-flex align-items-center text-primary fw-medium me-3"><i
														class="ti ti-arrow-forward-up me-1"></i>Reply</a>
												<p><a href="#" class="d-flex align-items-center"><i
															class="ti ti-message-circle-share me-1"></i>1
														Comment</a></p>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-3 pb-3">
									<div>
										<div class="d-flex align-items-center mb-3">
											<span class="avatar avatar-ld avatar-rounded me-2 flex-shrink-0"><img
													src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="Img"></span>
											<div>
												<h6 class="mb-1">Edgar Hansel</h6>
												<p><i class="ti ti-calendar-bolt me-1"></i>Updated 5 hours ago</p>
											</div>
										</div>
										<div>
											<div class="mb-3">
												<p>Please verify if the authentication service is running normally.
													Also check if the user account is locked due to multiple failed
													attempts.</p>
											</div>
											<div class="d-flex align-items-center">
												<span class="badge bg-light fw-normal">Screenshot.png<i
														class="ti ti-download ms-1"></i></span>
											</div>
											<div class="d-flex align-items-center mt-3">
												<a href="#"
													class="d-inline-flex align-items-center text-primary fw-medium me-3"><i
														class="ti ti-arrow-forward-up me-1"></i>Reply</a>
												<p><a href="#" class="d-flex align-items-center"><i
															class="ti ti-message-circle-share me-1"></i>5
														Comments</a></p>
											</div>
										</div>
									</div>
								</div>
								<div>
									<div>
										<div class="d-flex align-items-center mb-3">
											<span class="avatar avatar-lg avatar-rounded me-2 flex-shrink-0"><img
													src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}" alt="Img"></span>
											<div>
												<h6 class="mb-1">James Hendriques</h6>
												<p><i class="ti ti-calendar-bolt me-1"></i>Updated 5 hours ago</p>
											</div>
										</div>
										<div>
											<div class="mb-3">
												<p>Check the email service logs to confirm if password reset emails
													failed or were blocked. Also confirm if SMTP configuration is
													active or not.</p>
											</div>
											<div class="d-flex align-items-center mt-3">
												<a href="#"
													class="d-inline-flex align-items-center text-primary fw-medium me-3"><i
														class="ti ti-arrow-forward-up me-1"></i>Reply</a>
												<p><a href="#" class="d-flex align-items-center"><i
															class="ti ti-message-circle-share me-1"></i>9
														Comments</a></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-4">
					<div class="card">
						<div class="card-header p-3">
							<h4>Ticket Info</h4>
						</div>
						<div class="card-body p-0">
							<div class="border-bottom p-3">
								<div class="mb-3">
									<label class="form-label">Change Priority</label>
									<select class="select">
										<option>High</option>
										<option>Medium</option>
										<option>Low</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="form-label">Assign To</label>
									<select class="select">
										<option>Edgar Hansel</option>
										<option>Juan Hermann</option>
									</select>
								</div>
								<div>
									<label class="form-label">Ticket Status</label>
									<select class="select">
										<option>Open</option>
										<option>On Hold</option>
										<option>Reopened</option>
									</select>
								</div>
							</div>
							<div class="d-flex align-items-center border-bottom p-3">
								<span class="avatar avatar-md border p-1 rounded-circle me-2 flex-shrink-0"><img
										src="{{URL::asset('build/img/company/company-01.svg')}}" class="rounded-circle"
										alt="Img"></span>
								<div>
									<span class="fs-12">User</span>
									<p class="text-dark">BrightWave Innovations</p>
								</div>
							</div>
							<div class="d-flex align-items-center border-bottom p-3">
								<span class="avatar avatar-md me-2 flex-shrink-0"><img
										src="{{URL::asset('build/img/users/user-05.jpg')}}" class="rounded-circle" alt="Img"></span>
								<div>
									<span class="fs-12">Support Agent</span>
									<p class="text-dark">Edgar Hansel</p>
								</div>
							</div>
							<div class="border-bottom p-3">
								<span class="fs-12">Category</span>
								<p class="text-dark">Access Issue</p>
							</div>
							<div class="border-bottom p-3">
								<span class="fs-12">Email</span>
								<p class="text-dark">michael@example.com</p>
							</div>
							<div class="p-3">
								<span class="fs-12">Last Updated / Closed On</span>
								<p class="text-dark">15 Dec 2025</p>
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