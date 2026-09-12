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
					<h2 class="mb-1">Assets</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Assets
							</li>
							<li class="breadcrumb-item active" aria-current="page">Assets</li>
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
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_assets"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Asset</a>
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

			<!-- Assets Lists -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Assets List</h5>
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
								Status
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
									<th>Asset Name</th>
									<th>Asset User</th>
									<th>Purchase Date </th>
									<th>Warranty</th>
									<th>Warranty End Date</th>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
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
									<td>12 Sep 2024</td>
									<td>12 months</td>
									<td>
										12 Sep 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Canon Portable Printer</h6>
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
									<td>24 Oct 2024</td>
									<td>12 months</td>
									<td>
										24 Oct 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Sophie Headrick</a></h6>
											</div>
										</div>
									</td>
									<td>18 Feb 2024</td>
									<td>12 months</td>
									<td>
										18 Feb 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
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
									<td>17 Oct 2024</td>
									<td>12 months</td>
									<td>
										17 Oct 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-27.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Thomas Bordelon</a></h6>
											</div>
										</div>
									</td>
									<td>20 Jul 2024</td>
									<td>12 months</td>
									<td>
										20 Jul 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doglas Martini</a></h6>
											</div>
										</div>
									</td>
									<td>10 Apr 2024</td>
									<td>12 months</td>
									<td>
										10 Apr 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Cameron Drake</a></h6>
											</div>
										</div>
									</td>
									<td>29 Aug 2024</td>
									<td>12 months</td>
									<td>
										29 Aug 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-30.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Harvey Smith</a></h6>
											</div>
										</div>
									</td>
									<td>22 Feb 2024</td>
									<td>12 months</td>
									<td>
										22 Feb 2024
									</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border rounded-circle">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Michael Walker</a></h6>
											</div>
										</div>
									</td>
									<td>03 Nov 2024</td>
									<td>12 months</td>
									<td>
										03 Nov 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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
										<h6 class="fs-14 fw-medium">Dell Laptop</h6>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doris Crowley</a></h6>
											</div>
										</div>
									</td>
									<td>17 Dec 2024</td>
									<td>12 months</td>
									<td>
										17 Dec 2024
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active
										</span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_assets"><i class="ti ti-edit"></i></a>
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