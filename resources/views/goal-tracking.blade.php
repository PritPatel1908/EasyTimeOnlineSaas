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
					<h2 class="mb-1">Goal Tracking</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Performance
							</li>
							<li class="breadcrumb-item active" aria-current="page">Goal Tracking</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">

					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_goal"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Goal </a>
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
					<h5>Goal Tracking List</h5>
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
									<th>Goal Type</th>
									<th>Subject</th>
									<th>Target Achievement</th>
									<th>Start Date</th>
									<th>End Date</th>
									<th>Description</th>
									<th>Status</th>
									<th>Progress</th>
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
										Development Goals
									</td>
									<td>Programming Skills</td>
									<td>
										Complete a HTML course
									</td>
									<td>
										14 Jan 2024
									</td>
									<td>
										13 Mar 2024
									</td>
									<td>
										Improve proficiency
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<span class="fs-12 mb-1">Completed 70%</span>
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="width: 87px;height: 5px;">
											<div class="progress-bar bg-primary" style="width: 80%"></div>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_goal"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
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
										Project Goals
									</td>
									<td>App Development</td>
									<td>
										Deliver the app
									</td>
									<td>
										21 Jan 2024
									</td>
									<td>
										21 Feb 2024
									</td>
									<td>
										Complete the app
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<span class="fs-12 mb-1">Completed 40%</span>
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="width: 87px;height: 5px;">
											<div class="progress-bar bg-primary" style="width: 40%"></div>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_goal"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
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
										Project Goals
									</td>
									<td>Web Development</td>
									<td>
										Deliver the template
									</td>
									<td>
										18 Feb 2024
									</td>
									<td>
										18 Mar 2024
									</td>
									<td>
										Complete the template
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<span class="fs-12 mb-1">Completed 60%</span>
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="width: 87px;height: 5px;">
											<div class="progress-bar bg-primary" style="width: 60%"></div>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_goal"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
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