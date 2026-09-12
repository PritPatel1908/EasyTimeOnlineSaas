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
					<h2 class="mb-1">Tenant Usage Metrics</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Super Admin
							</li>
							<li class="breadcrumb-item active" aria-current="page">Tenant Usage Metrics</li>
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
					<div class="head-icons">
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
					<h5>Tenants Usage List</h5>
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
								Select Plan
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Advanced
										(Monthly)</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Basic (Yearly)</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Enterprise
										(Monthly)</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">InActive</a>
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
									<th>Tenants</th>
									<th>Plan</th>
									<th>Active Users</th>
									<th>Most Module Usage</th>
									<th>Storage Usage</th>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">BrightWave Innovations</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Advanced (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>82</td>
									<td>HRM, CRM</td>
									<td>12.4 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Stellar Dynamics</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Basic (Yearly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>90</td>
									<td>Recruitment</td>
									<td>10.8 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Quantum Nexus</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Advanced (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>104</td>
									<td>Finance & Accounts</td>
									<td>8.5 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">EcoVision Enterprises</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Advanced (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>80</td>
									<td>HRM</td>
									<td>6.0 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Aurora Technologies</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Enterprise (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>120</td>
									<td>CRM</td>
									<td>15.2 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">BlueSky Ventures</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Advanced (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>100</td>
									<td>Finance & Accounts</td>
									<td>12.5 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">TerraFusion Energy</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Enterprise (Yearly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>110</td>
									<td>HRM, Recruitment</td>
									<td>10.7 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">UrbanPulse Design</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Basic (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>125</td>
									<td>Recruitment</td>
									<td>9.4 GB</td>
									<td>
										<span class="badge badge-danger d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Nimbus Networks</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Basic (Yearly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>115</td>
									<td>HRM, CRM</td>
									<td>8.2 GB</td>
									<td>
										<span class="badge badge-success d-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Epicurean Delights</a></h6>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex justify-content-between align-items-center gap-2">
											<span>Advanced (Monthly)</span>
											<span class="badge badge-purple">Upgrade</span>
										</div>
									</td>
									<td>85</td>
									<td>Finance & Accounts</td>
									<td>7.5 GB</td>
									<td>
										<span class="badge badge-success dlign-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#view_details"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-refresh"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-ban"></i></a>
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