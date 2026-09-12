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
					<h2 class="mb-1">Leads</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								CRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Leads List</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('leads')}}" class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('leads-grid')}}" class="btn btn-icon btn-sm"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_leads"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Lead</a>
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

			<!-- Leads List -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Leads List</h5>
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
								Tags
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Closed</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Contacted</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Lost</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Not Contacted</a>
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
									<th>Lead Name</th>
									<th>Company Name</th>
									<th>Phone</th>
									<th>Email</th>
									<th>Tags</th>
									<th>Created Date</th>
									<th>Lead Owner</th>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Collins</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">BrightWave Innovations</a></h6>
											</div>
										</div>
									</td>
									<td>(123) 4567 890</td>
									<td>anthony@example.com</td>
									<td>
										<span class="badge badge-success-transparent ">Closed</span>
									</td>
									<td>14 Jan 2024</td>
									<td>Hendry Milner</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Konopelski</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">Stellar Dynamics</a></h6>
											</div>
										</div>
									</td>
									<td>(179) 7382 829</td>
									<td>brian@example.com</td>
									<td>
										<span class="badge badge-purple-transparent ">Contacted</span>
									</td>
									<td>21 Jan 2024</td>
									<td>Guilory Berggren</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Adams</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">Quantum Nexus</a></h6>
											</div>
										</div>
									</td>
									<td>(184) 2719 738</td>
									<td>harvey@example.com</td>
									<td>
										<span class="badge badge-danger-transparent ">Lost</span>
									</td>
									<td>20 Feb 2024</td>
									<td>Jami Carlile</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Schumm</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">EcoVision Enterprises</a></h6>
											</div>
										</div>
									</td>
									<td>(193) 7839 748</td>
									<td>peral@example.com</td>
									<td>
										<span class="badge badge-warning-transparent ">Not Contacted</span>
									</td>
									<td>15 Mar 2024</td>
									<td>Theresa Nelson</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Wisozk</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">Aurora Technologies</a></h6>
											</div>
										</div>
									</td>
									<td>(183) 9302 890</td>
									<td>martniwr@example.com</td>
									<td>
										<span class="badge badge-success-transparent "> Closed</span>
									</td>
									<td>12 Apr 2024</td>
									<td>Smith Cooper</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Heller</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">BlueSky Ventures</a></h6>
											</div>
										</div>
									</td>
									<td>(120) 3728 039</td>
									<td>ray456@example.com</td>
									<td>
										<span class="badge badge-purple-transparent "> Contacted</span>
									</td>
									<td>20 Apr 2024</td>
									<td>Martin Lewis</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Gutkowski</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">TerraFusion Energy</a></h6>
											</div>
										</div>
									</td>
									<td>(102) 8480 832</td>
									<td>murray@example.com</td>
									<td>
										<span class="badge badge-warning-transparent "> Not Contacted</span>
									</td>
									<td>06 Jul 2024</td>
									<td>Newell Egan</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Walter</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">UrbanPulse Design</a></h6>
											</div>
										</div>
									</td>
									<td>(162) 8920 713</td>
									<td>smtih@example.com</td>
									<td>
										<span class="badge badge-success-transparent "> Closed</span>
									</td>
									<td>02 Sep 2024</td>
									<td>Janet Carlson</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Hansen</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">Nimbus Networks</a></h6>
											</div>
										</div>
									</td>
									<td>(189) 0920 723</td>
									<td>connie@example.com</td>
									<td>
										<span class="badge badge-success-transparent "> Closed</span>
									</td>
									<td>15 Nov 2024</td>
									<td>Craig Brown</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium"><a href="{{url('leads-details')}}">Leuschke</a></h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-normal fs-14 text-gray-5"><a
														href="{{url('company-details')}}">Epicurean Delights</a></h6>
											</div>
										</div>
									</td>
									<td>(168) 8392 823</td>
									<td>broaddus@example.com</td>
									<td>
										<span class="badge badge-danger-transparent "> Lost</span>
									</td>
									<td>10 Dec 2024</td>
									<td>Daniel Byrne</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_leads"><i class="ti ti-edit"></i></a>
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
			<!-- /Leads List -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection