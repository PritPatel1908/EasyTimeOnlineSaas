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
					<h2 class="mb-1">Daily Report</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Reports
							</li>
							<li class="breadcrumb-item active" aria-current="page">Daily Report</li>
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
				<div class="col-xl-6 d-flex">
					<div class="row flex-fill">
						<!-- Total Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Present</p>
											<h4>300</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-primary bg-transparent-primary rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-user-check text-primary"></i></span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /Total Companies -->

						<!-- Total Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Completed Tasks</p>
											<h4>100</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-success bg-transparent-success rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-subtask text-success"></i></span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /Total Companies -->

						<!-- Inactive Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Absent</p>
											<h4>15</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-danger bg-transparent-danger rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-user-x text-danger"></i></span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /Inactive Companies -->

						<!-- Company Location -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Pending Tasks</p>
											<h4>125</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-skyblue bg-transparent-skyblue rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-user-x text-skyblue"></i></span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /Company Location -->
					</div>
				</div>
				<div class="col-xl-6 d-flex">
					<div class="card flex-fill">
						<div class="card-header border-0 pb-0">
							<div class="d-flex flex-wrap justify-content-between align-items-center">
								<div class="d-flex align-items-center ">
									<span class="me-2"><i class="ti ti-chart-bar text-danger"></i></span>
									<h5>Daily Attendance</h5>
								</div>
								<div class="d-flex align-items-center">
									<p class="d-inline-flex align-items-center me-2 mb-0">
										<i class="ti ti-square-filled fs-12 text-success me-2"></i>
										Present
									</p>
									<p class="d-inline-flex align-items-center mb-0 me-2">
										<i class="ti ti-square-filled fs-12 text-danger me-2"></i>
										Absent
									</p>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-sm fs-12 btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										This Year
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2023</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2022</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body py-0">
							<div id="daily-report"> </div>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Daily Attendance List</h5>
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
									<th>Name</th>
									<th>Date</th>
									<th>Department</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-32.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Anthony Lewis</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Finance</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-09.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Brian Villalobos</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Application Development</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-01.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Harvey Smith</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>IT Management</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Stephan Peralt</a></p>
												<span class="fs-12">Executive Officer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Web Development</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Doglas Martini</a></p>
												<span class="fs-12">Manager</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Sales</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-02.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Linda Ray</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>UI / UX</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-35.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Elliot Murray</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Account Management</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-36.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Rebecca Smtih</a></p>
												<span class="fs-12">Executive</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Marketing</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-37.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Connie Waters</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Administration</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-38.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Lori Broaddus</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>Business Development</td>
									<td>
										<span
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Absent
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