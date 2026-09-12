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
					<h2 class="mb-1">Tenant Support Tickets</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Super Admin
							</li>
							<li class="breadcrumb-item active" aria-current="page">Tenant Support Tickets</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">

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
					<div class="ms-2 mb-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="row">
				<div class="col-md-6 col-xl-3 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="row justify-content-between">
								<div class="col-8">
									<div class="flex-fill">
										<div
											class="rounded d-inline-flex align-items-center justify-content-center mb-3">
											<span class="avatar avatar-lg rounded bg-primary-transparent "><i
													class="ti ti-ticket fs-20"></i></span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<h2 class="me-2">80</h2>
											<div
												class="d-flex flex-column justify-content-between align-items-center">
												<span
													class="badge bg-transparent-purple d-inline-flex align-items-center">
													<i class="ti ti-arrow-wave-right-down me-1"></i>
													+5.50%
												</span>
											</div>
										</div>
										<span class="fs-12 fw-medium text-gray-5">New Tickets</span>
									</div>
								</div>
								<div class="col-4">
									<div class="text-end">
										<div id="tenent-support-chart1"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-xl-3 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="row">
								<div class="col-8">
									<div class="flex-fill">
										<div
											class="rounded d-inline-flex align-items-center justify-content-center mb-3">
											<span class="avatar avatar-lg rounded bg-transparent-purple "><i
													class="ti ti-ticket fs-20"></i></span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<h2 class="me-2">25</h2>
											<div
												class="d-flex flex-column justify-content-between align-items-center">
												<span
													class="badge bg-transparent-purple d-inline-flex align-items-center">
													<i class="ti ti-arrow-wave-right-down me-1"></i>
													+5.50%
												</span>
											</div>
										</div>
										<span class="fs-12 fw-medium text-gray-5">Open Tickets</span>
									</div>
								</div>
								<div class="col-4">
									<div class="text-end">
										<div id="tenent-support-chart2"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-xl-3 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="row">
								<div class="col-8">
									<div class="flex-fill">
										<div
											class="rounded d-inline-flex align-items-center justify-content-center mb-3">
											<span
												class="avatar avatar-lg rounded bg-transparent-skyblue text-skyblue"><i
													class="ti ti-ticket fs-20"></i></span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<h2 class="me-2">40</h2>
											<div
												class="d-flex flex-column justify-content-between align-items-center">
												<span
													class="badge bg-transparent-purple d-inline-flex align-items-center">
													<i class="ti ti-arrow-wave-right-down me-1"></i>
													+5.50%
												</span>
											</div>
										</div>
										<span class="fs-12 fw-medium text-gray-5">Pending Tickets</span>
									</div>
								</div>
								<div class="col-4">
									<div class="text-end">
										<div id="tenent-support-chart4"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-xl-3 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="row">
								<div class="col-8">
									<div class="flex-fill">
										<div
											class="rounded d-inline-flex align-items-center justify-content-center mb-3">
											<span
												class="avatar avatar-lg rounded bg-transparent-success text-success"><i
													class="ti ti-ticket fs-20"></i></span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<h2 class="me-2">70</h2>
											<div
												class="d-flex flex-column justify-content-between align-items-center">
												<span
													class="badge bg-transparent-purple d-inline-flex align-items-center">
													<i class="ti ti-arrow-wave-right-down me-1"></i>
													+5.50%
												</span>
											</div>
										</div>
										<span class="fs-12 fw-medium text-gray-5">Solved Tickets</span>
									</div>
								</div>
								<div class="col-4">
									<div class="text-end">
										<div id="tenent-support-chart5"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
						<h5>Ticket List</h5>
						<div class="d-flex align-items-center flex-wrap row-gap-3">
							<div class="dropdown me-2">
								<a href="javascript:void(0);"
									class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
									data-bs-toggle="dropdown">
									Priority
								</a>
								<ul class="dropdown-menu  dropdown-menu-end p-3">
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Priority</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">High</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Low</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Medium</a>
									</li>
								</ul>
							</div>
							<div class="dropdown me-2">
								<a href="javascript:void(0);"
									class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
									data-bs-toggle="dropdown">
									Select Status
								</a>
								<ul class="dropdown-menu  dropdown-menu-end p-3">
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Open</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">On Hold</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Reopened</a>
									</li>
								</ul>
							</div>
							<div class="dropdown">
								<a href="javascript:void(0);"
									class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
									data-bs-toggle="dropdown">
									Sort By : Last 7 Days
								</a>
								<ul class="dropdown-menu  dropdown-menu-end p-3">
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Recently
											Added</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Descending</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7
											Days</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xl-9 col-md-8">
					<div class="card">
						<div class="card-body">
							<div class="row row-gap-3">
								<div class="col-xl-3 col-lg-4 col-md-4">
									<div class="bg-light border rounded p-4 text-center">
										<p class="fs-12 fw-medium text-gray-9 mb-2">#TIC0016</p>
										<span class="badge badge-danger mb-2"><i
												class="ti ti-point-filled me-1"></i>High</span>
										<p class="fs-12 fw-medium text-gray-9 mb-0">15 Dec 2025</p>
									</div>
								</div>
								<div class="col-xl-9 col-lg-8 col-md-8">
									<div
										class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
										<h5>Login not working <span
												class="badge rounded-pill badge-info fs-10 text-white ms-2">Access
												Issue</span></h5>
										<div class="d-flex align-items-center gap-2">
											<a href="{{url('tenant-ticket-details')}}"><i
													class="ti ti-eye fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_ticket"><i
													class="ti ti-edit fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash fs-16 text-gray-5"></i></a>
										</div>
									</div>
									<div
										class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-2">Ticket Raised By</h6>
											<div class="d-flex align-items-center gap-2">
												<span
													class="avatar avatar-md rounded-circle border p-1 flex-shrink-0">
													<img src="{{URL::asset('build/img/company/company-01.svg')}}"
														class="rounded-circle" alt="logo">
												</span>
												<p class="fw-medium text-gray-9 mb-0">BrightWave Innovations</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Assignee</h6>
											<div class="d-flex align-items-center gap-2">
												<img src="{{URL::asset('build/img/agents/agent-11.jpg')}}"
													class="avatar avatar-xs rounded-circle" alt="logo">
												<p class="fw-medium text-gray-9 mb-0">Edgar Hansel</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Status</h6>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<span
														class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
															class="ti ti-point-filled text-success"></i></span> Open
												</a>
												<ul class="dropdown-menu  dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-success"></i></span>Open</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-danger"></i></span>Close</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-purple d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-purple"></i></span>New</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-body">
							<div class="row row-gap-3">
								<div class="col-xl-3 col-lg-4 col-md-4">
									<div class="bg-light border rounded p-4 text-center">
										<p class="fs-12 fw-medium text-gray-9 mb-2">#TIC0015</p>
										<span class="badge badge-warning mb-2"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
										<p class="fs-12 fw-medium text-gray-9 mb-0">10 Dec 2025</p>
									</div>
								</div>
								<div class="col-xl-9 col-lg-8 col-md-8">
									<div
										class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
										<h5>HR module not loading <span
												class="badge rounded-pill badge-pink fs-10 text-white ms-2">Module
												Issue</span></h5>
										<div class="d-flex align-items-center gap-2">
											<a href="{{url('tenant-ticket-details')}}"><i
													class="ti ti-eye fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_ticket"><i
													class="ti ti-edit fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash fs-16 text-gray-5"></i></a>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between flex-wrap">
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-2">Ticket Raised By</h6>
											<div class="d-flex align-items-center gap-2">
												<span
													class="avatar avatar-md rounded-circle border p-1 flex-shrink-0">
													<img src="{{URL::asset('build/img/company/company-02.svg')}}"
														class="rounded-circle" alt="logo">
												</span>
												<p class="fw-medium text-gray-9 mb-0">Ann Lynch</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Assignee</h6>
											<div class="d-flex align-items-center gap-2">
												<img src="{{URL::asset('build/img/agents/agent-12.jpg')}}"
													class="avatar avatar-xs rounded-circle" alt="logo">
												<p class="fw-medium text-gray-9 mb-0">Edgar Hansel</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Status</h6>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<span
														class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
															class="ti ti-point-filled text-success"></i></span> Open
												</a>
												<ul class="dropdown-menu  dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-success"></i></span>Open</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-danger"></i></span>Close</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-purple d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-purple"></i></span>New</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-body">
							<div class="row row-gap-3">
								<div class="col-xl-3 col-lg-4 col-md-4">
									<div class="bg-light border rounded p-4 text-center">
										<p class="fs-12 fw-medium text-gray-9 mb-2">#TIC0014</p>
										<span class="badge badge-success mb-2"><i
												class="ti ti-point-filled me-1"></i>Low</span>
										<p class="fs-12 fw-medium text-gray-9 mb-0">08 Dec 2025</p>
									</div>
								</div>
								<div class="col-xl-9 col-lg-8 col-md-8">
									<div
										class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
										<h5>Unable to access dashboard <span
												class="badge rounded-pill badge-info fs-10 text-white ms-2">Access
												Issue</span></h5>
										<div class="d-flex align-items-center gap-2">
											<a href="{{url('tenant-ticket-details')}}"><i
													class="ti ti-eye fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_ticket"><i
													class="ti ti-edit fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash fs-16 text-gray-5"></i></a>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between flex-wrap">
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-2">Ticket Raised By</h6>
											<div class="d-flex align-items-center gap-2">
												<span
													class="avatar avatar-md rounded-circle border p-1 flex-shrink-0">
													<img src="{{URL::asset('build/img/company/company-03.svg')}}"
														class="rounded-circle" alt="logo">
												</span>
												<p class="fw-medium text-gray-9 mb-0">Aurora Technologies</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Assignee</h6>
											<div class="d-flex align-items-center gap-2">
												<img src="{{URL::asset('build/img/agents/agent-13.jpg')}}"
													class="avatar avatar-xs rounded-circle" alt="logo">
												<p class="fw-medium text-gray-9 mb-0">Juan Hermann</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Status</h6>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<span
														class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
															class="ti ti-point-filled text-success"></i></span> Open
												</a>
												<ul class="dropdown-menu  dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-success"></i></span>Open</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-danger"></i></span>Close</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-purple d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-purple"></i></span>New</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-body">
							<div class="row row-gap-3">
								<div class="col-xl-3 col-lg-4 col-md-4">
									<div class="bg-light border rounded p-4 text-center">
										<p class="fs-12 fw-medium text-gray-9 mb-2">#TIC0013</p>
										<span class="badge badge-warning mb-2"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
										<p class="fs-12 fw-medium text-gray-9 mb-0">02 Dec 2025</p>
									</div>
								</div>
								<div class="col-xl-9 col-lg-8 col-md-8">
									<div
										class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
										<h5>Billing amount incorrect<span
												class="badge rounded-pill badge-purple fs-10 text-white ms-2">Billing
												& Payments</span></h5>
										<div class="d-flex align-items-center gap-2">
											<a href="{{url('tenant-ticket-details')}}"><i
													class="ti ti-eye fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_ticket"><i
													class="ti ti-edit fs-16 text-gray-5"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash fs-16 text-gray-5"></i></a>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between flex-wrap">
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-2">Ticket Raised By</h6>
											<div class="d-flex align-items-center gap-2">
												<span
													class="avatar avatar-md rounded-circle border p-1 flex-shrink-0">
													<img src="{{URL::asset('build/img/company/company-04.svg')}}"
														class="rounded-circle" alt="logo">
												</span>
												<p class="fw-medium text-gray-9 mb-0">Quantum Nexus</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Assignee</h6>
											<div class="d-flex align-items-center gap-2">
												<img src="{{URL::asset('build/img/agents/agent-14.jpg')}}"
													class="avatar avatar-xs rounded-circle" alt="logo">
												<p class="fw-medium text-gray-9 mb-0">Jessie Otero</p>
											</div>
										</div>
										<div class="">
											<h6 class="fs-12 fw-normal text-gray-5 mb-3">Status</h6>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<span
														class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
															class="ti ti-point-filled text-success"></i></span> Open
												</a>
												<ul class="dropdown-menu  dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-success"></i></span>Open</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-danger"></i></span>Close</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
																class="rounded-circle bg-transparent-purple d-flex justify-content-center align-items-center me-2"><i
																	class="ti ti-point-filled text-purple"></i></span>New</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="text-center mb-4">
						<a href="#" class="btn btn-primary"><i class="ti ti-loader-3 me-1"></i>Load More</a>
					</div>
				</div>
				<div class="col-xl-3 col-md-4">
					<div class="card">
						<div class="card-header">
							<h4>Ticket Categories</h4>
						</div>
						<div class="card-body p-0">
							<div class="d-flex flex-column">
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<a href="javascript:void(0);">Access Issue</a>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">1</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<a href="javascript:void(0);">Module Issue</a>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">1</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<a href="javascript:void(0);">Billing & Payments</a>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">0</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<a href="javascript:void(0);">API / Integration Issues</a>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">2</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between p-3">
									<a href="javascript:void(0);">Plan / Subscription Issues</a>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">1</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-header">
							<h4>Support Agents</h4>
						</div>
						<div class="card-body p-0">
							<div class="d-flex flex-column">
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<span class="d-flex align-items-center">
										<img src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
											class="avatar avatar-xs rounded-circle me-2" alt="img">Edgar Hansel
									</span>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">0</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<span class="d-flex align-items-center">
										<img src="{{URL::asset('build/img/agents/agent-12.jpg')}}"
											class="avatar avatar-xs rounded-circle me-2" alt="img">Ann Lynch
									</span>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">1</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-bottom p-3">
									<span class="d-flex align-items-center">
										<img src="{{URL::asset('build/img/agents/agent-13.jpg')}}"
											class="avatar avatar-xs rounded-circle me-2" alt="img">Juan Hermann
									</span>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">0</span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between p-3">
									<span class="d-flex align-items-center">
										<img src="{{URL::asset('build/img/agents/agent-14.jpg')}}"
											class="avatar avatar-xs rounded-circle me-2" alt="img">Jessie Otero
									</span>
									<div class="d-flex align-items-center">
										<span class="badge badge-xs bg-dark rounded-circle">2</span>
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