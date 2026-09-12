@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">

			<div class="row align-items-center mb-4">
				<div class="d-md-flex d-sm-block justify-content-between align-items-center flex-wrap">
					<h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0"><a href="{{url('projects')}}">
							<i class="ti ti-arrow-left me-2"></i>Back to List</a>
					</h6>
					<div class="d-flex">
						<div class="text-end">
							<a href="#" class="btn btn-primary" data-bs-toggle="modal"
								data-bs-target="#edit_project"><i class="ti ti-edit me-1"></i>Edit Project</a>
						</div>
						<div class="head-icons ms-2 text-end">
							<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
								data-bs-original-title="Collapse" id="collapse-header">
								<i class="ti ti-chevrons-up"></i>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xxl-3 col-xl-4 theiaStickySidebar">
					<div class="card">
						<div class="card-body">
							<h5 class="mb-3">Project Details</h5>
							<div class="list-group details-list-group mb-4">
								<div class="list-group-item">
									<span>Client</span>
									<p class="text-gray-9">EcoVision Enterprises</p>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Project Total Cost</span>
										<p class="text-gray-9">$1400</p>
									</div>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Hours of Work</span>
										<p class="text-gray-9">150 hrs</p>
									</div>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Created on</span>
										<p class="text-gray-9">14 Nov 2026</p>
									</div>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Started on</span>
										<p class="text-gray-9">15 Jan 2026</p>
									</div>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Due Date</span>
										<div class="d-flex align-items-center">
											<p class="text-gray-9 mb-0">15 Nov 2026</p>
											<span
												class="badge badge-danger d-inline-flex align-items-center ms-2"><i
													class="ti ti-clock-stop"></i>1</span>
										</div>

									</div>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Created by</span>
										<div class="d-flex align-items-center">
											<span class="avatar avatar-sm avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}" alt="Img">
											</span>
											<p class="text-gray-9 mb-0">Cameron</p>
										</div>

									</div>
								</div>
								<div class="list-group-item">
									<div class="d-flex align-items-center justify-content-between">
										<span>Priority</span>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												<span
													class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
														class="ti ti-point-filled text-danger"></i></span> High
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-danger d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-danger"></i></span>High</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-warning d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-warning"></i></span>Medium</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1 d-flex justify-content-start align-items-center"><span
															class="rounded-circle bg-transparent-success d-flex justify-content-center align-items-center me-2"><i
																class="ti ti-point-filled text-success"></i></span>Low</a>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<h5 class="mb-3">Tasks Details</h5>
							<div class="bg-light p-2 rounded">
								<span class="d-block mb-1">Tasks Done</span>
								<h4 class="mb-2">0 / 0</h4>
								<div class="progress progress-xs mb-2">
									<div class="progress-bar" role="progressbar"></div>
								</div>
								<p>0% Completed</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-9 col-xl-8">
					<div class="card">
						<div class="card-body">
							<div class="bg-light rounded p-3 mb-3">
								<div class="d-flex align-items-center">
									<a href="{{url('project-details')}}" class="flex-shrink-0 me-2">
										<img src="{{URL::asset('build/img/social/project-01.svg')}}" alt="Img">
									</a>
									<div>
										<h6 class="mb-1"><a href="{{url('project-details')}}">Hospital Administration</a>
										</h6>
										<p>Project ID : <span class="text-primary"> PRO-0004</span></p>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-sm-3">
									<p class="d-flex align-items-center mb-3"><i
											class="ti ti-square-rounded me-2"></i>Status</p>
								</div>
								<div class="col-sm-9">
									<span class="badge badge-soft-purple d-inline-flex align-items-center mb-3"><i
											class="ti ti-point-filled me-1"></i>InProgress</span>
								</div>
								<div class="col-sm-3">
									<p class="d-flex align-items-center mb-3"><i
											class="ti ti-users-group me-2"></i>Team</p>
								</div>
								<div class="col-sm-9">
									<div class="d-flex align-items-center mb-3">
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Lewis</a></h6>
										</div>
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Leona</a></h6>
										</div>
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-33.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Pineiro</a></h6>
										</div>
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-37.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Moseley</a></h6>
										</div>
										<div>
											<a href="#" class="d-flex align-items-center fs-12"><i
													class="ti ti-circle-plus me-1"></i>Add New</a>
										</div>
									</div>
								</div>
								<div class="col-sm-3">
									<p class="d-flex align-items-center mb-3"><i
											class="ti ti-user-shield me-2"></i>Team Lead</p>
								</div>
								<div class="col-sm-9">
									<div class="d-flex align-items-center mb-3">
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-42.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Ruth</a></h6>
										</div>
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-44.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Meredith</a></h6>
										</div>
										<div>
											<a href="#" class="d-flex align-items-center fs-12"><i
													class="ti ti-circle-plus me-1"></i>Add New</a>
										</div>
									</div>
								</div>
								<div class="col-sm-3">
									<p class="d-flex align-items-center mb-3"><i
											class="ti ti-user-star me-2"></i>Project Manager</p>
								</div>
								<div class="col-sm-9">
									<div class="d-flex align-items-center mb-3">
										<div class="bg-gray-100 p-1 rounded d-flex align-items-center me-2">
											<a href="#"
												class="avatar avatar-sm avatar-rounded border border-white flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-45.jpg')}}" alt="Img">
											</a>
											<h6 class="fs-12"><a href="#">Dwight</a></h6>
										</div>
										<div>
											<a href="#" class="d-flex align-items-center fs-12"><i
													class="ti ti-circle-plus me-1"></i>Add New</a>
										</div>
									</div>
								</div>
								<div class="col-sm-3">
									<p class="d-flex align-items-center mb-3"><i
											class="ti ti-bookmark me-2"></i>Tags</p>
								</div>
								<div class="col-sm-9">
									<div class="d-flex align-items-center mb-3">
										<a href="#" class="badge task-tag bg-pink rounded-pill me-2">Admin Panel</a>
										<a href="#" class="badge task-tag badge-info rounded-pill">High Tech</a>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="mb-3">
										<h6 class="mb-1">Description</h6>
										<p>The Enhanced Patient Management System (EPMS) project aims to modernize
											and streamline
											the patient management processes within. By integrating advanced
											technologies and optimizing existing
											workflows, the project seeks to improve patient care, enhance
											operational
											efficiency, and ensure compliance with regulatory standards.
										</p>
									</div>
								</div>
								<div class="col-md-12">
									<div
										class="bg-soft-secondary p-3 rounded d-flex align-items-center justify-content-between">
										<p class="text-secondary mb-0">Time Spent on this project</p>
										<h3 class="text-secondary">65/120 <span class="fs-16">Hrs</span></h3>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="custom-accordion-items">
						<div class="accordion accordions-items-seperate" id="accordionExample">
							<div class="accordion-item">
								<div class="accordion-header" id="headingTwo">
									<div class="accordion-button">
										<h5>Tasks</h5>
										<div class=" ms-auto">
											<a href="#" class="d-flex align-items-center collapsed collapse-arrow"
												data-bs-toggle="collapse" data-bs-target="#primaryBorderTwo"
												aria-expanded="false" aria-controls="primaryBorderTwo">
												<i class="ti ti-chevron-down fs-18"></i>
											</a>
										</div>
									</div>
								</div>
								<div id="primaryBorderTwo" class="accordion-collapse collapse show border-top"
									aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
									<div class="accordion-body">
										<div class="list-group list-group-flush">
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center row-gap-3">
													<div class="col-md-7">
														<div
															class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
															<span><i class="ti ti-grid-dots me-2"></i></span>
															<div class="form-check form-check-md me-2">
																<input class="form-check-input" type="checkbox">
															</div>
															<span
																class="me-2 d-flex align-items-center rating-select"><i
																	class="ti ti-star-filled filled"></i></span>
															<div class="strike-info">
																<h4 class="fs-14">Patient appointment booking</h4>
															</div>
														</div>
													</div>
													<div class="col-md-5">
														<div
															class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
															<span
																class="badge bg-soft-pink d-inline-flex align-items-center me-3"><i
																	class="fas fa-circle fs-6 me-1"></i>Onhold</span>
															<div class="d-flex align-items-center">
																<div class="avatar-list-stacked avatar-group-sm">
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
																	<ul class="dropdown-menu dropdown-menu-end p-3">
																		<li>
																			<a href="javascript:void(0);"
																				class="dropdown-item rounded-1"
																				data-bs-toggle="modal"
																				data-bs-target="#edit_todo"><i
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
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center row-gap-3">
													<div class="col-md-7">
														<div
															class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
															<span><i class="ti ti-grid-dots me-2"></i></span>
															<div class="form-check form-check-md me-2">
																<input class="form-check-input" type="checkbox">
															</div>
															<span
																class="me-2 rating-select d-flex align-items-center"><i
																	class="ti ti-star"></i></span>
															<div class="strike-info">
																<h4 class="fs-14">Appointment booking with payment
																	gateway</h4>
															</div>
														</div>
													</div>
													<div class="col-md-5">
														<div
															class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
															<span
																class="badge bg-transparent-purple d-flex align-items-center me-3"><i
																	class="fas fa-circle fs-6 me-1"></i>Inprogress</span>
															<div class="d-flex align-items-center">
																<div class="avatar-list-stacked avatar-group-sm">
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
																</div>
																<div class="dropdown ms-2">
																	<a href="javascript:void(0);"
																		class="d-inline-flex align-items-center"
																		data-bs-toggle="dropdown">
																		<i class="ti ti-dots-vertical"></i>
																	</a>
																	<ul class="dropdown-menu dropdown-menu-end p-3">
																		<li>
																			<a href="javascript:void(0);"
																				class="dropdown-item rounded-1"
																				data-bs-toggle="modal"
																				data-bs-target="#edit_todo"><i
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
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center row-gap-3">
													<div class="col-md-7">
														<div
															class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
															<span><i class="ti ti-grid-dots me-2"></i></span>
															<div class="form-check form-check-md me-2">
																<input class="form-check-input" type="checkbox">
															</div>
															<span
																class="me-2 rating-select d-flex align-items-center"><i
																	class="ti ti-star"></i></span>
															<div class="strike-info">
																<h4 class="fs-14">Patient and Doctor video
																	conferencing</h4>
															</div>
														</div>
													</div>
													<div class="col-md-5">
														<div
															class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
															<span
																class="badge badge-soft-success align-items-center me-3"><i
																	class="fas fa-circle fs-6 me-1"></i>Completed</span>
															<div class="d-flex align-items-center">
																<div class="avatar-list-stacked avatar-group-sm">
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
																	<ul class="dropdown-menu dropdown-menu-end p-3">
																		<li>
																			<a href="javascript:void(0);"
																				class="dropdown-item rounded-1"
																				data-bs-toggle="modal"
																				data-bs-target="#edit_todo"><i
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
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center row-gap-3">
													<div class="col-md-7">
														<div
															class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3 todo-strike-content">
															<span><i class="ti ti-grid-dots me-2"></i></span>
															<div class="form-check form-check-md me-2">
																<input class="form-check-input" type="checkbox"
																	checked="">
															</div>
															<span
																class="me-2 rating-select d-flex align-items-center"><i
																	class="ti ti-star"></i></span>
															<div class="strike-info">
																<h4 class="fs-14">Private chat module</h4>
															</div>
														</div>
													</div>
													<div class="col-md-5">
														<div
															class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
															<span
																class="badge badge-secondary-transparent d-flex align-items-center me-3"><i
																	class="fas fa-circle fs-6 me-1"></i>Pending</span>
															<div class="d-flex align-items-center">
																<div class="avatar-list-stacked avatar-group-sm">
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
																	<ul class="dropdown-menu dropdown-menu-end p-3">
																		<li>
																			<a href="javascript:void(0);"
																				class="dropdown-item rounded-1"
																				data-bs-toggle="modal"
																				data-bs-target="#edit_todo"><i
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
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center row-gap-3">
													<div class="col-md-7">
														<div
															class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
															<span><i class="ti ti-grid-dots me-2"></i></span>
															<div class="form-check form-check-md me-2">
																<input class="form-check-input" type="checkbox">
															</div>
															<span
																class="me-2 rating-select d-flex align-items-center"><i
																	class="ti ti-star"></i></span>
															<div class="strike-info">
																<h4 class="fs-14">Go-Live and Post-Implementation
																	Support</h4>
															</div>
														</div>
													</div>
													<div class="col-md-5">
														<div
															class="d-flex align-items-center justify-content-md-end flex-wrap row-gap-3">
															<span
																class="badge bg-transparent-purple d-flex align-items-center me-3"><i
																	class="fas fa-circle fs-6 me-1"></i>Inprogress</span>
															<div class="d-flex align-items-center">
																<div class="avatar-list-stacked avatar-group-sm">
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
																</div>
																<div class="dropdown ms-2">
																	<a href="javascript:void(0);"
																		class="d-inline-flex align-items-center"
																		data-bs-toggle="dropdown">
																		<i class="ti ti-dots-vertical"></i>
																	</a>
																	<ul class="dropdown-menu dropdown-menu-end p-3">
																		<li>
																			<a href="javascript:void(0);"
																				class="dropdown-item rounded-1"
																				data-bs-toggle="modal"
																				data-bs-target="#edit_todo"><i
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
											<button
												class="btn bg-primary-transparent border-dashed border-primary w-100 text-start"
												data-bs-toggle="modal" data-bs-target="#add_todo">
												<i class="ti ti-plus me-2"></i>New task
											</button>
										</div>
									</div>
								</div>
							</div>
							<div class="accordion-item ">
								<div class="accordion-header" id="headingThree">
									<div class="accordion-button">
										<div class="d-flex align-items-center flex-fill">
											<h5>Images</h5>
											<div class=" ms-auto d-flex align-items-center">
												<a href="#"
													class="btn btn-primary btn-xs d-inline-flex align-items-center me-3"><i
														class="ti ti-square-rounded-plus-filled me-1"></i>Add
													New</a>
												<a href="#"
													class="d-flex align-items-center collapsed collapse-arrow"
													data-bs-toggle="collapse" data-bs-target="#primaryBorderThree"
													aria-expanded="false" aria-controls="primaryBorderThree">
													<i class="ti ti-chevron-down fs-18"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
								<div id="primaryBorderThree" class="accordion-collapse collapse show border-top"
									aria-labelledby="headingThree" data-bs-parent="#accordionExample">
									<div class="accordion-body">
										<div class="swiper media-images-slider">
											<div class="swiper-wrapper">
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-08.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-08.jpg')}}" class=" rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-09.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-09.jpg')}}" class="rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-10.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-10.jpg')}}" class="rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-11.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-11.jpg')}}" class="rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-12.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-12.jpg')}}" class="rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-13.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-13.jpg')}}" class="rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											<div class="swiper-slide">
												<a href="{{URL::asset('build/img/media/media-big-14.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
												<img src="{{URL::asset('build/img/media/media-14.jpg')}}" class="rounded" alt="img">
												<div class="d-flex align-items-center hover-links">
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-maximize"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-link"></i></span>
													<span class="avatar avatar-md avatar-rounded"><i
															class="ti ti-trash"></i></span>
												</div>
											</a>
											</div>
											</div>
											<div class="swiper-button-next"></div>
											<div class="swiper-button-prev"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="accordion-item">
								<div class="accordion-header" id="headingFour">
									<div class="accordion-button">
										<div class="d-flex align-items-center flex-fill">
											<h5>Files</h5>
											<div class=" ms-auto d-flex align-items-center">
												<a href="#"
													class="btn btn-primary btn-xs d-inline-flex align-items-center me-3"><i
														class="ti ti-square-rounded-plus-filled me-1"></i>Add
													New</a>
												<a href="#"
													class="d-flex align-items-center collapsed collapse-arrow"
													data-bs-toggle="collapse" data-bs-target="#primaryBorderFour"
													aria-expanded="false" aria-controls="primaryBorderFour">
													<i class="ti ti-chevron-down fs-18"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
								<div id="primaryBorderFour" class="accordion-collapse collapse show border-top"
									aria-labelledby="headingFour">
									<div class="accordion-body">
										<div class="swiper files-carousel">
											<div class="swiper-wrapper">
											<div class="swiper-slide card shadow-none mb-0">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
														<div class="d-flex align-items-center">
															<a href="#" class="avatar avatar-md bg-light me-2">
																<img src="{{URL::asset('build/img/icons/file-02.svg')}}"
																	class="w-auto h-auto" alt="img">
															</a>
															<div>
																<h6 class="mb-1">Project_1.docx</h6>
																<span>7.6 MB</span>
															</div>
														</div>
														<div class="d-flex align-items-center">
															<a href="#" class="btn btn-sm btn-icon"><i
																	class="ti ti-download"></i></a>
															<a href="#" class="btn btn-sm btn-icon"><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<p class="fw-medium mb-0">15 May 2024, 6:53 PM</p>
														<span class="avatar avatar-sm avatar-rounded"><img
																src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																alt="Img"></span>
													</div>
												</div>
											</div>
											<div class="swiper-slide card shadow-none mb-0">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
														<div class="d-flex align-items-center">
															<a href="#" class="avatar avatar-md bg-light me-2">
																<img src="{{URL::asset('build/img/icons/file-01.svg')}}"
																	class="w-auto h-auto" alt="img">
															</a>
															<div>
																<h6 class="mb-1">Proposal.pdf</h6>
																<span>12.6 MB</span>
															</div>
														</div>
														<div class="d-flex align-items-center">
															<a href="#" class="btn btn-sm btn-icon"><i
																	class="ti ti-download"></i></a>
															<a href="#" class="btn btn-sm btn-icon"><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<p class="fw-medium mb-0">15 May 2024, 6:53 PM</p>
														<span class="avatar avatar-sm avatar-rounded"><img
																src="{{URL::asset('build/img/users/user-19.jpg')}}" alt="Img"></span>
													</div>
												</div>
											</div>
											<div class="swiper-slide card shadow-none mb-0">
												<div class="card-body">
													<div
														class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
														<div class="d-flex align-items-center">
															<a href="#" class="avatar avatar-md bg-light me-2">
																<img src="{{URL::asset('build/img/icons/file-04.svg')}}"
																	class="w-auto h-auto" alt="img">
															</a>
															<div>
																<h6 class="mb-1">Logo-Img.zip</h6>
																<span>6.2 MB</span>
															</div>
														</div>
														<div class="d-flex align-items-center">
															<a href="#" class="btn btn-sm btn-icon"><i
																	class="ti ti-download"></i></a>
															<a href="#" class="btn btn-sm btn-icon"><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<p class="fw-medium mb-0">15 May 2024, 6:53 PM</p>
														<span class="avatar avatar-sm avatar-rounded"><img
																src="{{URL::asset('build/img/users/user-20.jpg')}}" alt="Img"></span>
													</div>
												</div>
											</div>
											</div>
											<div class="swiper-button-next"></div>
											<div class="swiper-button-prev"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xl-6 d-flex">
									<div class="accordion-item flex-fill">
										<div class="accordion-header" id="headingFive">
											<div class="accordion-button">
												<div class="d-flex align-items-center flex-fill">
													<h5>Notes</h5>
													<div class=" ms-auto d-flex align-items-center">
														<a href="#"
															class="btn btn-primary btn-xs d-inline-flex align-items-center me-3"><i
																class="ti ti-square-rounded-plus-filled me-1"></i>Add
															New</a>
														<a href="#"
															class="d-flex align-items-center collapsed collapse-arrow"
															data-bs-toggle="collapse"
															data-bs-target="#primaryBorderFive"
															aria-expanded="false" aria-controls="primaryBorderFive">
															<i class="ti ti-chevron-down fs-18"></i>
														</a>
													</div>
												</div>
											</div>
										</div>
										<div id="primaryBorderFive"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingFive">
											<div class="accordion-body">
												<div class="card">
													<div class="card-body">
														<div
															class="d-flex align-items-center justify-content-between mb-2">
															<h6 class="text-gray-5 fw-medium">15 May 2025</h6>
															<div class="dropdown">
																<a href="javascript:void(0);"
																	class="d-inline-flex align-items-center"
																	data-bs-toggle="dropdown" aria-expanded="false">
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
																			class="dropdown-item rounded-1"><i
																				class="ti ti-trash me-1"></i>Delete</a>
																	</li>
																</ul>
															</div>
														</div>
														<h6 class="d-flex align-items-center mb-2"><i
																class="ti ti-point-filled text-primary me-1"></i>Changes
															&amp; design</h6>
														<p class="text-truncate line-clamb-3">An office management
															app project streamlines administrative tasks by
															integrating
															tools for scheduling, communication, and
															task management, enhancing overall productivity and
															efficiency.
														</p>
													</div>
												</div>
												<div class="card mb-0">
													<div class="card-body">
														<div
															class="d-flex align-items-center justify-content-between mb-2">
															<h6 class="text-gray-5 fw-medium">15 May 2025</h6>
															<div class="dropdown">
																<a href="javascript:void(0);"
																	class="d-inline-flex align-items-center"
																	data-bs-toggle="dropdown" aria-expanded="false">
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
																			class="dropdown-item rounded-1"><i
																				class="ti ti-trash me-1"></i>Delete</a>
																	</li>
																</ul>
															</div>
														</div>
														<h6 class="d-flex align-items-center mb-2"><i
																class="ti ti-point-filled text-primary me-1"></i>Changes
															&amp; design</h6>
														<p class="text-truncate line-clamb-3">An office management
															app project streamlines administrative tasks by
															integrating
															tools for scheduling, communication, and
															task management, enhancing overall productivity and
															efficiency.
														</p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 d-flex">
									<div class="accordion-item flex-fill">
										<div class="accordion-header" id="headingSix">
											<div class="accordion-button">
												<div class="d-flex align-items-center flex-fill">
													<h5>Activity</h5>
													<div class=" ms-auto d-flex align-items-center">
														<a href="#"
															class="btn btn-primary btn-xs d-inline-flex align-items-center me-3"><i
																class="ti ti-square-rounded-plus-filled me-1"></i>Add
															New</a>
														<a href="#"
															class="d-flex align-items-center collapsed collapse-arrow"
															data-bs-toggle="collapse"
															data-bs-target="#primaryBorderSix" aria-expanded="false"
															aria-controls="primaryBorderSix">
															<i class="ti ti-chevron-down fs-18"></i>
														</a>
													</div>
												</div>
											</div>
										</div>
										<div id="primaryBorderSix"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingSix">
											<div class="accordion-body">
												<div class="notice-widget">
													<div
														class="d-flex align-items-center justify-content-between mb-4">
														<div class="d-flex overflow-hidden">
															<span
																class="bg-info avatar avatar-md me-3 rounded-circle flex-shrink-0">
																<i class="ti ti-checkup-list fs-16"></i>
															</span>
															<div class="overflow-hidden">
																<p class="text-truncate mb-1"><span
																		class="text-gray-9 fw-medium">Andrew
																	</span>added a New Task</p>
																<p class="mb-1">15 May 2024, 6:53 PM</p>
															</div>
														</div>
													</div>
													<div
														class="d-flex align-items-center justify-content-between mb-4">
														<div class="d-flex overflow-hidden me-2">
															<span
																class="bg-warning avatar avatar-md me-3 rounded-circle flex-shrink-0">
																<i class="ti ti-circle-dot fs-16"></i>
															</span>
															<div class="overflow-hidden">
																<p class="text-truncate mb-1"><span
																		class="text-gray-9 fw-medium">Jermai
																	</span>Moved task <span
																		class="text-gray-9 fw-medium"> “Private chat
																		module”</span></p>
																<p class="mb-1">15 May 2024, 6:53 PM</p>
																<div class="d-flex align-items-center">
																	<span class="badge badge-success me-2"><i
																			class="ti ti-point-filled me-1"></i>Completed</span>
																	<span><i
																			class="ti ti-arrows-left-right me-2"></i></span>
																	<span class="badge badge-purple"><i
																			class="ti ti-point-filled me-1"></i>Inprogress</span>
																</div>
															</div>
														</div>
													</div>
													<div
														class="d-flex align-items-center justify-content-between mb-4">
														<div class="d-flex overflow-hidden me-2">
															<span
																class="bg-purple avatar avatar-md me-3 rounded-circle flex-shrink-0">
																<i class="ti ti-checkup-list fs-16"></i>
															</span>
															<div class="overflow-hidden">
																<p class="text-truncate mb-1"><span
																		class="text-gray-9 fw-medium">Jermai
																	</span>Created task <span
																		class="text-gray-9 fw-medium"> “Private chat
																		module”</span></p>
																<p class="mb-1">15 May 2024, 6:53 PM</p>
															</div>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-between">
														<div class="d-flex overflow-hidden">
															<span
																class="bg-secondary avatar avatar-md me-3 rounded-circle flex-shrink-0">
																<i class="ti ti-photo fs-16"></i>
															</span>
															<div class="overflow-hidden">
																<p class="text-truncate mb-1"><span
																		class="text-gray-9 fw-medium">Hendry </span>
																	Updated Image <span
																		class="text-gray-9 fw-medium"> “logo.jpg”
																	</span></p>
																<p class="mb-1">15 May 2024, 6:53 PM</p>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="accordion-item">
								<div class="accordion-header" id="headingSeven">
									<div class="accordion-button">
										<h5>Invoices</h5>
										<div class=" ms-auto d-flex align-items-center">
											<a href="#" class="d-flex align-items-center collapsed collapse-arrow"
												data-bs-toggle="collapse" data-bs-target="#primaryBorderSeven"
												aria-expanded="false" aria-controls="primaryBorderSeven">
												<i class="ti ti-chevron-down fs-18"></i>
											</a>
										</div>
									</div>
								</div>
								<div id="primaryBorderSeven" class="accordion-collapse collapse show border-top"
									aria-labelledby="headingSeven" data-bs-parent="#accordionExample">
									<div class="accordion-body">
										<div class="list-group list-group-flush">
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center g-3">
													<div class="col-sm-6">
														<div class="d-flex align-items-center">
															<span
																class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																	class="ti ti-file-invoice text-dark fs-24"></i></span>
															<div>
																<h6 class="fw-medium mb-1">Phase 2 Completion</h6>
																<p><a href="#" class="text-info">#INV-123 </a> 11
																	Sep 2025, 05:35 pm</p>
															</div>
														</div>
													</div>
													<div class="col-sm-3">
														<div>
															<span>Amount</span>
															<p class="text-dark">$6,598</p>
														</div>
													</div>
													<div class="col-sm-3">
														<div
															class="d-flex align-items-center justify-content-sm-end">
															<span
																class="badge badge-soft-success d-inline-flex  align-items-center me-4"><i
																	class="ti ti-point-filled me-1"></i>Paid</span>
															<a href="#" class="btn btn-icon btn-sm"><i
																	class="ti ti-edit"></i></a>
															<a href="#" class="btn btn-icon btn-sm "><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
												</div>
											</div>
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center g-3">
													<div class="col-sm-6">
														<div class="d-flex align-items-center">
															<span
																class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																	class="ti ti-file-invoice text-dark fs-24"></i></span>
															<div>
																<h6 class="fw-medium mb-1">Advance for Project</h6>
																<p><a href="#" class="text-info">#INV-124 </a> 14
																	Sep 2025, 05:35 pm</p>
															</div>
														</div>
													</div>
													<div class="col-sm-3">
														<div>
															<span>Amount</span>
															<p class="text-dark">$3312</p>
														</div>
													</div>
													<div class="col-sm-3">
														<div
															class="d-flex align-items-center justify-content-sm-end">
															<span
																class="badge badge-soft-success d-inline-flex  align-items-center me-4"><i
																	class="ti ti-point-filled me-1"></i>Hold</span>
															<a href="#" class="btn btn-icon btn-sm"><i
																	class="ti ti-edit"></i></a>
															<a href="#" class="btn btn-icon btn-sm "><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
												</div>
											</div>
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center g-3">
													<div class="col-sm-6">
														<div class="d-flex align-items-center">
															<span
																class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																	class="ti ti-file-invoice text-dark fs-24"></i></span>
															<div>
																<h6 class="fw-medium mb-1">Changes & design
																	Alignments</h6>
																<p><a href="#" class="text-info">#INV-125 </a> 15
																	Sep 2025, 05:35 pm</p>
															</div>
														</div>
													</div>
													<div class="col-sm-3">
														<div>
															<span>Amount</span>
															<p class="text-dark">$4154</p>
														</div>
													</div>
													<div class="col-sm-3">
														<div
															class="d-flex align-items-center justify-content-sm-end">
															<span
																class="badge badge-soft-success d-inline-flex  align-items-center me-4"><i
																	class="ti ti-point-filled me-1"></i>Paid</span>
															<a href="#" class="btn btn-icon btn-sm"><i
																	class="ti ti-edit"></i></a>
															<a href="#" class="btn btn-icon btn-sm "><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
												</div>
											</div>
											<div class="list-group-item border rounded mb-2 p-2">
												<div class="row align-items-center g-3">
													<div class="col-sm-6">
														<div class="d-flex align-items-center">
															<span
																class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																	class="ti ti-file-invoice text-dark fs-24"></i></span>
															<div>
																<h6 class="fw-medium mb-1">Added New Functionality
																</h6>
																<p><a href="#" class="text-info">#INV-126 </a> 16
																	Sep 2025, 05:35 pm</p>
															</div>
														</div>
													</div>
													<div class="col-sm-3">
														<div>
															<span>Amount</span>
															<p class="text-dark">$658</p>
														</div>
													</div>
													<div class="col-sm-3">
														<div
															class="d-flex align-items-center justify-content-sm-end">
															<span
																class="badge badge-soft-success d-inline-flex  align-items-center me-4"><i
																	class="ti ti-point-filled me-1"></i>Paid</span>
															<a href="#" class="btn btn-icon btn-sm"><i
																	class="ti ti-edit"></i></a>
															<a href="#" class="btn btn-icon btn-sm "><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
												</div>
											</div>
											<div class="list-group-item border rounded p-2">
												<div class="row align-items-center g-3">
													<div class="col-sm-6">
														<div class="d-flex align-items-center">
															<span
																class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																	class="ti ti-file-invoice text-dark fs-24"></i></span>
															<div>
																<h6 class="fw-medium mb-1">Phase 1 Completion</h6>
																<p><a href="#" class="text-info">#INV-127 </a> 17
																	Sep 2025, 05:35 pm</p>
															</div>
														</div>
													</div>
													<div class="col-sm-3">
														<div>
															<span>Amount</span>
															<p class="text-dark">$1259</p>
														</div>
													</div>
													<div class="col-sm-3">
														<div
															class="d-flex align-items-center justify-content-sm-end">
															<span
																class="badge badge-soft-danger d-inline-flex  align-items-center me-4"><i
																	class="ti ti-point-filled me-1"></i>Unpaid</span>
															<a href="#" class="btn btn-icon btn-sm"><i
																	class="ti ti-edit"></i></a>
															<a href="#" class="btn btn-icon btn-sm "><i
																	class="ti ti-trash"></i></a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="text-end mb-4">
							<div class="dropdown">
								<a href="javascript:void(0);"
									class="d-inline-flex align-items-center avatar avatar-lg avatar-rounded bg-primary"
									data-bs-toggle="dropdown">
									<i class="ti ti-plus fs-24 text-white"></i>
								</a>
								<ul
									class="dropdown-menu dropdown-menu-end bg-gray-900 dropdown-menu-md dropdown-menu-dark p-3">
									<li>
										<a href="javascript:void(0);"
											class="dropdown-item rounded-1 d-flex align-items-center">
											<span class="avatar avatar-md bg-gray-800 flex-shrink-0 me-2"><i
													class="ti ti-basket-code"></i></span>
											<div>
												<h6 class="fw-medium text-white mb-1">Add a Task</h6>
												<p class="text-white">Create a new Priority tasks </p>
											</div>
										</a>
									</li>
									<li>
										<a href="javascript:void(0);"
											class="dropdown-item rounded-1 d-flex align-items-center">
											<span class="avatar avatar-md bg-gray-800 flex-shrink-0 me-2"><i
													class="ti ti-file-invoice"></i></span>
											<div>
												<h6 class="fw-medium text-white mb-1">Add Invoice</h6>
												<p class="text-white">Create a new Billing</p>
											</div>
										</a>
									</li>
									<li>
										<a href="javascript:void(0);"
											class="dropdown-item rounded-1 d-flex align-items-center">
											<span class="avatar avatar-md bg-gray-800 flex-shrink-0 me-2"><i
													class="ti ti-file-description"></i></span>
											<div>
												<h6 class="fw-medium text-white mb-1">Notes</h6>
												<p class="text-white">Create new note for you & team</p>
											</div>
										</a>
									</li>
									<li>
										<a href="javascript:void(0);"
											class="dropdown-item rounded-1 d-flex align-items-center">
											<span class="avatar avatar-md bg-gray-800 flex-shrink-0 me-2"><i
													class="ti ti-folder-open"></i></span>
											<div>
												<h6 class="fw-medium text-white mb-1">Add Files</h6>
												<p class="text-white">Upload New files for this Client</p>
											</div>
										</a>
									</li>
								</ul>
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