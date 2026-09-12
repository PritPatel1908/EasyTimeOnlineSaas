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
					<h2 class="mb-1">Todo</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Applications
							</li>
							<li class="breadcrumb-item active" aria-current="page">Todo</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="d-flex align-items-center border rounded p-1 me-2">
						<a href="{{url('todo-list')}}" class="btn btn-icon btn-sm active bg-primary text-white"><i
								class="ti ti-list-tree"></i></a>
						<a href="{{url('todo')}}" class="btn btn-icon btn-sm"><i class="ti ti-table"></i></a>
					</div>
					<div class="">
						<a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
							data-bs-target="#add_todo"><i class="ti ti-circle-plus me-2"></i>Create New</a>
					</div>
					<div class="ms-2 mb-0 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5 class="d-flex align-items-center">Todo Lists <span class="badge bg-soft-pink ms-2">200
							Employees</span></h5>
					<div class="d-flex align-items-center flex-wrap row-gap-3">
						<div class="input-icon-start me-2 position-relative">
							<span class="icon-addon">
								<i class="ti ti-calendar"></i>
							</span>
							<input type="text" class="form-control date-range bookingrange"
								placeholder="dd/mm/yyyy - dd/mm/yyyy">
						</div>
						<div class="input-icon position-relative w-120 me-2">
							<span class="input-icon-addon">
								<i class="ti ti-calendar"></i>
							</span>
							<input type="text" class="form-control datetimepicker" placeholder="Due Date">
						</div>
						<div class="dropdown me-2">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Tags
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">All Tags</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Urgent</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">High</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Medium</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-2">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Assignee
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Sophie</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Cameron</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Doris</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Rufana</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-2">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Completed</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Inprogress</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Onhold</a>
								</li>
							</ul>
						</div>
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center fs-12"
								data-bs-toggle="dropdown">
								<span class="fs-12 d-inline-flex me-1">Sort By : </span>
								Last 7 Days
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 1 month</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 1 year</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card-body p-0">

					<!-- Student List -->
					<div class="custom-datatable-filter table-responsive">
						<table class="table datatable">
							<thead class="thead-light">
								<tr>
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Company Name</th>
									<th>Tags</th>
									<th>Assignee</th>
									<th>Created On</th>
									<th>Progress</th>
									<th>Due Date</th>
									<th>Status</th>
									<th class="no-sort">Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-danger me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Respond to any pending messages</p>
									</td>
									<td>
										<span class="badge badge-info">Social</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 100%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-success rounded" role="progressbar"
												style="width: 100%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>
										<span class="badge badge-soft-success d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Completed
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star-filled filled"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-purple me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Update calendar and schedule</p>
									</td>
									<td>
										<span class="badge badge-purple">Meetings</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>21 Jan 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 15%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-danger rounded" role="progressbar"
												style="width: 15%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>21 Jan 2024</td>
									<td>
										<span class="badge badge-soft-dark d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Pending
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-purple me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Respond to any pending messages</p>
									</td>
									<td>
										<span class="badge bg-pink">Research</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>20 Feb 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 45%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-warning rounded" role="progressbar"
												style="width: 45%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>20 Feb 2024</td>
									<td>
										<span class="badge bg-transparent-purple d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Inprogress
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-warning me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Attend team meeting at 10:00 AM</p>
									</td>
									<td>
										<span class="badge bg-skyblue">Web Design</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>15 Mar 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 40%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-warning rounded" role="progressbar"
												style="width: 40%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>15 Mar 2024</td>
									<td>
										<span class="badge bg-transparent-purple d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Inprogress
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-purple me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Check and respond to emails</p>
									</td>
									<td>
										<span class="badge badge-secondary">Reminder</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>12 Apr 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 65%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-purple rounded" role="progressbar"
												style="width: 65%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>12 Apr 2024</td>
									<td>
										<span class="badge badge-soft-dark d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Pending
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-warning me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Coordinate with department head</p>
									</td>
									<td>
										<span class="badge badge-danger">Internal</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-11.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>20 Apr 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 85%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-pink rounded" role="progressbar"
												style="width: 85%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>20 Apr 2024</td>
									<td>
										<span class="badge bg-soft-pink d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Onhold
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-success me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Plan tasks for the next day</p>
									</td>
									<td>
										<span class="badge badge-info">Social</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>06 Jul 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 100%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-success rounded" role="progressbar"
												style="width: 100%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>06 Jul 2024</td>
									<td>
										<span class="badge badge-soft-success d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Completed
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-success me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Finalize project proposal</p>
									</td>
									<td>
										<span class="badge badge-success">Projects</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>02 Sep 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 65%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-danger rounded" role="progressbar"
												style="width: 65%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>02 Sep 2024</td>
									<td>
										<span class="badge bg-soft-pink d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Onhold
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-purple me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Submit to supervisor by EOD</p>
									</td>
									<td>
										<span class="badge badge-secondary">Reminder</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-21.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>15 Nov 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 75%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-purple rounded" role="progressbar"
												style="width: 75%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>15 Nov 2024</td>
									<td>
										<span class="badge bg-transparent-purple d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Inprogress
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-md">
												<input class="form-check-input" type="checkbox">
											</div>
											<span class="mx-2 d-flex align-items-center rating-select"><i
													class="ti ti-star"></i></span>
											<span class="d-flex align-items-center"><i
													class="ti ti-square-rounded text-success me-2"></i></span>
										</div>
									</td>
									<td>
										<p class="fw-medium text-dark">Prepare presentation slides</p>
									</td>
									<td>
										<span class="badge bg-pink">Research</span>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}" alt="img">
											</span>
											<span class="avatar avatar-rounded">
												<img class="border border-white"
													src="{{URL::asset('build/img/profiles/avatar-25.jpg')}}" alt="img">
											</span>
										</div>
									</td>
									<td>10 Dec 2024</td>
									<td>
										<span class="d-block mb-1">Progress : 90%</span>
										<div class="progress progress-xs flex-grow-1 mb-2" style="width: 190px;">
											<div class="progress-bar bg-pink rounded" role="progressbar"
												style="width: 90%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</td>
									<td>10 Dec 2024</td>
									<td>
										<span class="badge badge-soft-dark d-inline-flex align-items-center">
											<i class="ti ti-circle-filled fs-5 me-1"></i>
											Pending
										</span>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#edit_todo">
												<i class="ti ti-edit"></i>
											</a>
											<a href="#" class="btn btn-sm btn-icon" data-bs-toggle="modal"
												data-bs-target="#delete_modal">
												<i class="ti ti-trash"></i>
											</a>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<!-- /Student List -->
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