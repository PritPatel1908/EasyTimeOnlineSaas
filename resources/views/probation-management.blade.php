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
					<h2 class="mb-1">Probation Management</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								HRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Probation Management</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2 me-2">
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_modal"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add New Employee</a>
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

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Probation Management</h5>
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
								Designation
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
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Web Developer</a>
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
									<th>Emp ID</th>
									<th>Name</th>
									<th>Designation</th>
									<th>Joining Date</th>
									<th>Probation End Date</th>
									<th>Reviewer</th>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-001</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Anthony Lewis</a>
											</div>
										</div>
									</td>
									<td>Accountant</td>
									<td>
										14 Jun 2025
									</td>
									<td>12 Sep 2025</td>
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
										<span
											class="badge badge-soft-info d-inline-flex align-items-center badge-xs">
											Pending
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-002</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-13.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Brian Villalobos</a>
											</div>
										</div>
									</td>
									<td>App Developer</td>
									<td>
										25 May 2025
									</td>
									<td>24 Jul 2025</td>
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
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Completed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#"><i class="ti ti-refresh"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-003</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-12.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Harvey Smith</a>
											</div>
										</div>
									</td>
									<td>Technician</td>
									<td>
										10 May 2025
									</td>
									<td>08 Aug 2025</td>
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
										<span
											class="badge badge-soft-warning d-inline-flex align-items-center badge-xs">
											In Review
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-0" data-bs-toggle="modal"
												data-bs-target="#edit_modal"><i class="ti ti-edit"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-004</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Stephan Peralt</a>
											</div>
										</div>
									</td>
									<td>Web Developer</td>
									<td>
										28 Apr 2025
									</td>
									<td>27 Jul 2025</td>
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
										<span
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											Failed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#"><i class="ti ti-ban"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-005</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-15.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Doglas Martini</a>
											</div>
										</div>
									</td>
									<td>Sales Executive Officer</td>
									<td>
										15 Apr 2025
									</td>
									<td>14 Jun 2025</td>
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
										<span
											class="badge badge-soft-purple d-inline-flex align-items-center badge-xs">
											Extended
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-006</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-14.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Linda Ray</a>
											</div>
										</div>
									</td>
									<td>Designer</td>
									<td>
										20 Mar 2025
									</td>
									<td>18 Jun 2025</td>
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
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Completed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#"><i class="ti ti-refresh"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-007</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Elliot Murray</a>
											</div>
										</div>
									</td>
									<td>Account Manager</td>
									<td>
										10 Mar 2025
									</td>
									<td>08 Jun 2025</td>
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
										<span
											class="badge badge-soft-warning d-inline-flex align-items-center badge-xs">
											In Review
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-0" data-bs-toggle="modal"
												data-bs-target="#edit_modal"><i class="ti ti-edit"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-008</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-18.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Rebecca Smtih</a>
											</div>
										</div>
									</td>
									<td>SEO Analyst</td>
									<td>
										17 Feb 2025
									</td>
									<td>18 Apr 2025</td>
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
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Completed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-009</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Connie Waters</a>
											</div>
										</div>
									</td>
									<td>Admin</td>
									<td>
										02 Feb 2025
									</td>
									<td>03 Apr 2025</td>
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
										<span
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											Failed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
											<a href="#"><i class="ti ti-ban"></i></a>
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
										<a href="#" data-bs-toggle="offcanvas"
											data-bs-target="#probation_details">Emp-010</a>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Lori Broaddus</a>
											</div>
										</div>
									</td>
									<td>Business Analyst</td>
									<td>
										24 Jan 2025
									</td>
									<td>24 Apr 2025</td>
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
										<span
											class="badge badge-soft-info d-inline-flex align-items-center badge-xs">
											Pending
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="offcanvas"
												data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
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