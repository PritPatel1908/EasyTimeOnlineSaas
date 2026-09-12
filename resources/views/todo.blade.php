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
					<h2 class="mb-1">Todo</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Applications
							</li>
							<li class="breadcrumb-item active" aria-current="page">Todo</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="d-flex align-items-center border rounded p-1 me-2">
						<a href="{{url('todo-list')}}" class="btn btn-icon btn-sm"><i class="ti ti-list-tree"></i></a>
						<a href="{{url('todo')}}" class="btn btn-icon btn-sm active bg-primary text-white"><i
								class="ti ti-table"></i></a>
					</div>
					<div class="">
						<div class="input-icon-start position-relative">
							<span class="input-icon-addon">
								<i class="ti ti-search"></i>
							</span>
							<input type="text" class="form-control" placeholder="Search Todo List">
						</div>
					</div>
					<div class="ms-2 mb-0 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>

			</div>>
			<div class="card">
				<div class="card-body">
					<div class="row gy-3 mb-3">
						<div class="col-sm-4">
							<div class="d-flex align-items-center">
								<h4>Total Todo</h4>
								<span class="badge badge-dark rounded-pill badge-xs ms-2">+1</span>
							</div>
						</div>
						<div class="col-sm-8">
							<div class="d-flex align-items-center justify-content-end">
								<p class="mb-0 me-3 pe-3 border-end fs-14">Total Task : <span class="text-dark"> 55
									</span></p>
								<p class="mb-0 me-3 pe-3 border-end fs-14">Pending : <span class="text-dark"> 15
									</span></p>
								<p class="mb-0 fs-14">Completed : <span class="text-dark"> 40 </span></p>
							</div>
						</div>
					</div>
					<div class="mb-3">
						<button class="btn bg-primary-transparent border-dashed border-primary w-100 text-start"
							data-bs-toggle="modal" data-bs-target="#add_todo">
							<i class="ti ti-plus me-2"></i>New task
						</button>
					</div>
					<div class="row border-bottom mb-3">
						<div class="col-lg-6">
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
						<div class="col-lg-6">
							<div class="d-flex align-items-center justify-content-lg-end flex-wrap row-gap-3 mb-3">
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
										All Tags
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">All
												Tags</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Internal</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Projects</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Meetings</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Reminder</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">Research</a>
										</li>
									</ul>
								</div>
								<div class="d-flex align-items-center">
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
												<a href="javascript:void(0);"
													class="dropdown-item rounded-1">Priority</a>
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
							<div class="accordion todo-accordion" id="accordionExample">
								<div class="accordion-item mb-3">
									<div class="row align-items-center mb-3 row-gap-3">
										<div class="col-lg-4 col-sm-6">
											<div class="accordion-header" id="headingTwo">
												<div class="accordion-button" data-bs-toggle="collapse"
													data-bs-target="#collapseTwo" aria-controls="collapseTwo">
													<div class="d-flex align-items-center w-100">
														<div class="me-2">
															<a href="javascript:void(0);">
																<span><i class="fas fa-chevron-down"></i></span>
															</a>
														</div>
														<div class="d-flex align-items-center">
															<span><i
																	class="ti ti-square-rounded text-purple me-2"></i></span>
															<h5 class="fw-semibold">High</h5>
															<span class="badge bg-light rounded-pill ms-2">15</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-8 col-sm-6">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="#" class="btn btn-light me-2" data-bs-toggle="modal"
													data-bs-target="#add_todo"><i
														class="ti ti-circle-plus me-2"></i>Add New</a>
												<a href="#" class="btn btn-outline-light border">See All <i
														class="ti ti-arrow-right ms-2"></i></a>
											</div>
										</div>
									</div>
									<div id="collapseTwo" class="accordion-collapse collapse show"
										aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
										<div class="accordion-body">
											<div class="list-group list-group-flush border-bottom pb-2">
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 d-flex align-items-center rating-select"><i
																		class="ti ti-star-filled filled"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Finalize project proposal</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>15 Jan
																	2025</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-success me-3">Projects</span>
																<span
																	class="badge bg-soft-pink d-inline-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Onhold</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Submit to supervisor by EOD
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>25 May
																	2024</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-danger me-3">Internal</span>
																<span
																	class="badge bg-transparent-purple d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Inprogress</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-21.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3 todo-strike-content">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox"
																		checked>
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Prepare presentation slides
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>15 Jan
																	2025</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-secondary me-3">Reminder</span>
																<span
																	class="badge badge-secondary-transparent d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Pending</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-25.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="accordion-item mb-3">
									<div class="row align-items-center mb-3 row-gap-3">
										<div class="col-lg-4 col-sm-6">
											<div class="accordion-header" id="headingThree">
												<div class="accordion-button" data-bs-toggle="collapse"
													data-bs-target="#collapseThree" aria-controls="collapseThree">
													<div class="d-flex align-items-center w-100">
														<div class="me-2">
															<a href="javascript:void(0);">
																<span><i class="fas fa-chevron-down"></i></span>
															</a>
														</div>
														<div class="d-flex align-items-center">
															<span><i
																	class="ti ti-square-rounded text-warning me-2"></i></span>
															<h5 class="fw-semibold">Medium</h5>
															<span class="badge bg-light rounded-pill ms-2">05</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-8 col-sm-6">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="#" class="btn btn-light me-2" data-bs-toggle="modal"
													data-bs-target="#add_todo"><i
														class="ti ti-circle-plus me-2"></i>Add New</a>
												<a href="#" class="btn btn-outline-light border">See All <i
														class="ti ti-arrow-right ms-2"></i></a>
											</div>
										</div>
									</div>
									<div id="collapseThree" class="accordion-collapse collapse show"
										aria-labelledby="headingThree" data-bs-parent="#accordionExample">
										<div class="accordion-body">
											<div class="list-group list-group-flush border-bottom pb-2">
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Check and respond to emails
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>Tomorrow</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-secondary me-3">Reminder</span>
																<span
																	class="badge badge-soft-success align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Completed</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Coordinate with department
																		head on progress</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>25 May
																	2024</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-danger me-3">Internal</span>
																<span
																	class="badge bg-transparent-purple d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Inprogress</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="accordion-item mb-3">
									<div class="row align-items-center mb-3 row-gap-3">
										<div class="col-lg-4 col-sm-6">
											<div class="accordion-header" id="headingFour">
												<div class="accordion-button" data-bs-toggle="collapse"
													data-bs-target="#collapseFour" aria-controls="collapseFour">
													<div class="d-flex align-items-center w-100">
														<div class="me-2">
															<a href="javascript:void(0);">
																<span><i class="fas fa-chevron-down"></i></span>
															</a>
														</div>
														<div class="d-flex align-items-center">
															<span><i
																	class="ti ti-square-rounded text-success me-2"></i></span>
															<h5 class="fw-semibold">Low</h5>
															<span class="badge bg-light rounded-pill ms-2">24</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-8 col-sm-6">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="#" class="btn btn-light me-2" data-bs-toggle="modal"
													data-bs-target="#add_todo"><i
														class="ti ti-circle-plus me-2"></i>Add New</a>
												<a href="#" class="btn btn-outline-light border">See All <i
														class="ti ti-arrow-right ms-2"></i></a>
											</div>
										</div>
									</div>
									<div id="collapseFour" class="accordion-collapse collapse show"
										aria-labelledby="headingFour" data-bs-parent="#accordionExample">
										<div class="accordion-body">
											<div class="list-group list-group-flush">
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Plan tasks for the next day
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>Today</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span class="badge badge-info me-3">Social</span>
																<span
																	class="badge badge-soft-secondary d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Pending</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="pills-contact" role="tabpanel">
							<div class="accordion todo-accordion">
								<div class="accordion-item mb-3">
									<div class="row align-items-center mb-3 row-gap-3">
										<div class="col-lg-4 col-sm-6">
											<div class="accordion-header" id="headingSix">
												<div class="accordion-button" data-bs-toggle="collapse"
													data-bs-target="#collapseSix" aria-controls="collapseSix">
													<div class="d-flex align-items-center w-100">
														<div class="me-2">
															<a href="javascript:void(0);">
																<span><i class="fas fa-chevron-down"></i></span>
															</a>
														</div>
														<div class="d-flex align-items-center">
															<span><i
																	class="ti ti-square-rounded text-purple me-2"></i></span>
															<h5 class="fw-semibold">High</h5>
															<span class="badge bg-light rounded-pill ms-2">15</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-8 col-sm-6">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="#" class="btn btn-light me-2" data-bs-toggle="modal"
													data-bs-target="#add_todo"><i
														class="ti ti-circle-plus me-2"></i>Add New</a>
												<a href="#" class="btn btn-outline-light border">See All <i
														class="ti ti-arrow-right ms-2"></i></a>
											</div>
										</div>
									</div>
									<div id="collapseSix" class="accordion-collapse collapse show"
										aria-labelledby="headingSix">
										<div class="accordion-body">
											<div class="list-group list-group-flush">
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star-filled filled"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Finalize project proposal</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>15 Jan
																	2025</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-success me-3">Projects</span>
																<span
																	class="badge bg-soft-pink d-inline-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Onhold</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Submit to supervisor by EOD
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>25 May
																	2024</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-danger me-3">Internal</span>
																<span
																	class="badge bg-transparent-purple d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Inprogress</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-21.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3 todo-strike-content">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox"
																		checked>
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Prepare presentation slides
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>15 Jan
																	2025</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-secondary me-3">Reminder</span>
																<span
																	class="badge badge-secondary-transparent d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Pending</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-25.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="pills-medium" role="tabpanel">
							<div class="accordion todo-accordion">
								<div class="accordion-item mb-3">
									<div class="row align-items-center mb-3 row-gap-3">
										<div class="col-lg-4 col-sm-6">
											<div class="accordion-header" id="headingSeven">
												<div class="accordion-button" data-bs-toggle="collapse"
													data-bs-target="#collapseSeven" aria-controls="collapseSeven">
													<div class="d-flex align-items-center w-100">
														<div class="me-2">
															<a href="javascript:void(0);">
																<span><i class="fas fa-chevron-down"></i></span>
															</a>
														</div>
														<div class="d-flex align-items-center">
															<span><i
																	class="ti ti-square-rounded text-warning me-2"></i></span>
															<h5 class="fw-semibold">Medium</h5>
															<span class="badge bg-light rounded-pill ms-2">05</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-8 col-sm-6">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="#" class="btn btn-light me-2" data-bs-toggle="modal"
													data-bs-target="#add_todo"><i
														class="ti ti-circle-plus me-2"></i>Add New</a>
												<a href="#" class="btn btn-outline-light border">See All <i
														class="ti ti-arrow-right ms-2"></i></a>
											</div>
										</div>
									</div>
									<div id="collapseSeven" class="accordion-collapse collapse show"
										aria-labelledby="headingSeven">
										<div class="accordion-body">
											<div class="list-group list-group-flush">
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Check and respond to emails
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>Tomorrow</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-secondary me-3">Reminder</span>
																<span
																	class="badge badge-soft-success align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Completed</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Coordinate with department
																		head on progress</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>25 May
																	2024</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span
																	class="badge badge-danger me-3">Internal</span>
																<span
																	class="badge bg-transparent-purple d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Inprogress</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="pills-low" role="tabpanel">
							<div class="accordion todo-accordion">
								<div class="accordion-item mb-3">
									<div class="row align-items-center mb-3 row-gap-3">
										<div class="col-lg-4 col-sm-6">
											<div class="accordion-header" id="headingEight">
												<div class="accordion-button" data-bs-toggle="collapse"
													data-bs-target="#collapseEight" aria-controls="collapseEight">
													<div class="d-flex align-items-center w-100">
														<div class="me-2">
															<a href="javascript:void(0);">
																<span><i class="fas fa-chevron-down"></i></span>
															</a>
														</div>
														<div class="d-flex align-items-center">
															<span><i
																	class="ti ti-square-rounded text-success me-2"></i></span>
															<h5 class="fw-semibold">Low</h5>
															<span class="badge bg-light rounded-pill ms-2">24</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-8 col-sm-6">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="#" class="btn btn-light me-2" data-bs-toggle="modal"
													data-bs-target="#add_todo"><i
														class="ti ti-circle-plus me-2"></i>Add New</a>
												<a href="#" class="btn btn-outline-light border">See All <i
														class="ti ti-arrow-right ms-2"></i></a>
											</div>
										</div>
									</div>
									<div id="collapseEight" class="accordion-collapse collapse show"
										aria-labelledby="headingEight">
										<div class="accordion-body">
											<div class="list-group list-group-flush">
												<div
													class="list-group-item list-item-hover shadow-sm rounded mb-2 p-3">
													<div class="row align-items-center row-gap-3">
														<div class="col-lg-6 col-md-7">
															<div
																class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																<span class="me-2 d-flex align-items-center"><i
																		class="ti ti-grid-dots text-dark"></i></span>
																<div class="form-check form-check-md me-2">
																	<input class="form-check-input" type="checkbox">
																</div>
																<span
																	class="me-2 rating-select d-flex align-items-center"><i
																		class="ti ti-star"></i></span>
																<div class="strike-info">
																	<h4 class="fs-14">Plan tasks for the next day
																	</h4>
																</div>
																<span
																	class="badge bg-transparent-dark text-dark rounded-pill ms-2"><i
																		class="ti ti-calendar me-1"></i>Today</span>
															</div>
														</div>
														<div class="col-lg-6 col-md-5">
															<div
																class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
																<span class="badge badge-info me-3">Social</span>
																<span
																	class="badge badge-soft-secondary d-flex align-items-center me-3"><i
																		class="fas fa-circle fs-6 me-1"></i>Pending</span>
																<div class="d-flex align-items-center">
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																	<div class="dropdown ms-2">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#add_todo"><i
																						class="ti ti-edit me-2"></i>Edit</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#delete_modal"><i
																						class="ti ti-trash me-2"></i>Delete</a>
																			</li>
																			<li>
																				<a href="javascript:void(0);"
																					class="dropdown-item rounded-1"
																					data-bs-toggle="modal"
																					data-bs-target="#view_todo"><i
																						class="ti ti-eye me-2"></i>View</a>
																			</li>
																		</ul>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="text-center">
						<a href="#" class="btn btn-primary"><i class="ti ti-loader me-2"></i>Load More</a>
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