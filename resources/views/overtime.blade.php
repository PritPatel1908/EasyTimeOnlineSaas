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
					<h2 class="mb-1">Overtime</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Attendance
							</li>
							<li class="breadcrumb-item active" aria-current="page">Overtime</li>
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_overtime"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Overtime</a>
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

			<!-- Overtime Counts -->
			<div class="row">
				<div class="col-xl-3 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center flex-wrap justify-content-between">
								<div>
									<p class="fs-12 fw-medium mb-0 text-gray-5">Overtime Employee</p>
									<h4>12</h4>
								</div>
								<div>
									<span
										class="p-2 br-10 bg-transparent-primary border border-primary d-flex align-items-center justify-content-center"><i
											class="ti ti-user-check text-primary fs-18"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center flex-wrap justify-content-between">
								<div>
									<p class="fs-12 fw-medium mb-0 text-gray-5">Overtime Hours</p>
									<h4>118</h4>
								</div>
								<div>
									<span
										class="p-2 br-10 bg-pink-transparent border border-pink d-flex align-items-center justify-content-center"><i
											class="ti ti-user-edit text-pink fs-18"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center flex-wrap justify-content-between">
								<div>
									<p class="fs-12 fw-medium mb-0 text-gray-5">Pending Request</p>
									<h4>23</h4>
								</div>
								<div>
									<span
										class="p-2 br-10 bg-transparent-purple border border-purple d-flex align-items-center justify-content-center"><i
											class="ti ti-user-exclamation text-purple fs-18"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center flex-wrap justify-content-between">
								<div>
									<p class="fs-12 fw-medium mb-0 text-gray-5">Rejected</p>
									<h4>5</h4>
								</div>
								<div>
									<span
										class="p-2 br-10 bg-skyblue-transparent border border-skyblue d-flex align-items-center justify-content-center"><i
											class="ti ti-user-exclamation text-skyblue fs-18"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Overtime Counts -->

			<!-- Performance Indicator list -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Overtime</h5>
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
								Employee
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Anthony Lewis</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Brian
										Villalobos</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Harvey Smith</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Project
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Office
										Management</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Project
										Management</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Hospital
										Administration</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Accepted</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Rejected</a>
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
									<th>Employee</th>
									<th>Date </th>
									<th>Overtime Hours</th>
									<th>Project</th>
									<th>Approved By</th>
									<th>Status</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Anthony Lewis</a></h6>
												<span class="fs-12 fw-normal ">UI/UX Team</span>
											</div>
										</div>
									</td>
									<td>
										14 Jan 2024
									</td>
									<td>32</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Office
												Management </a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-39.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Michael Walker</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Brian Villalobos</a></h6>
												<span class="fs-12 fw-normal ">Development</span>
											</div>
										</div>
									</td>
									<td>
										21 Jan 2024
									</td>
									<td>45</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Project
												Management</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-02.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Sophie Headrick</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Harvey Smith</a></h6>
												<span class="fs-12 fw-normal ">HR</span>
											</div>
										</div>
									</td>
									<td>
										20 Feb 2024
									</td>
									<td>31</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Project
												Management</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-03.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Cameron Drake</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Stephan Peralt</a></h6>
												<span class="fs-12 fw-normal ">Management</span>
											</div>
										</div>
									</td>
									<td>
										15 Mar 2024
									</td>
									<td>45</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Hospital
												Administration</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-04.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doris Crowley</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Rejected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-34.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doglas Martini</a></h6>
												<span class="fs-12 fw-normal ">Development</span>
											</div>
										</div>
									</td>
									<td>
										12 Apr 2024
									</td>
									<td>36</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Office
												Management</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-06.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Thomas Bordelon</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Linda Ray</a></h6>
												<span class="fs-12 fw-normal ">UI/UX Team</span>
											</div>
										</div>
									</td>
									<td>
										20 Apr 2024
									</td>
									<td>49</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Hospital
												Administration</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-06.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Kathleen Gutierrez</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-35.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Elliot Murray</a></h6>
												<span class="fs-12 fw-normal ">Developer</span>
											</div>
										</div>
									</td>
									<td>
										06 Jul 2024
									</td>
									<td>57</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Video
												Calling App</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>

									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-07.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Bruce Wright</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-36.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Rebecca Smtih</a></h6>
												<span class="fs-12 fw-normal ">UI/UX Team</span>
											</div>
										</div>
									</td>
									<td>
										02 Sep 2024
									</td>
									<td>21</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Office
												Management</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-09.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Estelle Morgan</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Rejected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-37.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Connie Waters</a></h6>
												<span class="fs-12 fw-normal ">Management</span>
											</div>
										</div>
									</td>
									<td>
										15 Nov 2024
									</td>
									<td>32</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Project
												Management</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-10.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Stephen Dias</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Connie Waters</a></h6>
												<span class="fs-12 fw-normal ">Management</span>
											</div>
										</div>
									</td>
									<td>
										15 Nov 2024
									</td>
									<td>66</td>
									<td>
										<div class=" d-flex align-items-center">
											<a href="#"
												class="fs-14 fw-medium text-gray-9 d-flex align-items-center"
												data-bs-toggle="modal" data-bs-target="#overtime_details">Ware house
												developement</a>
											<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/reports/user-05.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Angela Thomas</a></h6>
											</div>
										</div>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Accepted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_overtime"><i class="ti ti-edit"></i></a>
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
			<!-- /Performance Indicator list -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection