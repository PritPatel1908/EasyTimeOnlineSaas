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
					<h2 class="mb-1">Resume Parsing</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Recruitment
							</li>
							<li class="breadcrumb-item active" aria-current="page">Resume Parsing</li>
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
					<h5>Resume List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="input-icon position-relative me-3">
							<span class="input-icon-addon">
								<i class="ti ti-calendar text-gray-9"></i>
							</span>
							<input type="text" class="form-control date-range bookingrange"
								placeholder="dd/mm/yyyy - dd/mm/yyyy">
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Designation
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
									<th>Cand ID</th>
									<th>Candidate</th>
									<th>Applied Role</th>
									<th>Phone</th>
									<th>Expereience</th>
									<th>Location</th>
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
									<td>Cand-001</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-49.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Harold Gaynor</a></h6>
												<span class="fs-12 fw-normal ">harold@example.com</span>
											</div>
										</div>
									</td>
									<td>Admin</td>
									<td>(128) 0975 348</td>
									<td>4 yrs</td>
									<td>New York</td>
									<td>
										<span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Parsed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-file-invoice"></i></a>
											<a href="#" class="me-2"><i class="ti ti-download"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>Cand-002</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Sandra Ornellas</a></h6>
												<span class="fs-12 fw-normal">sandra@example.com</span>
											</div>
										</div>
									</td>
									<td>App Developer</td>
									<td>(148) 9648 218</td>
									<td>3 yrs</td>
									<td>Los Angeles</td>
									<td>
										<span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Parsed
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-file-invoice"></i></a>
											<a href="#" class="me-2"><i class="ti ti-download"></i></a>
										</div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-003</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-05.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">John Harris</a></h6>
												<span class="fs-12 fw-normal">john@example.com</span>
											</div>
										</div>
									</td>
									<td>Technician</td>
									<td>(196) 2348 947</td>
									<td>5 yrs</td>
									<td>Chicago</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-004</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-03.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Carole Langan</a></h6>
												<span class="fs-12 fw-normal">carole@example.com</span>
											</div>
										</div>
									</td>
									<td>Web Developer</td>
									<td>(138) 6487 295</td>
									<td>1 yr</td>
									<td>Houston</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-005</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-11.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Charles Marks</a></h6>
												<span class="fs-12 fw-normal">charles@example.com</span>
											</div>
										</div>
									</td>
									<td>Sales Executive Officer</td>
									<td>(154) 6485 218</td>
									<td>4 yrs</td>
									<td>Phoenix</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-006</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-05.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Kerry Drake</a></h6>
												<span class="fs-12 fw-normal">kerry@example.com</span>
											</div>
										</div>
									</td>
									<td>Designer</td>
									<td>(185) 5947 097</td>
									<td>2 yrs</td>
									<td>Dallas</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-007</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-15.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">David Carmona</a></h6>
												<span class="fs-12 fw-normal">david@example.com</span>
											</div>
										</div>
									</td>
									<td>Account Manager</td>
									<td>(106) 3485 978</td>
									<td>3 yrs</td>
									<td>Austin</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-008</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-07.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Margaret Soto</a></h6>
												<span class="fs-12 fw-normal">margaret@example.com</span>
											</div>
										</div>
									</td>
									<td>SEO Analyst</td>
									<td>(174) 3795 107</td>
									<td>5 yrs</td>
									<td>Boston</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-009</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Jeffrey Thaler</a></h6>
												<span class="fs-12 fw-normal">jeffrey@example.com</span>
											</div>
										</div>
									</td>
									<td>Admin</td>
									<td>(128) 0975 348</td>
									<td>4 yrs</td>
									<td>Miami</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
									</td>
								</tr>

								<tr>
									<td>
										<div class="form-check form-check-md"><input class="form-check-input"
												type="checkbox"></div>
									</td>
									<td>Cand-010</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded"><img
													src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid"
													alt="img"></a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Joyce Golston</a></h6>
												<span class="fs-12 fw-normal">joyce@example.com</span>
											</div>
										</div>
									</td>
									<td>Business Analyst</td>
									<td>(132) 1876 304</td>
									<td>2 yrs</td>
									<td>Denver</td>
									<td><span
											class="badge rounded bg-outline-info d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Parsed</span></td>
									<td>
										<div class="action-icon d-inline-flex"><a href="#" class="me-2"><i
													class="ti ti-file-invoice"></i></a><a href="#" class="me-2"><i
													class="ti ti-download"></i></a></div>
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