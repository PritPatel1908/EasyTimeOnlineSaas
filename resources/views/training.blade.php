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
					<h2 class="mb-1">Training</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Training
							</li>
							<li class="breadcrumb-item active" aria-current="page">Add Training</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_training"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Training </a>
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
					<h5>Training List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

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
									<th>Training Type</th>
									<th>Trainer</th>
									<th>Employee</th>
									<th>Time Duration</th>
									<th>Description</th>
									<th>Cost</th>
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
										Git Training
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Anthony Lewis</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+4
											</span>
										</div>
									</td>
									<td>12 Jan 2024 - 12 Feb 2024</td>
									<td>Version control and code collaboration.</td>
									<td>$200</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_training"><i class="ti ti-edit"></i></a>
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
										HTML Training
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Brian Villalobos</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-30.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-28.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+3
											</span>
										</div>
									</td>
									<td>17 Jan 2024 - 17 Feb 2024</td>
									<td>Basics of web page structure and markup.</td>
									<td>$100</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_training"><i class="ti ti-edit"></i></a>
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
										React Training
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Harvey Smith</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-21.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-18.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+6
											</span>
										</div>
									</td>
									<td>10 Feb 2024 - 10 Mar 2024</td>
									<td>Dynamic web applications with components</td>
									<td>$300</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_training"><i class="ti ti-edit"></i></a>
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
										Nodejs Training
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Stephan Peralt</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-28.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-22.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+5
											</span>
										</div>
									</td>
									<td>20 Feb 2024 - 20 Mar 2024</td>
									<td>Building scalable server-side applications</td>
									<td>$250</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_training"><i class="ti ti-edit"></i></a>
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
										Vuejs Training
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-34.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doglas Martini</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="avatar-list-stacked avatar-group-sm">
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-23.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-13.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-12.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-25.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span class="avatar border-0">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="rounded-circle"
													alt="img">
											</span>
											<span
												class="avatar group-counts bg-primary rounded-circle border-0 fs-10">
												+7
											</span>
										</div>
									</td>
									<td>16 Mar 2024 - 16 Apr 2024</td>
									<td>Interactive single-page applications</td>
									<td>$280</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_training"><i class="ti ti-edit"></i></a>
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