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
					<h2 class="mb-1">Refferals</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Recruitment
							</li>
							<li class="breadcrumb-item active" aria-current="page">Refferals</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
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
					<h5>Refferals List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

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
									<th>Refferals ID</th>
									<th> Referrer Name</th>
									<th>Job Reffered</th>
									<th>Referee Name</th>
									<th>Refferals Bonus</th>
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
									<td>Reff-001</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-32.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Anthony Lewis</a></h6>
												<span class="d-block mt-1">Finance</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/apple.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior IOS Developer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Harold Gaynor</a></h6>
												<span class="d-block mt-1">harold@example.com</span>
											</div>
										</div>
									</td>
									<td>$200</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-002</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Brian Villalobos</a></h6>
												<span class="d-block mt-1">Developer</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/php.svg')}}" class="img-fluid rounded-circle"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior PHP Developer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-29.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Sandra Ornellas</a></h6>
												<span class="d-block mt-1">sandra@example.com</span>
											</div>
										</div>
									</td>
									<td>$100</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-003</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Harvey Smith</a></h6>
												<span class="d-block mt-1">Developer</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/black.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Network Engineer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">John Harris</a></h6>
												<span class="d-block mt-1">john@example.com</span>
											</div>
										</div>
									</td>
									<td>$300</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-004</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Stephan Peralt</a></h6>
												<span class="d-block mt-1">Executive Officer</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/react.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior React Developer </a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-57.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Whitney Barnette</a></h6>
												<span class="d-block mt-1">whitney@example.com</span>
											</div>
										</div>
									</td>
									<td>$150</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-005</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-56.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doglas Martini</a></h6>
												<span class="d-block mt-1">Manager</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/laravel.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior Laravel Developer </a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-55.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Richard Thompson</a></h6>
												<span class="d-block mt-1">richard@example.com</span>
											</div>
										</div>
									</td>
									<td>$250</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-006</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-34.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Linda Ray</a></h6>
												<span class="d-block mt-1">Finance</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/devops.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">DevOps Engineer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-45.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Kerry Drake</a></h6>
												<span class="d-block mt-1">kerry@example.com</span>
											</div>
										</div>
									</td>
									<td>$400</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-007</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-42.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Elliot Murray</a></h6>
												<span class="d-block mt-1">Developer</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/android.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior Android Developer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-30.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">David Carmona</a></h6>
												<span class="d-block mt-1">david@example.com</span>
											</div>
										</div>
									</td>
									<td>$450</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-008</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-38.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Rebecca Smtih</a></h6>
												<span class="d-block mt-1">Executive</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/html.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior HTML Developer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-26.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Margaret Soto</a></h6>
												<span class="d-block mt-1">margaret@example.com</span>
											</div>
										</div>
									</td>
									<td>$220</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-009</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-52.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Connie Waters</a></h6>
												<span class="d-block mt-1">Developer</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/ui.svg')}}" class="img-fluid rounded-circle"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Junior UI/UX Designer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-44.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Jeffrey Thaler</a></h6>
												<span class="d-block mt-1">jeffrey@example.com</span>
											</div>
										</div>
									</td>
									<td>$180</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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
									<td>Reff-010</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-06.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Lori Broaddus</a></h6>
												<span class="d-block mt-1">Finance</span>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md bg-light rounded">
												<img src="{{URL::asset('build/img/icons/grafic.svg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Senior Graphic Designer</a></h6>

											</div>
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md ">
												<img src="{{URL::asset('build/img/users/user-10.jpg')}}"
													class="img-fluid rounded-circle" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Joyce Golston</a></h6>
												<span class="d-block mt-1">joyce@example.com</span>
											</div>
										</div>
									</td>
									<td>$250</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2"><i class="ti ti-edit"></i></a>
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