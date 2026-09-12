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
					<h2 class="mb-1">Clients</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Projects
							</li>
							<li class="breadcrumb-item active" aria-current="page">Client List</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('clients')}}" class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('clients-grid')}}" class="btn btn-icon btn-sm"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_client"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Client</a>
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

			<!-- Clients Info -->
			<div class="row">
				<div class="col-xl-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<div class="flex-shrink-0 me-2">
										<span
											class="p-2 br-10 bg-pink-transparent border border-pink d-flex align-items-center justify-content-center">
											<i class="ti ti-users-group text-pink fs-18"></i>
										</span>
									</div>
									<div>
										<p class="fs-12 fw-medium mb-0 text-gray-5 mb-1">Total Clients</p>
										<h4>300</h4>
									</div>
								</div>
								<span
									class="badge bg-transparent-purple d-inline-flex align-items-center fw-normal">
									<i class="ti ti-arrow-wave-right-down me-1"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<div class="flex-shrink-0 me-2">
										<span
											class="p-2 br-10 bg-success-transparent border border-success d-flex align-items-center justify-content-center">
											<i class="ti ti-user-share fs-18"></i>
										</span>
									</div>
									<div>
										<p class="fs-12 fw-medium mb-0 text-gray-5 mb-1">Active Clients</p>
										<h4>270</h4>
									</div>
								</div>
								<span
									class="badge bg-transparent-primary text-primary d-inline-flex align-items-center fw-normal">
									<i class="ti ti-arrow-wave-right-down me-1"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<div class="flex-shrink-0 me-2">
										<span
											class="p-2 br-10 bg-danger-transparent border border-danger d-flex align-items-center justify-content-center">
											<i class="ti ti-user-pause fs-18"></i>
										</span>
									</div>
									<div>
										<p class="fs-12 fw-medium mb-0 text-gray-5 mb-1">Inactive Clients</p>
										<h4>30</h4>
									</div>
								</div>
								<span
									class="badge bg-transparent-dark text-dark d-inline-flex align-items-center fw-normal">
									<i class="ti ti-arrow-wave-right-down me-1"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<div class="flex-shrink-0 me-2">
										<span
											class="p-2 br-10 bg-info-transparent border border-info d-flex align-items-center justify-content-center">
											<i class="ti ti-user-plus fs-18"></i>
										</span>
									</div>
									<div>
										<p class="fs-12 fw-medium mb-0 text-gray-5 mb-1">New Clients</p>
										<h4>300</h4>
									</div>
								</div>
								<span
									class="badge bg-transparent-secondary text-dark d-inline-flex align-items-center fw-normal">
									<i class="ti ti-arrow-wave-right-down me-1"></i>
									+19.01%
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Clients Info -->

			<!-- Clients list -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Client List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
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
								class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
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
									<th>Client ID</th>
									<th>Client Name</th>
									<th>Company Name</th>
									<th>Email</th>
									<th>Phone</th>
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
									<td><a href="{{url('client-details')}}">Cli-001</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-39.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Michael
														Walker</a></h6>
												<span class="fs-12 fw-normal ">CEO</span>
											</div>
										</div>
									</td>
									<td>BrightWave Innovations</td>
									<td>
										michael@example.com
									</td>
									<td>
										(163) 2459 315
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-002</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-40.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Sophie
														Headrick</a></h6>
												<span class="fs-12 fw-normal ">Manager</span>
											</div>
										</div>
									</td>
									<td>Stellar Dynamics</td>
									<td>
										sophie@example.com
									</td>
									<td>
										(146) 1249 296
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-003</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-41.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Cameron
														Drake</a></h6>
												<span class="fs-12 fw-normal ">Director</span>
											</div>
										</div>
									</td>
									<td>Quantum Nexus</td>
									<td>
										cameron@example.com
									</td>
									<td>
										(135) 3489 516
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-004</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-42.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Doris
														Crowley</a></h6>
												<span class="fs-12 fw-normal ">Consultant</span>
											</div>
										</div>
									</td>
									<td>EcoVision Enterprises</td>
									<td>
										doris@example.com
									</td>
									<td>
										(158) 3459 596
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-005</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-44.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Thomas
														Bordelon</a></h6>
												<span class="fs-12 fw-normal ">Manager</span>
											</div>
										</div>
									</td>
									<td>Aurora Technologies</td>
									<td>
										thomas@example.com
									</td>
									<td>
										(196) 4862 196
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-006</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-45.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Kathleen
														Gutierrez</a></h6>
												<span class="fs-12 fw-normal ">Director</span>
											</div>
										</div>
									</td>
									<td>BlueSky Ventures</td>
									<td>
										kathleen@example.com
									</td>
									<td>
										(163) 6498 256
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-007</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-46.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Bruce Wright</a>
												</h6>
												<span class="fs-12 fw-normal ">CEO</span>
											</div>
										</div>
									</td>
									<td>TerraFusion Energy</td>
									<td>
										bruce@example.com
									</td>
									<td>
										(154) 6481 075
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-008</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-47.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Estelle
														Morgan</a></h6>
												<span class="fs-12 fw-normal ">Manager</span>
											</div>
										</div>
									</td>
									<td>UrbanPulse Design</td>
									<td>
										estelle@example.com
									</td>
									<td>
										(184) 6348 195
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-009</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-48.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Stephen Dias</a>
												</h6>
												<span class="fs-12 fw-normal ">CEO</span>
											</div>
										</div>
									</td>
									<td>Nimbus Networks</td>
									<td>
										stephen@example.com
									</td>
									<td>
										(175) 2496 125
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
									<td><a href="{{url('client-details')}}">Cli-010</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('client-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-43.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('client-details')}}">Angela
														Thomas</a></h6>
												<span class="fs-12 fw-normal ">Consultant</span>
											</div>
										</div>
									</td>
									<td>Epicurean Delights</td>
									<td>
										angela@example.com
									</td>
									<td>
										(132) 3145 977
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
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
			<!-- /Clients list -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection