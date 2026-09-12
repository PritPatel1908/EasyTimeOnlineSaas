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
					<h2 class="mb-1">Companies</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								CRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Companies List</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('companies-crm')}}"
								class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('companies-grid')}}" class="btn btn-icon btn-sm"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_company"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Company</a>
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
					<h5>Companies List</h5>
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
								Company
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">BrightWave
										Innovations </a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Quantum Nexus</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">TerraFusion
										Energy</a>
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
									<th>Company Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Location</th>
									<th>Rating</th>
									<th>Owner</th>
									<th>Contact</th>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">BrightWave
														Innovations</a></h6>
											</div>
										</div>
									</td>
									<td>michael@example.com</td>
									<td>(163) 2459 315</td>
									<td>Germany</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>4.2</span>
									</td>
									<td>Hendry Milner</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">Stellar
														Dynamics</a></h6>
											</div>
										</div>
									</td>
									<td>sophie@example.com</td>
									<td>(146) 1249 296</td>
									<td>USA</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>5.0</span>
									</td>
									<td>Guilory Berggren</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">Quantum
														Nexus</a></h6>
											</div>
										</div>
									</td>
									<td>cameron@example.com</td>
									<td>(135) 3489 516</td>
									<td>Canada</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>3.5</span>
									</td>
									<td>Jami Carlile</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">EcoVision
														Enterprises</a></h6>
											</div>
										</div>
									</td>
									<td>doris@example.com</td>
									<td>(158) 3459 596</td>
									<td>India</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>4.5</span>
									</td>
									<td>Theresa Nelson</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">Aurora
														Technologies</a></h6>
											</div>
										</div>
									</td>
									<td>thomas@example.com</td>
									<td>(196) 4862 196</td>
									<td>China</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>4.7</span>
									</td>
									<td>Smith Cooper</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">BlueSky
														Ventures</a></h6>
											</div>
										</div>
									</td>
									<td>kathleen@example.com</td>
									<td>(163) 6498 256</td>
									<td>Japan</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>5.0</span>
									</td>
									<td>Martin Lewis</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">TerraFusion
														Energy</a></h6>
											</div>
										</div>
									</td>
									<td>bruce@example.com</td>
									<td>(154) 6481 075</td>
									<td>Indonesia</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>3.1</span>
									</td>
									<td>Newell Egan</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">UrbanPulse
														Design</a></h6>
											</div>
										</div>
									</td>
									<td>estelle@example.com</td>
									<td>(184) 6348 195</td>
									<td>Cuba</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>5.0</span>
									</td>
									<td>Janet Carlson</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">Nimbus
														Networks</a></h6>
											</div>
										</div>
									</td>
									<td>stephen@example.com</td>
									<td>(175) 2496 125</td>
									<td>Israel</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>2.7</span>
									</td>
									<td>Craig Brown</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
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
											<a href="{{url('company-details')}}"
												class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="{{url('company-details')}}">Epicurean
														Delights</a></h6>
											</div>
										</div>
									</td>
									<td>angela@example.com</td>
									<td>(132) 3145 977</td>
									<td>Colombia</td>
									<td>
										<span><i class="ti ti-star-filled text-warning me-2"></i>3.0</span>
									</td>
									<td>Daniel Byrne</td>
									<td>
										<ul class="contact-icon d-flex align-items-center ">
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-mail d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-mail text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-call d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-phone-call text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-msg d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-message-2 text-gray-5"></i></span></a></li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-skype d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-skype text-gray-5"></i></span></a>
											</li>
											<li><a href="#"
													class="p-1 rounded-circle contact-icon-facebook d-flex align-items-center justify-content-center"><span
														class="d-flex align-items-center justify-content-center"><i
															class="ti ti-brand-facebook text-gray-5"></i></span></a>
											</li>
										</ul>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="{{url('company-details')}}" class="me-2"><i
													class="ti ti-eye"></i></a>
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_company"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-target="#delete_modal"
												data-bs-toggle="modal"><i class="ti ti-trash"></i></a>
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