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
					<h2 class="mb-1">Kanban View</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Applications
							</li>
							<li class="breadcrumb-item active" aria-current="page">Kanban View</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="dropdown">
						<a href="javascript:void(0);"
							class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
							data-bs-toggle="dropdown">
							<i class="ti ti-file-export me-2"></i>
							Export
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
					<div class="ms-2 mb-0 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h4>Projects</h4>
					<div class="d-flex align-items-center flex-wrap row-gap-3">
						<div class="avatar-list-stacked avatar-group-sm me-3">
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded bg-primary fs-12">
								1+
							</span>
						</div>
						<div class="d-flex align-items-center me-3">
							<p class="mb-0 me-3 pe-3 border-end fs-14">Total Task : <span class="text-dark"> 55
								</span></p>
							<p class="mb-0 me-3 pe-3 border-end fs-14">Pending : <span class="text-dark"> 15 </span>
							</p>
							<p class="mb-0 fs-14">Completed : <span class="text-dark"> 40 </span></p>
						</div>
						<div class="input-icon-start position-relative">
							<span class="input-icon-addon">
								<i class="ti ti-search"></i>
							</span>
							<input type="text" class="form-control" placeholder="Search Project">
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-4">
							<div class="d-flex align-items-center flex-wrap row-gap-3 mb-3">
								<h6 class="me-2">Priority</h6>
								<ul class="nav nav-pills border d-inline-flex p-1 rounded bg-light todo-tabs"
									id="pills-tab" role="tablist">
									<li class="nav-item" role="presentation">
										<button
											class="nav-link btn btn-sm btn-icon py-3 d-flex align-items-center justify-content-center w-auto active"
											data-bs-toggle="pill" data-bs-target="#pills-home" type="button"
											role="tab" aria-selected="true">All</button>
									</li>
									<li class="nav-item" role="presentation">
										<button
											class="nav-link btn btn-sm btn-icon py-3 d-flex align-items-center justify-content-center w-auto"
											data-bs-toggle="pill" data-bs-target="#pills-contact" type="button"
											role="tab" aria-selected="false">High</button>
									</li>
									<li class="nav-item" role="presentation">
										<button
											class="nav-link btn btn-sm btn-icon py-3 d-flex align-items-center justify-content-center w-auto"
											data-bs-toggle="pill" data-bs-target="#pills-medium" type="button"
											role="tab" aria-selected="false">Medium</button>
									</li>
									<li class="nav-item" role="presentation">
										<button
											class="nav-link btn btn-sm btn-icon py-3 d-flex align-items-center justify-content-center w-auto"
											data-bs-toggle="pill" data-bs-target="#pills-low" type="button"
											role="tab" aria-selected="false">Low</button>
									</li>
								</ul>
							</div>
						</div>
						<div class="col-lg-8">
							<div class="d-flex align-items-center justify-content-lg-end flex-wrap row-gap-3 mb-3">
								<div class="dropdown me-2">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										Clients
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Clients</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Sophie</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Cameron</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Doris</a>
										</li>
									</ul>
								</div>
								<div class="input-icon w-120 position-relative me-2">
									<span class="input-icon-addon">
										<i class="ti ti-calendar text-gray-9"></i>
									</span>
									<input type="text" class="form-control datetimepicker"
										placeholder="Created Date">
								</div>
								<div class="input-icon w-120 position-relative me-2">
									<span class="input-icon-addon">
										<i class="ti ti-calendar text-gray-9"></i>
									</span>
									<input type="text" class="form-control datetimepicker" placeholder="Due Date">
								</div>
								<div class="dropdown me-2">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										Select Status
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Inprogress</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">On-hold</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Completed</a>
										</li>
									</ul>
								</div>
								<div class="d-flex align-items-center border p-2 rounded">
									<span class="d-inline-flex me-2">Sort By : </span>
									<div class="dropdown">
										<a href="javascript:void(0);"
											class="dropdown-toggle btn btn-white d-inline-flex align-items-center border-0 bg-transparent p-0 text-dark"
											data-bs-toggle="dropdown">
											Created Date
										</a>
										<ul class="dropdown-menu  dropdown-menu-end p-3">
											<li>
												<a href="javascript:void(0);"
													class="dropdown-item rounded-1">Created Date</a>
											</li>
											<li>
												<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7
													Days</a>
											</li>
											<li>
												<a href="javascript:void(0);" class="dropdown-item rounded-1">Due
													Date</a>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-content" id="pills-tabContent">
						<div class="tab-pane fade show active" id="pills-home" role="tabpanel">
							<div class="d-flex align-items-start overflow-auto project-status pb-4">
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-pink p-1 d-flex rounded-circle me-2"><span
														class="bg-pink rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">New</h5>
												<span class="badge bg-light rounded-pill">02</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-154</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-155</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-skyblue p-1 d-flex rounded-circle me-2"><span
														class="bg-skyblue rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Inprogress</h5>
												<span class="badge bg-light rounded-pill">13</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-156</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-157</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-158</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-danger p-1 d-flex rounded-circle me-2"><span
														class="bg-danger rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">On-hold</h5>
												<span class="badge bg-light rounded-pill">04</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-159</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-160</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-success p-1 d-flex rounded-circle me-2"><span
														class="bg-success rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Completed</h5>
												<span class="badge bg-light rounded-pill">10</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-161</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="pills-contact" role="tabpanel">
							<div class="d-flex align-items-start overflow-auto project-status pb-4">
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-pink p-1 d-flex rounded-circle me-2"><span
														class="bg-pink rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">New</h5>
												<span class="badge bg-light rounded-pill">02</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-154</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-155</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-skyblue p-1 d-flex rounded-circle me-2"><span
														class="bg-skyblue rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Inprogress</h5>
												<span class="badge bg-light rounded-pill">13</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-156</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-157</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-danger p-1 d-flex rounded-circle me-2"><span
														class="bg-danger rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">On-hold</h5>
												<span class="badge bg-light rounded-pill">04</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-159</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-success p-1 d-flex rounded-circle me-2"><span
														class="bg-success rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Completed</h5>
												<span class="badge bg-light rounded-pill">10</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-purple badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>High</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-161</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="pills-medium" role="tabpanel">
							<div class="d-flex align-items-start overflow-auto project-status pb-4">
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-pink p-1 d-flex rounded-circle me-2"><span
														class="bg-pink rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">New</h5>
												<span class="badge bg-light rounded-pill">02</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-154</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-155</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-skyblue p-1 d-flex rounded-circle me-2"><span
														class="bg-skyblue rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Inprogress</h5>
												<span class="badge bg-light rounded-pill">13</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-156</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-157</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-danger p-1 d-flex rounded-circle me-2"><span
														class="bg-danger rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">On-hold</h5>
												<span class="badge bg-light rounded-pill">04</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-159</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-success p-1 d-flex rounded-circle me-2"><span
														class="bg-success rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Completed</h5>
												<span class="badge bg-light rounded-pill">10</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-warning badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Medium</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-161</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="pills-low" role="tabpanel">
							<div class="d-flex align-items-start overflow-auto project-status pb-4">
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-pink p-1 d-flex rounded-circle me-2"><span
														class="bg-pink rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">New</h5>
												<span class="badge bg-light rounded-pill">02</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-154</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-155</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-skyblue p-1 d-flex rounded-circle me-2"><span
														class="bg-skyblue rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Inprogress</h5>
												<span class="badge bg-light rounded-pill">13</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-156</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-157</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100 me-3">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-danger p-1 d-flex rounded-circle me-2"><span
														class="bg-danger rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">On-hold</h5>
												<span class="badge bg-light rounded-pill">04</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-159</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
								<div class="p-3 rounded bg-transparent-secondary w-100">
									<div class="bg-white p-2 rounded mb-2">
										<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<span class="bg-soft-success p-1 d-flex rounded-circle me-2"><span
														class="bg-success rounded-circle d-block p-1"></span></span>
												<h5 class="me-2">Completed</h5>
												<span class="badge bg-light rounded-pill">10</span>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-dots-vertical"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1"><i
																class="ti ti-edit me-2"></i>Edit</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1" data-bs-toggle="modal"
															data-bs-target="#delete_modal"><i
																class="ti ti-trash me-2"></i>Delete</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="kanban-drag-wrap">
										<div>
											<div class="card kanban-card mb-2">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-3">
														<div class="d-flex align-items-center">
															<span class="badge bg-outline-dark me-2">Web
																Layout</span>
															<span
																class="badge bg-success badge-xs d-flex align-items-center justify-content-center"><i
																	class="fas fa-circle fs-6 me-1"></i>Low</span>
														</div>
														<div class="dropdown">
															<a href="javascript:void(0);"
																class="d-inline-flex align-items-center"
																data-bs-toggle="dropdown">
																<i class="ti ti-dots-vertical"></i>
															</a>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"><i
																			class="ti ti-edit me-2"></i>Edit</a>
																</li>
																<li>
																	<a href="javascript:void(0);"
																		class="dropdown-item rounded-1"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i
																			class="ti ti-trash me-2"></i>Delete</a>
																</li>
															</ul>
														</div>
													</div>
													<div class="d-flex align-items-center mb-2">
														<span
															class="avatar avatar-xs rounded-circle bg-warning me-2">
															<img src="{{URL::asset('build/img/icons/kanban-arrow.svg')}}"
																class="w-auto h-auto" alt="Img">
														</span>
														<h6 class="d-flex align-items-center">Project Title <span
																class="fs-12 ms-2 text-gray">PRJ-161</span></h6>
													</div>
													<div class="d-flex align-items-center border-bottom mb-3 pb-3">
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Budget</span>
															<p class="fs-12 text-dark">$24,000</p>
														</div>
														<div class="me-3 pe-3 border-end">
															<span class="fw-medium fs-12 d-block mb-1">Tasks</span>
															<p class="fs-12 text-dark">12/15</p>
														</div>
														<div class="">
															<span class="fw-medium fs-12 d-block mb-1">Due on</span>
															<p class="fs-12 text-dark">15 Apr 2024</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="avatar-list-stacked avatar-group-sm me-3">
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																	alt="img">
															</span>
															<span class="avatar avatar-rounded">
																<img class="border border-white"
																	src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
																	alt="img">
															</span>
															<a href="#"
																class="avatar avatar-rounded bg-primary fs-12 text-white">
																1+
															</a>
														</div>
														<div class="d-flex align-items-center">
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark me-2"><i
																	class="ti ti-message-circle text-gray me-1"></i>14</a>
															<a href="javascript:void(0);"
																class="d-flex align-items-center text-dark"><i
																	class="ti ti-paperclip text-gray me-1"></i>14</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="pt-2">
										<a href="#"
											class="btn btn-white border border-dashed d-flex align-items-center justify-content-center">
											<i class="ti ti-plus me-2"></i>
											New Project
										</a>
									</div>
								</div>
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