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
					<h2 class="mb-1">Timesheets</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Attendance
							</li>
							<li class="breadcrumb-item active" aria-current="page">Timesheets</li>
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_timesheet"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Today’s Work</a>
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

			<!-- Performance Indicator list -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Timesheet</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Project
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
									<th>Project</th>
									<th>Assigned Hours</th>
									<th>Worked Hours</th>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center">Office
											Management <a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>32</td>
									<td>
										13
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center">Project
											Management <a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>45</td>
									<td>
										14
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center">Project
											Management <a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>45</td>
									<td>
										22
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center ">Hospital
											Administration<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>45</td>
									<td>
										78
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center">Office
											Management <a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>36</td>
									<td>
										45
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center ">Hospital
											Administration <a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>49</td>
									<td>
										14
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center">Video
											Calling App<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>57</td>
									<td>
										16
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center ">Office
											Management <a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>21</td>
									<td>
										18
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center">Project
											Management<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>32</td>
									<td>
										19
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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
									<td>
										<p class="fs-14 fw-medium text-gray-9 d-flex align-items-center ">Project
											Management<a href="#" class="ms-1" data-bs-toggle="tooltip"
												data-bs-placement="right" data-bs-title="Worked on the Management
											design & Development"><i class="ti ti-info-circle text-info"></i></a></p>
									</td>
									<td>32</td>
									<td>
										19
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_timesheet"><i class="ti ti-edit"></i></a>
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