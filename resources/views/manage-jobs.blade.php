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
					<h2 class="mb-1">Manage Jobs</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Administration
							</li>
							<li class="breadcrumb-item active" aria-current="page">Manage Jobs</li>
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
						<a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
							data-bs-target="#add_job"><i class="ti ti-circle-plus me-2"></i>Add Job</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->


			<!-- Job List -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Job List</h5>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Web
										Development</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">UI / UX</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Job Type
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Full Time</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Part Time</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Internship</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Other</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Open</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Closed</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Canceled</a>
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
									<th>Job Title</th>
									<th>Department</th>
									<th>Start Date</th>
									<th>Expire Date</th>
									<th>Job Type</th>
									<th>Status</th>
									<th>Applicants</th>
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
										<h6 class="fw-medium">Accountant</h6>
									</td>
									<td>Finance</td>
									<td>14 Jan 2024</td>
									<td>20 Feb 2024</td>
									<td>
										<select class="select">
											<option>Full Time</option>
											<option>Part Time</option>
											<option>Internship</option>
										</select>
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="border border-purple rounded-circle bg-transparent-purple d-flex justify-content-center align-items-center me-1"><i
														class="ti ti-point-filled text-purple"></i></span>Open
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Open</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Closed</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Cancelled</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-primary badge-md">3 Candidates</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_job"><i class="ti ti-edit"></i></a>
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
									<td>
										<h6 class="fw-medium"><a href="#">App Developer</a></h6>
									</td>
									<td>Application Development</td>
									<td>21 Feb 2024</td>
									<td>11 Mar 2024</td>
									<td>
										<select class="select">
											<option>Full Time</option>
											<option>Part Time</option>
											<option>Internship</option>
										</select>
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="border border-success rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-1"><i
														class="ti ti-point-filled text-success"></i></span>Closed
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Open</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Closed</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Cancelled</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-primary badge-md">3 Candidates</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_job"><i class="ti ti-edit"></i></a>
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
									<td>
										<h6 class="fw-medium"><a href="#">Technician</a></h6>
									</td>
									<td>IT Management</td>
									<td>18 Mar 2024</td>
									<td>05 Apr 2024</td>
									<td>
										<select class="select">
											<option>Full Time</option>
											<option>Part Time</option>
											<option selected>Internship</option>
										</select>
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="border border-danger rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-1"><i
														class="ti ti-point-filled text-danger"></i></span>Cancelled
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Open</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Closed</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Cancelled</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-primary badge-md">3 Candidates</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_job"><i class="ti ti-edit"></i></a>
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
									<td>
										<h6 class="fw-medium"><a href="#">Web Developer</a></h6>
									</td>
									<td>UI / UX</td>
									<td>10 Apr 2024</td>
									<td>10 Apr 2024</td>
									<td>
										<select class="select">
											<option>Full Time</option>
											<option selected>Part Time</option>
											<option>Internship</option>
										</select>
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="border border-danger rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-1"><i
														class="ti ti-point-filled text-danger"></i></span>Cancelled
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Open</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Closed</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Cancelled</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-primary badge-md">3 Candidates</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_job"><i class="ti ti-edit"></i></a>
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
									<td>
										<h6 class="fw-medium"><a href="#">Designer</a></h6>
									</td>
									<td>UI / UX</td>
									<td>14 Mar 2024</td>
									<td>14 Mar 2024</td>
									<td>
										<select class="select">
											<option>Full Time</option>
											<option selected>Part Time</option>
											<option>Internship</option>
										</select>
									</td>
									<td>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="border border-danger rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-1"><i
														class="ti ti-point-filled text-danger"></i></span>Cancelled
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Open</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Closed</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Cancelled</a>
												</li>
											</ul>
										</div>
									</td>
									<td>
										<span class="badge badge-primary badge-md">3 Candidates</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_job"><i class="ti ti-edit"></i></a>
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
			<!-- /Pricing Table -->
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection