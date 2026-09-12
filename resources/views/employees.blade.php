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
					<h2 class="mb-1">Employees List</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Employees
							</li>
							<li class="breadcrumb-item active" aria-current="page">Employees List</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('employees')}}"
								class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('employees-grid')}}" class="btn btn-icon btn-sm"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_employee"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Employee</a>
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

				<!-- Total Plans -->
				<div class="col-lg-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center overflow-hidden">
								<div>
									<span class="avatar avatar-lg bg-dark rounded-circle"><i
											class="ti ti-users"></i></span>
								</div>
								<div class="ms-2 overflow-hidden">
									<p class="fs-12 fw-medium mb-1 text-truncate">Total Employee</p>
									<h4>1007</h4>
								</div>
							</div>
							<div>
								<span class="badge badge-soft-purple badge-sm fw-normal">
									<i class="ti ti-arrow-wave-right-down"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<!-- /Total Plans -->

				<!-- Total Plans -->
				<div class="col-lg-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center overflow-hidden">
								<div>
									<span class="avatar avatar-lg bg-success rounded-circle"><i
											class="ti ti-user-share"></i></span>
								</div>
								<div class="ms-2 overflow-hidden">
									<p class="fs-12 fw-medium mb-1 text-truncate">Active</p>
									<h4>1007</h4>
								</div>
							</div>
							<div>
								<span class="badge badge-soft-primary badge-sm fw-normal">
									<i class="ti ti-arrow-wave-right-down"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<!-- /Total Plans -->

				<!-- Inactive Plans -->
				<div class="col-lg-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center overflow-hidden">
								<div>
									<span class="avatar avatar-lg bg-danger rounded-circle"><i
											class="ti ti-user-pause"></i></span>
								</div>
								<div class="ms-2 overflow-hidden">
									<p class="fs-12 fw-medium mb-1 text-truncate">InActive</p>
									<h4>1007</h4>
								</div>
							</div>
							<div>
								<span class="badge badge-soft-dark badge-sm fw-normal">
									<i class="ti ti-arrow-wave-right-down"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<!-- /Inactive Companies -->

				<!-- No of Plans  -->
				<div class="col-lg-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center overflow-hidden">
								<div>
									<span class="avatar avatar-lg bg-info rounded-circle"><i
											class="ti ti-user-plus"></i></span>
								</div>
								<div class="ms-2 overflow-hidden">
									<p class="fs-12 fw-medium mb-1 text-truncate">New Joiners</p>
									<h4>67</h4>
								</div>
							</div>
							<div>
								<span class="badge badge-soft-secondary badge-sm fw-normal">
									<i class="ti ti-arrow-wave-right-down"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<!-- /No of Plans -->

			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Plan List</h5>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Finance</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Developer</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Executive</a>
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
									<th>Emp ID</th>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Designation</th>
									<th>Joining Date</th>
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
									<td><a href="{{url('employee-details')}}">Emp-001</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-32.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Anthony
														Lewis</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>anthony@example.com</td>
									<td>(123) 4567 890</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Finance
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>12 Sep 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-002</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-09.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Brian
														Villalobos</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>brian@example.com</td>
									<td>(179) 7382 829</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Developer
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>24 Oct 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-003</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-01.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Harvey
														Smith</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>harvey@example.com</td>
									<td>(184) 2719 738</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Developer
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>18 Feb 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-004</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Stephan
														Peralt</a></p>
												<span class="fs-12">Executive Officer</span>
											</div>
										</div>
									</td>
									<td>peral@example.com</td>
									<td>(193) 7839 748</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Executive
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>17 Oct 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-005</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Doglas
														Martini</a></p>
												<span class="fs-12">Manager</span>
											</div>
										</div>
									</td>
									<td>martniwr@example.com</td>
									<td>(183) 9302 890</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Manager
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>20 Jul 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-006</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-02.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Linda
														Ray</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>ray456@example.com</td>
									<td>(120) 3728 039</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Finance
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>10 Apr 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-007</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-35.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Elliot
														Murray</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>murray@example.com</td>
									<td>(102) 8480 832</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Developer
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>29 Aug 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-008</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-36.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Rebecca
														Smtih</a></p>
												<span class="fs-12">Executive</span>
											</div>
										</div>
									</td>
									<td>smtih@example.com</td>
									<td>(162) 8920 713</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Executive
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>22 Feb 2024</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-sm">
											<i class="ti ti-point-filled me-1"></i>Inactive
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-009</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-37.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Connie
														Waters</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>connie@example.com</td>
									<td>(189) 0920 723</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Developer
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>03 Nov 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('employee-details')}}">Emp-010</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="{{url('employee-details')}}" class="avatar avatar-md"
												data-bs-toggle="modal" data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-38.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="{{url('employee-details')}}">Lori
														Broaddus</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>broaddus@example.com</td>
									<td>(168) 8392 823</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Finance
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Developer</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Executive</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Finanace</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Manager</a>
												</li>
											</ul>
										</div>
									</td>
									<td>17 Dec 2024</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_employee"><i class="ti ti-edit"></i></a>
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

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection