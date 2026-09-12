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
					<h2 class="mb-1">Candidates</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Recruitment
							</li>
							<li class="breadcrumb-item active" aria-current="page">Candidates Grid</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('candidates-kanban')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-layout-kanban"></i></a>
							<a href="{{url('candidates')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('candidates-grid')}}"
								class="btn btn-icon btn-sm active bg-primary text-white"><i
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
					<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
						<h5>Candidates Grid</h5>
						<div class="d-flex align-items-center flex-wrap row-gap-3">
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
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Accountant</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">App
											Developer</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Technician</a>
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
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Scheduled</a>
									</li>
									<li>
										<a href="javascript:void(0);"
											class="dropdown-item rounded-1">Interviewed</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Offered</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Rejected</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Hired</a>
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

			<!-- Candidates Grid -->
			<div class="row">
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-39.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Harold Gaynor</a></h6>
											<span class="badge bg-primary-transparent">Cand-001</span>
										</div>
										<p class="text-gray fs-13 fw-normal">harold@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Accountant</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-purple"> <i
											class="ti ti-point-filled"></i> New</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-40.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"> <a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Sandra Ornellas</a></h6>
											<span class="badge bg-primary-transparent">Cand-002</span>
										</div>
										<p class="text-gray fs-13 fw-normal">sandra@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Accountant</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-pink"> <i class="ti ti-point-filled"></i>
										Scheduled</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-41.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">John Harris</a></h6>
											<span class="badge bg-primary-transparent">Cand-003</span>
										</div>
										<p class="text-gray fs-13 fw-normal">john@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Technician</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-info"> <i class="ti ti-point-filled"></i>
										Interviewed</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-42.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Carole Langan</a></h6>
											<span class="badge bg-primary-transparent">Cand-004</span>
										</div>
										<p class="text-gray fs-13 fw-normal">carole@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Web Developer</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-warning"> <i
											class="ti ti-point-filled"></i> Offered</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-44.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Charles Marks</a></h6>
											<span class="badge bg-primary-transparent">Cand-005</span>
										</div>
										<p class="text-gray fs-13 fw-normal">charles@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">SEO</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-success"> <i
											class="ti ti-point-filled"></i> Hired</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-43.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Kerry Drake</a></h6>
											<span class="badge bg-primary-transparent">Cand-006</span>
										</div>
										<p class="text-gray fs-13 fw-normal">kerry@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Designer</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-danger"> <i
											class="ti ti-point-filled"></i> Rejected</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-46.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">David Carmona</a></h6>
											<span class="badge bg-primary-transparent">Cand-007</span>
										</div>
										<p class="text-gray fs-13 fw-normal">david@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Account Manager</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-success"> <i
											class="ti ti-point-filled"></i> Hired</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-45.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Margaret Soto</a></h6>
											<span class="badge bg-primary-transparent">Cand-008</span>
										</div>
										<p class="text-gray fs-13 fw-normal">margaret@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">SEO Analyst</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-pink"> <i class="ti ti-point-filled"></i>
										Scheduled</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-48.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Jeffrey Thaler</a></h6>
											<span class="badge bg-primary-transparent">Cand-009</span>
										</div>
										<p class="text-gray fs-13 fw-normal">jeffrey@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Admin</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-purple"> <i
											class="ti ti-point-filled"></i> New</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-47.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Joyce Golston</a></h6>
											<span class="badge bg-primary-transparent">Cand-010</span>
										</div>
										<p class="text-gray fs-13 fw-normal">joyce@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Business Analyst</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-success"> <i
											class="ti ti-point-filled"></i> Hired</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-49.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Cedric Rosalez</a></h6>
											<span class="badge bg-primary-transparent">Cand-011</span>
										</div>
										<p class="text-gray fs-13 fw-normal">cedric@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Financial Analyst</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-purple"> <i
											class="ti ti-point-filled"></i> New</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="d-flex align-items-center flex-shrink-0">
									<a href="#" class="avatar avatar-lg avatar rounded-circle me-2"
										data-bs-toggle="offcanvas" data-bs-target="#candidate_details">
										<img src="{{URL::asset('build/img/users/user-50.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
									<div class="d-flex flex-column">
										<div class="d-flex flex-wrap mb-1">
											<h6 class="fs-16 fw-semibold me-1"><a href="#"
													data-bs-toggle="offcanvas"
													data-bs-target="#candidate_details">Lillie Diaz</a></h6>
											<span class="badge bg-primary-transparent">Cand-012</span>
										</div>
										<p class="text-gray fs-13 fw-normal">lillie@example.com</p>
									</div>
								</div>
							</div>
							<div class="bg-light rounder p-2">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
									<span class="text-dark fs-14 fw-medium">Receptionist</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
									<span class="text-dark fs-14 fw-medium">12 Sep 2024</span>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="text-gray fs-14 fw-normal">Status</h6>
									<span class="fs-10 fw-medium badge bg-danger"> <i
											class="ti ti-point-filled"></i> Rejected</span>
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
			<!-- /Candidates Grid -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection