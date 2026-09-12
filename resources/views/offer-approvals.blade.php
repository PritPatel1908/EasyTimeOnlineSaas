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
					<h2 class="mb-1">Offer Approvals</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Administration
							</li>
							<li class="breadcrumb-item active" aria-current="page">Offer Approvals</li>
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
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Offer Approvals List</h5>
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
								Job Title
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Accountant</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">App Developer</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Technician</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Part time</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Internship</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Requested</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Approved</a>
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
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Name</th>
									<th>Job Title</th>
									<th>Job Type</th>
									<th>Pay</th>
									<th>Annual IP</th>
									<th>Long Term IP</th>
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
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-32.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Anthony Lewis</a></p>
												<span class="fs-12">Accountant</span>
											</div>
										</div>
									</td>
									<td>Accountant</td>
									<td>Full Time</td>
									<td>$20000</td>
									<td>12%</td>
									<td>No</td>
									<td>
										<span
											class="badge badge-soft-warning d-inline-flex align-items-center badge-xs">
											Requested
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-check"></i></a>
											<a href="#" class=""><i class="ti ti-x"></i></a>
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
									<td>App Developer</td>
									<td>Part time</td>
									<td>$25000</td>
									<td>15%</td>
									<td>No</td>
									<td>
										<span
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											Rejected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-check"></i></a>
											<a href="#" class=""><i class="ti ti-x"></i></a>
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
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-01.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Harvey Smith</a></p>
												<span class="fs-12">Technician</span>
											</div>
										</div>
									</td>
									<td>Technician</td>
									<td>Internship</td>
									<td>$15000</td>
									<td>10%</td>
									<td>No</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Approved
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-check"></i></a>
											<a href="#" class=""><i class="ti ti-x"></i></a>
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
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Stephan Peralt</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>Web Developer</td>
									<td>Web Development</td>
									<td>$25000</td>
									<td>15%</td>
									<td>No</td>
									<td>
										<span
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											Rejected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-check"></i></a>
											<a href="#" class=""><i class="ti ti-x"></i></a>
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
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-34.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Doglas Martini</a></p>
												<span class="fs-12">Designer</span>
											</div>
										</div>
									</td>
									<td>Designer</td>
									<td>Others</td>
									<td>$22000</td>
									<td>13%</td>
									<td>No</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Approved
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-check"></i></a>
											<a href="#" class=""><i class="ti ti-x"></i></a>
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