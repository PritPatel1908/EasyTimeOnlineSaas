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
							<a href="{{url('job-list')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('job-grid')}}" class="btn btn-icon btn-sm active bg-primary text-white"><i
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
								class="ti ti-circle-plus me-2"></i>Post Job</a>
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
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between">
						<h5>Job Grid</h5>
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
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Recently
											Added</a>
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
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7
											Days</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/apple.svg')}}" class="w-auto h-auto"
													alt="icon"></span>
										</a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Senior IOS
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									New York, USA
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									30, 000 - 35, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									2 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/php.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Junior PHP
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Los Angeles, USA
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									20, 000 - 25, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									4 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/black.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Network
													Engineer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Bristol, UK
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									30, 000 - 35, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									1 year of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/react.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">React Developer
												</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Birmingham, UK
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									28, 000 - 32, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									3 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/laravel.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Laravel
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Washington, USA
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									32, 000 - 36, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									1 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/devops.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">DevOps Engineer</a>
											</h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Coventry, UK
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									25, 000 - 35, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									6 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/android.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Android
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Chicago, USA
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									28, 000 - 32, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									5 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/html.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">HTML Developer</a>
											</h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Carlisle, UK
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									25, 000 - 28, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									3 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/ui.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">UI/UX Designer</a>
											</h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									UI/UX Designer
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									20, 000 - 25, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									4 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/grafic.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Senior IOS
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									San Diego, USA
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									22, 000 - 28, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									3 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray"><img
													src="{{URL::asset('build/img/icons/angular.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Angular
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Sheffield, UK
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									28, 000 - 30, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									2 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="card bg-light">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<a href="#" class="me-2">
											<span class="avatar avatar-lg bg-gray-100"><img
													src="{{URL::asset('build/img/icons/nodejs.svg')}}" class="w-auto h-auto"
													alt="icon"></span></a>
										<div>
											<h6 class="fw-medium mb-1 text-truncate"><a href="#">Node js
													Developer</a></h6>
											<p class="fs-12 text-gray fw-normal">25 Applicants</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-column mb-3">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-map-pin-check text-gray-5 me-2"></i>
									Boston, USA
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-currency-dollar text-gray-5 me-2"></i>
									25, 000 - 28, 000 / month
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-briefcase text-gray-5 me-2"></i>
									3 years of experience
								</p>

							</div>
							<div class="mb-3">
								<span class="badge badge-pink-transparent me-2">Full Time</span>
								<span class="badge bg-secondary-transparent">Expert</span>
							</div>
							<div class="progress progress-xs mb-2">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
							</div>
							<div>
								<p class="fs-12 text-gray fw-normal">10 of 25 filled</p>
							</div>
						</div>
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