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
					<h2 class="mb-1">Certification Tracking</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Training
							</li>
							<li class="breadcrumb-item active" aria-current="page">Certification Tracking</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2 me-2">
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_modal"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add New Certification</a>
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
					<h5>Certification List</h5>
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
									<th>Employee Name</th>
									<th>Training Type</th>
									<th>Trainer Name</th>
									<th>Date</th>
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
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Anthony Lewis</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">Git Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Harold Gaynor</a>
											</div>
										</div>
									</td>
									<td>14 Jun 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-13.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Brian Villalobos</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">HTML Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Sandra Ornellas</a>
											</div>
										</div>
									</td>
									<td>25 May 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-12.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Harvey Smith</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">React Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">John Harris</a>
											</div>
										</div>
									</td>
									<td>10 May 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Stephan Peralt</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">Nodejs Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Whitney Barnette</a>
											</div>
										</div>
									</td>
									<td>28 Apr 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-15.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Doglas Martini</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">Django Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Richard Thompson</a>
											</div>
										</div>
									</td>
									<td>15 Apr 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-14.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Linda Ray</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">Java Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-06.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Kerry Drake</a>
											</div>
										</div>
									</td>
									<td>20 Mar 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Elliot Murray</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">MySQL Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">David Carmona</a>
											</div>
										</div>
									</td>
									<td>10 Mar 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-18.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Rebecca Smtih</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">DevOps Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Margaret Soto</a>
											</div>
										</div>
									</td>
									<td>17 Feb 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Connie Waters</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">Angular Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Jeffrey Thaler</a>
											</div>
										</div>
									</td>
									<td>02 Feb 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
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
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Lori Broaddus</a>
											</div>
										</div>
									</td>
									<td>
										<p class="mb-0 fw-medium text-dark">Vuejs Training</p>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Joyce Golston</a>
											</div>
										</div>
									</td>
									<td>24 Jan 2025</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#details_modal"><i class="ti ti-eye"></i></a>
											<a href="#" class="me-2"><i class="ti ti-download"></i></a>
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