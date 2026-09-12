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
					<h2 class="mb-1">Campus Hiring</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Recruitment
							</li>
							<li class="breadcrumb-item active" aria-current="page">Campus Hiring</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2 me-2">
						<div class="dropdown">
							<a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-file-export me-1"></i>Export
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="#" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
								</li>
							</ul>
						</div>

					</div>
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_candidate"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add New Candidate</a>
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
					<h5>Students List</h5>
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
								Role
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
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Sales Executive
										Officer</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Applied</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">In progress</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Selected</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Rejected</a>
								</li>
							</ul>
						</div>
						<div class="dropdown">
							<a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Sort By : Last 7 Days
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="#" class="dropdown-item rounded-1">Recently Added</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Ascending</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Descending</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Last Month</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Last 7 Days</a>
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
									<th>Student Name</th>
									<th>Branch</th>
									<th>Graduation Year</th>
									<th>Job Role</th>
									<th>Recruiter Name</th>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Harold Gaynor</a>
												<span class="d-block">harold@example.com</span>
											</div>
										</div>
									</td>
									<td>B.E/CSE</td>
									<td>2025</td>
									<td>Accountant</td>
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
									<td>
										<span
											class="badge border border-purple text-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Applied
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Sandra Ornellas</a>
												<span class="d-block">sandra@example.com</span>
											</div>
										</div>
									</td>
									<td>B.Tech/IT</td>
									<td>2025</td>
									<td>App Developer</td>
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
									<td>
										<span
											class="badge border border-pink text-pink d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Shortlisted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">John Harris</a>
												<span class="d-block">john@example.com</span>
											</div>
										</div>
									</td>
									<td>B.E/CSE</td>
									<td>2025</td>
									<td>Technician</td>
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
									<td>
										<span
											class="badge border border-info text-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>In progress
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Whitney Barnette</a>
												<span class="d-block">whitney@example.com</span>
											</div>
										</div>
									</td>
									<td>B.E/CSE</td>
									<td>2025</td>
									<td>Web Developer</td>
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
									<td>
										<span
											class="badge border border-purple text-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Applied
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Richard Thompson</a>
												<span class="d-block">richard@example.com</span>
											</div>
										</div>
									</td>
									<td>B.E/CSE</td>
									<td>2024</td>
									<td>Sales Executive Officer</td>
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
									<td>
										<span
											class="badge border border-success text-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Selected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-06.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Kerry Drake</a>
												<span class="d-block">kerry@example.com</span>
											</div>
										</div>
									</td>
									<td>B.Tech/IT</td>
									<td>2025</td>
									<td>Designer</td>
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
									<td>
										<span
											class="badge border border-danger text-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Rejected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">David Carmona</a>
												<span class="d-block">david@example.com</span>
											</div>
										</div>
									</td>
									<td>B.E/CSE</td>
									<td>2025</td>
									<td>Account Manager</td>
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
									<td>
										<span
											class="badge border border-info text-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>In progress
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Margaret Soto</a>
												<span class="d-block">margaret@example.com</span>
											</div>
										</div>
									</td>
									<td>B.Tech/IT</td>
									<td>2024</td>
									<td>SEO Analyst</td>
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
									<td>
										<span
											class="badge border border-pink text-pink d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Shortlisted
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Jeffrey Thaler</a>
												<span class="d-block">jeffrey@example.com</span>
											</div>
										</div>
									</td>
									<td>B.E/CSE</td>
									<td>2025</td>
									<td>Admin</td>
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
									<td>
										<span
											class="badge border border-success text-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Selected
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Joyce Golston</a>
												<span class="d-block">joyce@example.com</span>
											</div>
										</div>
									</td>
									<td>B.Tech/IT</td>
									<td>2025</td>
									<td>Business Analyst</td>
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
									<td>
										<span
											class="badge border border-info text-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>In progress
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_candidate"><i class="ti ti-edit"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
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