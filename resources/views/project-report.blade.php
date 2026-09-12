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
					<h2 class="mb-1">Project Report</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Reports
							</li>
							<li class="breadcrumb-item active" aria-current="page">Project Report</li>
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
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate mb-1">Total Projects</span>
											<h5>300</h5>
										</div>
										<div class="progress" role="progressbar" aria-label="Basic example"
											aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
											style="width: 100%;height: 5px;">
											<div class="progress-bar bg-pink" style="width: 70%"></div>
										</div>
									</div>
									<div class="d-flex mt-2">
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+10.54%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate mb-1">Completed
												Projects</span>
											<h5>250</h5>
										</div>
										<div class="progress" role="progressbar" aria-label="Basic example"
											aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
											style="width: 100%;height: 5px;">
											<div class="progress-bar bg-success" style="width: 80%"></div>
										</div>
									</div>
									<div class="d-flex mt-2">
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+12.84%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate mb-1">Pending Projects</span>
											<h5>50</h5>
										</div>
										<div class="progress" role="progressbar" aria-label="Basic example"
											aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
											style="width: 100%;height: 5px;">
											<div class="progress-bar bg-danger" style="width: 20%"></div>
										</div>
									</div>
									<div class="d-flex mt-2">
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-danger fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>-10.75%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div>
										<div class="mb-2">
											<span class="fs-14 fw-normal text-truncate mb-1">New Projects</span>
											<h5>30</h5>
										</div>
										<div class="progress" role="progressbar" aria-label="Basic example"
											aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
											style="width: 100%;height: 5px;">
											<div class="progress-bar bg-purple" style="width: 60%"></div>
										</div>
									</div>
									<div class="d-flex mt-2">
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+15.74%</span>from
											last month</p>
									</div>
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
								<div class="d-flex align-items-center ">
									<span class="me-2"><i class="ti ti-chart-pie text-danger"></i></span>
									<h5>Projects By Tasks</h5>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-sm fs-12 btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										Office Management App
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">PRO-001</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">PRO-002</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">PRO-004</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body pt-0">
							<div class="row align-items-center">
								<div class="col-md-6 d-flex justify-content-center">
									<div id="project-report">
									</div>
								</div>
								<div class="col-md-6">
									<div class="row gy-4">
										<div class="col-md-6">
											<p class="fs-16 project-report-badge-blue fw-normal mb-0 text-gray-5">
												Pending </p>
											<p class="fs-20 fw-bold text-dark ">30%</p>
										</div>
										<div class="col-md-6">
											<p
												class="fs-16 project-report-badge-purple mb-0  fw-normal text-gray-5">
												On Hold</p>
											<p class="fs-20 fw-bold text-dark ">10%</p>
										</div>
										<div class="col-md-6">
											<p
												class="fs-16 project-report-badge-warning  mb-0 fw-normal text-gray-5">
												Inprogress </p>
											<p class="fs-20 fw-bold text-dark ">20%</p>
										</div>
										<div class="col-md-6">
											<p
												class="fs-16 project-report-badge-success  mb-0 fw-normal text-gray-5">
												Completed</p>
											<p class="fs-20 fw-bold text-dark ">40%</p>
										</div>
									</div>
								</div>
							</div>


						</div>
					</div>
				</div>
				<!-- /Total Exponses -->


			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Project List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="me-3">
							<div class="input-icon-end position-relative">
								<input type="text" class="form-control date-range bookingrange"
									placeholder="dd/mm/yyyy - dd/mm/yyyy">
								<span class="input-icon-addon">
									<i class="ti ti-chevron-down"></i>
								</span>
							</div>
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
									<th>Project ID</th>
									<th>Project Name</th>
									<th>Leader</th>
									<th>Team</th>
									<th>Deadline</th>
									<th>Priority</th>
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
										PRO-001
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Office Management App</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Anthony Lewis</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-47.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-44.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+2
											</span>
										</div>
									</td>
									<td>
										12 Sep 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-002
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Clinic Management </a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Brian Villalobos</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-18.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+3
											</span>
										</div>
									</td>
									<td>
										24 Oct 2024
									</td>
									<td>
										<span class="badge badge-warning-transparent"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-003
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Educational Platform</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Harvey Smith</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-21.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-22.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+1
											</span>
										</div>
									</td>
									<td>
										18 Feb 2024
									</td>
									<td>
										<span class="badge badge-danger-transparent"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-004
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> Chat & Call Mobile App</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Stephan Peralt</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-26.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-35.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+3
											</span>
										</div>
									</td>
									<td>
										17 Oct 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-005
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> Travel Planning Website</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-34.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Doglas Martini</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-36.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+4
											</span>
										</div>
									</td>
									<td>
										20 Jul 2024
									</td>
									<td>
										<span class="badge badge-danger-transparent"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-006
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> Service Booking Software</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Linda Ray</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+5
											</span>
										</div>
									</td>
									<td>
										10 Apr 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-007
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> Hotel Booking App</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-22.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Elliot Murray</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-14.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-29.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-22.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+4
											</span>
										</div>
									</td>
									<td>
										29 Aug 2024
									</td>
									<td>
										<span class="badge badge-warning-transparent"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-008
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> Car & Bike Rental Software</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-36.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Rebecca Smtih</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-42.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+2
											</span>
										</div>
									</td>
									<td>
										22 Feb 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive
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
										PRO-009
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> Food Order App</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-37.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Connie Waters</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-26.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-40.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+1
											</span>
										</div>
									</td>
									<td>
										03 Nov 2024
									</td>
									<td>
										<span class="badge badge-warning-transparent"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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
										PRO-010
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#"> POS Admin Software</a></h6>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5">Lori Broaddus</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-13.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-18.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+3
											</span>
										</div>
									</td>
									<td>
										17 Dec 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
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