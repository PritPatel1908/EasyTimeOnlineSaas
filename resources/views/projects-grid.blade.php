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
					<h2 class="mb-1">Projects</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Projects
							</li>
							<li class="breadcrumb-item active" aria-current="page">Projects Grid</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('projects')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('projects-grid')}}" class="btn btn-icon btn-sm active bg-primary text-white"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_project"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Project</a>
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
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
						<h5>Projects Grid</h5>
						<div class="d-flex align-items-center flex-wrap row-gap-3">
							<div class="dropdown me-2">
								<a href="javascript:void(0);"
									class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
									data-bs-toggle="dropdown">
									Select Status
								</a>
								<ul class="dropdown-menu  dropdown-menu-end p-3">
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Select
											Status</a>
									</li>
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
									class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
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

			<!-- Project Grid -->
			<div class="row">
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Office Management</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">An office management app project
									streamlines administrative tasks by integrating
									tools for scheduling, communication, and
									task management, enhancing overall productivity and efficiency.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-39.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Anthony Lewis</a>
										</h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">
									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">14 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">6</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Clinic Management </a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">A clinic management project streamlines
									patient records, appointments, and billing processes to improve operational
									efficiency.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-40.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Sophie
												Headrick</a></h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">15 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">7</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Educational Platform</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">An educational platform project provides
									a centralized space for delivering online courses, tracking progress, and
									managing student assessments.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-41.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Cameron Drake</a>
										</h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">16 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">5</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-11.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}"> Chat & Call Mobile App</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">A chat and call mobile app enables users
									to send messages, make voice and video calls, and share media seamlessly across
									devices.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-42.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Doris Crowley</a>
										</h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">17 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">6</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Travel Planning Website</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">A travel planning website helps users
									explore destinations, compare flights and accommodations, and create
									personalized itineraries.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-43.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Thomas
												Bordelon</a></h6>
										<span class="fs-12 fw-normal">Manager</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">18 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">8</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Service Booking Software</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">Service booking software enables users to
									schedule appointments, manage bookings, and handle payments for various
									services.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-45.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Kathleen
												Gutierrez</a></h6>
										<span class="fs-12 fw-normal">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">19 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">8</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Hotel Booking App</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">A hotel booking app allows users to
									search, compare, and book accommodations with ease, offering a wide range of
									options.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-46.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Bruce Wright</a>
										</h6>
										<span class="fs-12 fw-normal">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">20 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">8</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Car & Bike Rental Software</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">Car and bike rental software allows users
									to browse, reserve, and rent vehicles efficiently through an online platform.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-47.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Rebecca Smtih</a>
										</h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">17 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">6</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Navigation and Safety App</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">A navigation and safety app provides
									real-time GPS guidance, traffic updates, and route optimization to help users
									reach their destinations efficiently.A navigation and safety app provides
									real-time GPS guidance, traffic updates, and route optimization to help users
									reach their destinations efficiently.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-28.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Connie Waters</a>
										</h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">14 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">6</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Food Order App</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">A food order app allows users to browse
									menus, place orders, and track delivery from their favorite restaurants with
									ease.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-42.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Lori Broaddus</a>
										</h6>
										<span class="fs-12 fw-normal ">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">15 Jan 2024</p>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">7</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">POS Admin Software</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">POS admin software enables businesses to
									manage sales, track inventory, and process transactions efficiently through a
									centralized platform.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-48.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Stephen Dias</a>
										</h6>
										<span class="fs-12 fw-normal ">CEO</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">22 Jan 2024</p>

									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">5</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-26.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<h6><a href="{{url('project-details')}}">Invoicing & Billing Software</a></h6>
								<div class="dropdown">
									<a href="javascript:void(0);" class="d-inline-flex align-items-center"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</a>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#edit_project"><i
													class="ti ti-edit me-2"></i>Edit</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-3 pb-3 border-bottom">
								<p class="text-truncate line-clamb-3 mb-0">Invoicing and billing software automates
									the creation, sending, and tracking of invoices, making payment processes
									quicker and more efficient.
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
								<div class="d-flex align-items-center file-name-icon">
									<a href="javascript:void(0);"
										class="avatar avatar-sm avatar-rounded flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-50.jpg')}}" class="img-fluid" alt="img">
									</a>
									<div class="ms-2">
										<h6 class="fw-normal fs-12"><a href="javascript:void(0);">Angela Thomas</a>
										</h6>
										<span class="fs-12 fw-normal">Project Leader</span>
									</div>
								</div>
								<div class="d-flex align-items-center">

									<div>
										<span class="fs-12 fw-normal ">Deadline</span>
										<p class="mb-0 fs-12">23 Jan 2024</p>

									</div>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center">
									<span
										class="avatar avatar-sm avatar-rounded bg-success-transparent flex-shrink-0 me-2">
										<i class="ti ti-checklist text-success fs-16"></i>
									</span>
									<p>
										<small>Tasks : </small>
										<span class="text-dark">8</span>/10
									</p>
								</div>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-30.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12 fw-medium"
										href="javascript:void(0);">
										+2
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="text-center mb-4">
						<a href="#" class="btn btn-primary"><i class="ti ti-loader-3 me-1"></i>Load More</a>
					</div>
				</div>
			</div>
			<!-- / Project Grid -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection