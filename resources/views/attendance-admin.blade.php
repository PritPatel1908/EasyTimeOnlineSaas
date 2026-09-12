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
					<h2 class="mb-1">Attendance Admin</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Attendance
							</li>
							<li class="breadcrumb-item active" aria-current="page">Attendance Admin</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('attendance-employee')}}" class="btn btn-icon btn-sm  me-1"><i
									class="ti ti-brand-days-counter"></i></a>
							<a href="{{url('attendance-admin')}}"
								class="btn btn-icon btn-sm active bg-primary text-white"><i
									class="ti ti-calendar-event"></i></a>
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
						<a href="#" class="btn btn-primary d-flex align-items-center"
							data-bs-target="#attendance_report" data-bs-toggle="modal"><i
								class="ti ti-file-analytics me-2"></i>Report</a>
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

			<div class="card border-0">
				<div class="card-body">
					<div class="row align-items-center mb-4">
						<div class="col-md-5">
							<div class="mb-3 mb-md-0">
								<h4 class="mb-1">Attendance Details Today</h4>
								<p>Data from the 800+ total no of employees</p>
							</div>
						</div>
						<div class="col-md-7">
							<div class="d-flex align-items-center justify-content-md-end">
								<h6>Total Absenties today</h6>
								<div class="avatar-list-stacked avatar-group-sm ms-4">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="border rounded">
						<div class="row gx-0">
							<div class="col-md col-sm-4 border-end">
								<div class="p-3">
									<span class="fw-medium mb-1 d-block">Present</span>
									<div class="d-flex align-items-center justify-content-between">
										<h5>250</h5>
										<span class="badge badge-success d-inline-flex align-items-center">
											<i class="ti ti-arrow-wave-right-down me-1"></i>
											+1%
										</span>
									</div>
								</div>
							</div>
							<div class="col-md col-sm-4 border-end">
								<div class="p-3">
									<span class="fw-medium mb-1 d-block">Late Login</span>
									<div class="d-flex align-items-center justify-content-between">
										<h5>45</h5>
										<span class="badge badge-danger d-inline-flex align-items-center">
											<i class="ti ti-arrow-wave-right-down me-1"></i>
											-1%
										</span>
									</div>
								</div>
							</div>
							<div class="col-md col-sm-4 border-end">
								<div class="p-3">
									<span class="fw-medium mb-1 d-block">Uninformed</span>
									<div class="d-flex align-items-center justify-content-between">
										<h5>15</h5>
										<span class="badge badge-danger d-inline-flex align-items-center">
											<i class="ti ti-arrow-wave-right-down me-1"></i>
											-12%
										</span>
									</div>
								</div>
							</div>
							<div class="col-md col-sm-4 border-end">
								<div class="p-3">
									<span class="fw-medium mb-1 d-block">Permisson</span>
									<div class="d-flex align-items-center justify-content-between">
										<h5>03</h5>
										<span class="badge badge-success d-inline-flex align-items-center">
											<i class="ti ti-arrow-wave-right-down me-1"></i>
											+1%
										</span>
									</div>
								</div>
							</div>
							<div class="col-md col-sm-4">
								<div class="p-3">
									<span class="fw-medium mb-1 d-block">Absent</span>
									<div class="d-flex align-items-center justify-content-between">
										<h5>12</h5>
										<span class="badge badge-danger d-inline-flex align-items-center">
											<i class="ti ti-arrow-wave-right-down me-1"></i>
											-19%
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Admin Attendance</h5>
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
								Department
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Finance</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Application
										Development</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">IT Management</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Present</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Absent</a>
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
									<th>Employee</th>
									<th>Status</th>
									<th>Check In</th>
									<th>Check Out</th>
									<th>Break</th>
									<th>Late</th>
									<th>Production Hours</th>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-49.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Anthony Lewis</a></h6>
												<span class="fs-12 fw-normal ">UI/UX Team</span>
											</div>
										</div>
									</td>
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										06:45 PM
									</td>
									<td>30 Min</td>
									<td>
										32 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>8.55 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
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
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										06:12 PM
									</td>
									<td>20 Min</td>
									<td>
										20 Min
									</td>
									<td><span class="badge badge-danger d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>7.54 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
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
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										06:13 PM
									</td>
									<td>50 Min</td>
									<td>
										23 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>8.45 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
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
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										06:23 PM
									</td>
									<td>41 Min</td>
									<td>
										50 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>8.35 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
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
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										06:43 PM
									</td>
									<td>23 Min</td>
									<td>
										10 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>8.22 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
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
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										07:15 PM
									</td>
									<td>03 Min</td>
									<td>
										30 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>8.32 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-35.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Elliot Murray</a></h6>
												<span class="fs-12 fw-normal ">UI/UX Team</span>
											</div>
										</div>
									</td>
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										07:13 PM
									</td>
									<td>32 Min</td>
									<td>
										41 Min
									</td>
									<td><span class="badge badge-info d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>9.15 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-30.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Rebecca Smtih</a></h6>
												<span class="fs-12 fw-normal ">UI/UX Team</span>
											</div>
										</div>
									</td>
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										09:17 PM
									</td>
									<td>14 Min</td>
									<td>
										12 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>9.25 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-36.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Connie Waters</a></h6>
												<span class="fs-12 fw-normal ">Management</span>
											</div>
										</div>
									</td>
									<td><span
											class="badge badge-success-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Present</span></td>
									<td>09:00 AM</td>
									<td>
										08:15 PM
									</td>
									<td>12 Min</td>
									<td>
										03 Min
									</td>
									<td><span class="badge badge-success d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>8.35 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Lori Broaddus</a></h6>
												<span class="fs-12 fw-normal ">Finance</span>
											</div>
										</div>
									</td>
									<td><span
											class="badge badge-danger-transparent d-inline-flex align-items-center"><i
												class="ti ti-point-filled me-1"></i>Absent</span></td>
									<td>-</td>
									<td>
										-
									</td>
									<td>-</td>
									<td>
										-
									</td>
									<td><span class="badge badge-danger d-inline-flex align-items-center"><i
												class="ti ti-clock-hour-11 me-1"></i>0.00 Hrs</span></td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_attendance"><i class="ti ti-edit"></i></a>
										</div>
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