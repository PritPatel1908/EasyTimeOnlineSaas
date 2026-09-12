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
					<h2 class="mb-1">Projects</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Projects
							</li>
							<li class="breadcrumb-item active" aria-current="page">Projects</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('projects')}}" class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('projects-grid')}}" class="btn btn-icon btn-sm"><i
									class="ti ti-layout-grid"></i></a>
						</div>
					</div>
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_project"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Project</a>
					</div>
					<div class="ms-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- Project list -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Project List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
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
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-001</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Office Management
												App</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-39.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Michael
														Walker</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+1
											</a>
										</div>
									</td>
									<td>
										12 Sep 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-danger"></i></span> High
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-002</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Clinic Management</a>
										</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Brian
														Villalobos</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+1
											</a>
										</div>
									</td>
									<td>
										24 Oct 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-success"></i></span> Low
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-003</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Educational
												Platform</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Harvey Smith</a>
												</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-11.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+1
											</a>
										</div>
									</td>
									<td>
										18 Feb 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-warning"></i></span> Medium
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-004</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Chat & Call Mobile
												App</a> </h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Stephan
														Peralt</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+3
											</a>
										</div>
									</td>
									<td>
										17 Oct 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-warning"></i></span> Medium
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-005</a></td>
									<td>
										<h6 class="fw-medium"> <a href="{{url('project-details')}}">Travel Planning
												Website</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-34.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Doglas
														Martini</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+2
											</a>
										</div>
									</td>
									<td>
										20 Jul 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-warning"></i></span> Medium
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-006</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Service Booking
												Software</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Linda Ray</a>
												</h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+4
											</a>
										</div>
									</td>
									<td>
										10 Apr 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-danger"></i></span> High
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-007</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Hotel Booking App</a>
										</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-35.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Elliot
														Murray</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-21.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+4
											</a>
										</div>
									</td>
									<td>
										10 Apr 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-warning"></i></span> Medium
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-008</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Car & Bike Rental
												Software</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-36.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Rebecca
														Smtih</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-25.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-26.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+2
											</a>
										</div>
									</td>
									<td>
										22 Feb 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-success"></i></span> Low
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-009</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">Food Order App</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-37.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Connie
														Waters</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+3
											</a>
										</div>
									</td>
									<td>
										03 Nov 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-warning"></i></span> Medium
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('project-details')}}">PRO-010</a></td>
									<td>
										<h6 class="fw-medium"><a href="{{url('project-details')}}">POS Admin Software</a>
										</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="javascript:void(0);"
												class="avatar avatar-sm border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal"><a href="javascript:void(0);">Lori
														Broaddus</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-30.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}" alt="img">
											</span>
											<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
												href="javascript:void(0);">
												+4
											</a>
										</div>
									</td>
									<td>
										17 Dec 2024
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-danger"></i></span> High
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_project"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- / Project list  -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection