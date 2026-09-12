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
					<h2 class="mb-1">Domain</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Super Admin
							</li>
							<li class="breadcrumb-item active" aria-current="page">Domain List</li>
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
					<div class="ms-2 head-icons">
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
					<h5>Domain List</h5>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Yearly</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Approved</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
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
									<th>Domain URL</th>
									<th>Plan</th>
									<th>Created Date</th>
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
									<td>bwi.example.com</td>
									<td>Advanced (Monthly)</td>
									<td>12 Sep 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-checks me-1"></i>Approved
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
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
									<td>sd.example.com</td>
									<td>Basic (Yearly)</td>
									<td>24 Oct 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-skyblue d-inline-flex align-items-center badge-xs">
											<i class="ti ti-clock me-1"></i>Pending
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_pending"><i class="ti ti-eye"></i></a>
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
									<td>qn.example.com</td>
									<td>Advanced (Monthly)</td>
									<td>18 Feb 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-x me-1"></i>Rejected
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_rejected"><i class="ti ti-eye"></i></a>
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
									<td>eve.example.com</td>
									<td>Advanced (Monthly)</td>
									<td>17 Oct 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-checks me-1"></i>Approved
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
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
									<td>at.example.com</td>
									<td>Enterprise (Monthly)</td>
									<td>20 Jul 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-checks me-1"></i>Approved
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
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
									<td>bsv.example.com</td>
									<td>Advanced (Monthly)</td>
									<td>10 Apr 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-skyblue d-inline-flex align-items-center badge-xs">
											<i class="ti ti-clock me-1"></i>Pending
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_pending"><i class="ti ti-eye"></i></a>
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
									<td>tfe.example.com</td>
									<td>Enterprise (Yearly)</td>
									<td>29 Aug 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-checks me-1"></i>Approved
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
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
									<td>upd.example.com</td>
									<td>Basic (Monthly)</td>
									<td>22 Feb 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-x me-1"></i>Rejected
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_rejected"><i class="ti ti-eye"></i></a>
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
									<td>nn.example.com</td>
									<td>Basic (Yearly)</td>
									<td>03 Nov 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-checks me-1"></i>Approved
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
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
									<td>ed.example.com</td>
									<td>Advanced (Monthly)</td>
									<td>17 Dec 2024</td>
									<td>
										<a href="#"
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-checks me-1"></i>Approved
										</a>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
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

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection