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
					<h2 class="mb-1">Jobs</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Recruitment
							</li>
							<li class="breadcrumb-item active" aria-current="page">Jobs</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">

					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('job-list')}}" class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('job-grid')}}" class="btn btn-icon btn-sm"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_post"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Post job</a>
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
					<h5>Job List</h5>
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
								Role
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Senior IOS
										Developer</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Junior PHP
										Developer</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Network
										Engineer</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Accepted</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">sent</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Expired</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Declined</a>
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
									<th>Job ID</th>
									<th>Job Title</th>
									<th>Category</th>
									<th>Location</th>
									<th>Salary Range</th>
									<th>Posted Date</th>
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
									<td>Job-001</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/apple.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior IOS Developer</a></h6>
												<span class="d-block mt-1">25 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>New York , USA</td>
									<td>30, 000 - 35, 000 / month</td>
									<td>12 Sep 2024 </td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-002</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/php.svg')}}" class="img-fluid rounded-circle"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior PHP Developer</a></h6>
												<span class="d-block mt-1">20 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Los Angeles, USA</td>
									<td>20, 000 - 25, 000 / month</td>
									<td>24 Oct 2024 </td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-003</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/black.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior PHP Developer</a></h6>
												<span class="d-block mt-1">20 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Los Angeles, USA</td>
									<td>20, 000 - 25, 000 / month</td>
									<td>24 Oct 2024 </td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-004</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/react.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior React Developer </a></h6>
												<span class="d-block mt-1">35 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Bristol, UK</td>
									<td>30, 000 - 35, 000 / month</td>
									<td>18 Feb 2024 </td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-005</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/laravel.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior Laravel Developer </a></h6>
												<span class="d-block mt-1">40 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Washington, USA</td>
									<td>32, 000 - 36, 000 / month</td>
									<td>20 Jul 2024</td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-006</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/devops.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">DevOps Engineer</a></h6>
												<span class="d-block mt-1">20 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Coventry, UK</td>
									<td>25, 000 - 35, 000 / month</td>
									<td>10 Apr 2024</td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-007</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/android.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior Android Developer</a></h6>
												<span class="d-block mt-1">25 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Chicago, USA</td>
									<td>28, 000 - 32, 000 / month</td>
									<td>29 Aug 2024</td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-008</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/html.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior HTML Developer</a></h6>
												<span class="d-block mt-1">35 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Carlisle, UK</td>
									<td>25, 000 - 28, 000 / month</td>
									<td>22 Feb 2024</td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-009</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/ui.svg')}}" class="img-fluid rounded-circle"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior UI/UX Designer</a></h6>
												<span class="d-block mt-1">20 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>Lancaster, UK</td>
									<td>20, 000 - 25, 000 / month</td>
									<td>03 Nov 2024</td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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
									<td>Job-010</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/grafic.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior Graphic Designer</a></h6>
												<span class="d-block mt-1">25 Applicants</span>
											</div>
										</div>
									</td>
									<td>Software</td>
									<td>San Diego, USA</td>
									<td>22, 000 - 28, 000 / month</td>
									<td>17 Dec 2024</td>

									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_post"><i class="ti ti-edit"></i></a>
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