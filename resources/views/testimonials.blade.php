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
					<h2 class="mb-1">Testimonials</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Content
							</li>
							<li class="breadcrumb-item active" aria-current="page">Testimonials</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_testimonials"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Testimonial</a>
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
					<h5>Testimonials List</h5>
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
									<th>Author</th>
									<th>Role</th>
									<th>Content</th>
									<th>Created Date</th>
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
												<img src="{{URL::asset('build/img/testimonials/user-01.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Ivan Lucas</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>This system streamlined our HR processes, saving us time and boosting team
										efficiency.</td>
									<td>12 Sep 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-02.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">John Mason</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>This system has made payroll and attendance tracking so much easier for our
										team</td>
									<td>24 Oct 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-03.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Susan Hill</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>We've significantly reduced paperwork and manual errors since implementing
										this platform</td>
									<td>18 Feb 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-04.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Gary Payton</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>Managing employee leave requests and benefits is now a hassle-free process
									</td>
									<td>17 Oct 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-05.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Jennifer Vaughn</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>This platform has drastically cut down the time we spend on recruitment and
										onboarding.</td>
									<td>20 Jul 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-06.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Ricky Easley</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>The system's ease of use has greatly improved our HR team's productivity.
									</td>
									<td>10 Apr 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-07.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">David Rodriguez</a></h6>
											</div>
										</div>
									</td>
									<td>HR Manager</td>
									<td>The platform allows me to update my personal details and view my payslips
										instantly</td>
									<td>29 Aug 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-08.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Dennis Lorenzo</a></h6>
											</div>
										</div>
									</td>
									<td>Employee</td>
									<td>Submitting my timesheets and checking my attendance is super easy with this
										tool.</td>
									<td>22 Feb 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-09.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Barry Ducote</a></h6>
											</div>
										</div>
									</td>
									<td>Manager</td>
									<td>Enhanced employee performance tracking and simplified payroll management
										effortlessly.</td>
									<td>03 Nov 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/testimonials/user-10.jpg')}}" class="img-fluid"
													alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Judy Walton</a></h6>
											</div>
										</div>
									</td>
									<td>Employee</td>
									<td>It’s great to see all my benefits and compensation details in one
										user-friendly interface.</td>
									<td>17 Dec 2024</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" data-bs-toggle="modal" data-bs-target="#edit_testimonials"
												class="me-2"><i class="ti ti-edit"></i></a>
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