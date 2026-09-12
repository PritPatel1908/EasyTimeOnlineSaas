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
					<h2 class="mb-1">Ticket Report</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Tickets
							</li>
							<li class="breadcrumb-item active" aria-current="page">Ticket Report</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
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
					<div class="head-icons ms-2">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="row">

				<!-- Total Exponses -->
				<div class="col-lg-6 col-md-6 d-flex">
					<div class="row flex-fill">
						<div class="col-lg-4 col-md-6 d-flex">
							<div class="ticket-report-card flex-fill d-flex">
								<div class="card-content flex-fill">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate text-body mb-1">Total
												Projects</span>
											<h5>240</h5>
										</div>
									</div>
									<div class="d-flex mt-2">
										<span class="badge badge-success-transparent"><i
												class="ti ti-arrow-wave-right-up me-1"></i>+5.50%</span>
									</div>
								</div>
								<div class="ticket-report-card-icon">
									<i class="ti ti-ticket"></i>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6 d-flex">
							<div class="ticket-report-card flex-fill d-flex">
								<div class="card-content flex-fill">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate text-body mb-1">Open
												Tickets</span>
											<h5>35</h5>
										</div>
									</div>
									<div class="d-flex mt-2">
										<span class="badge badge-success-transparent"><i
												class="ti ti-arrow-wave-right-up me-1"></i>+2.10%</span>
									</div>
								</div>
								<div class="ticket-report-card-icon">
									<i class="ti ti-clock-hour-3"></i>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6 d-flex">
							<div class="ticket-report-card flex-fill d-flex">
								<div class="card-content flex-fill">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate text-body mb-1"> Pending
												Tickets</span>
											<h5>15</h5>
										</div>
									</div>
									<div class="d-flex mt-2">
										<span class="badge badge-danger-transparent"><i
												class="ti ti-arrow-wave-right-up me-1"></i>+3.40%</span>
									</div>
								</div>
								<div class="ticket-report-card-icon">
									<i class="ti ti-hourglass-empty"></i>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6 d-flex">
							<div class="ticket-report-card flex-fill d-flex">
								<div class="card-content flex-fill">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate text-body mb-1">Resolved
												Tickets</span>
											<h5>170</h5>
										</div>
									</div>
									<div class="d-flex mt-2">
										<span class="badge badge-success-transparent"><i
												class="ti ti-arrow-wave-right-up me-1"></i>+4.30%</span>
									</div>
								</div>
								<div class="ticket-report-card-icon">
									<i class="ti ti-checklist"></i>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6 d-flex">
							<div class="ticket-report-card flex-fill d-flex">
								<div class="card-content flex-fill">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate text-body mb-1">SLA
												Breached</span>
											<h5>08</h5>
										</div>
									</div>
									<div class="d-flex mt-2">
										<span class="badge badge-success-transparent"><i
												class="ti ti-arrow-wave-right-up me-1"></i>+1.20%</span>
									</div>
								</div>
								<div class="ticket-report-card-icon">
									<i class="ti ti-alert-triangle"></i>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-md-6 d-flex">
							<div class="ticket-report-card flex-fill d-flex">
								<div class="card-content flex-fill">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate text-body mb-1">Tickets
												Escalated</span>
											<h5>12</h5>
										</div>
									</div>
									<div class="d-flex mt-2">
										<span class="badge badge-danger-transparent"><i
												class="ti ti-arrow-wave-right-up me-1"></i>+2.70%</span>
									</div>
								</div>
								<div class="ticket-report-card-icon">
									<i class="ti ti-user-share"></i>
								</div>
							</div>
						</div>
					</div>

				</div>
				<!-- /Total Exponses -->

				<!-- Total Exponses -->
				<div class="col-lg-6 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-header border-0">
							<div class="d-flex flex-wrap justify-content-between align-items-center">
								<div class="d-flex align-items-center">
									<h5><span class="me-2"><i class="ti ti-chart-bar text-danger"></i></span>Ticket
										Categories Vs Priority</h5>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-sm fs-12 btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										This Year
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2025</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body p-0">
							<div id="ticket-report"></div>
						</div>
					</div>
				</div>
				<!-- /Total Exponses -->


			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Ticket List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="me-3">
							<div class="input-icon position-relative">
								<span class="input-icon-addon">
									<i class="ti ti-calendar text-gray-9"></i>
								</span>
								<input type="text" class="form-control date-range bookingrange"
									placeholder="dd/mm/yyyy - dd/mm/yyyy">
							</div>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Tenant
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">BrightWave
										Innovations</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Stellar
										Dynamics</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Quantum Nexus</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Priority
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Low</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Medium</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">High</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
								</li>
							</ul>
						</div>
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Sort By : Last 7 Days
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card-body p-0">
					<div class="custom-datatable-filter table-responsive">
						<table class="table datatable">
							<thead class="thead-light">
								<tr>
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Ticket ID</th>
									<th>Description</th>
									<th>Priority</th>
									<th>Tenants</th>
									<th>Date</th>
									<th>Assignee</th>
									<th>Status</th>
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
										<a href="#">#TIC016</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Login not working</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-pink text-pink"><i
												class="ti ti-point-filled me-1"></i>Critical</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">BrightWave Innovations</a>
											</div>
										</div>
									</td>
									<td>
										15 Dec 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">William Parsons</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Closed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC015</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">HR module not loading</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-danger text-danger"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Stellar Dynamics</a>
											</div>
										</div>
									</td>
									<td>
										10 Dec 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Lucille Tomberlin</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Open
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC014</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Billing amount incorrect</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-warning text-warning"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Quantum Nexus</a>
											</div>
										</div>
									</td>
									<td>
										08 Dec 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Frederick Johnson</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Closed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC-013</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Session Expiring Quickly</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-danger text-danger"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">EcoVision Enterprises</a>
											</div>
										</div>
									</td>
									<td>
										01 Dec 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Sarah Henry</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Pending
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC012</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Unable to access dashboard</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-pink text-pink"><i
												class="ti ti-point-filled me-1"></i>Critical</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Aurora Technologies</a>
											</div>
										</div>
									</td>
									<td>
										26 Nov 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Thomas Miller</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Open
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC011</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Data Sync Issue</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-danger text-danger"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">BlueSky Ventures</a>
											</div>
										</div>
									</td>
									<td>
										18 Nov 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-06.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Melissa Shelton</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-warning d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>On Hold
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC010</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Widgets Missing</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-warning text-warning"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">TerraFusion Energy</a>
											</div>
										</div>
									</td>
									<td>
										03 Nov 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">James Rodriguez</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Open
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC009</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Payment Failed</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-danger text-danger"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">UrbanPulse Design</a>
											</div>
										</div>
									</td>
									<td>
										29 Oct 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Regina Chavez</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Closed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC008</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Subscription Not Updating</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-success text-success"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Nimbus Networks</a>
											</div>
										</div>
									</td>
									<td>
										25 Oct 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Joshua Dillon</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Pending
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<a href="#">#TIC007</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Data Export Blank</a></h6>
										</div>
									</td>
									<td>
										<span class="badge border border-danger text-danger"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Epicurean Delights</a>
											</div>
										</div>
									</td>
									<td>
										17 Oct 2025
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Diana Riddle</a>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-warning d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>On Hold
										</span>
									</td>
								</tr>
							</tbody>
						</table>
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