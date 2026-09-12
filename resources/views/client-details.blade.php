@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">
			<div class="row justify-content-between align-items-center mb-4">
				<div class="col-md-12">
					<div class="d-flex justify-content-between align-items-center">
						<h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0"><a href="{{url('clients')}}">
								<i class="ti ti-arrow-left me-2"></i>Clients</a>
						</h6>
						<div class="ms-2 head-icons">
							<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
								data-bs-original-title="Collapse" id="collapse-header">
								<i class="ti ti-chevrons-up"></i>
							</a>
						</div>
					</div>
				</div>

			</div>
			<div class="row">
				<div class="col-xl-4 theiaStickySidebar">
					<div class="card card-bg-1">
						<div class="card-body p-0">
							<span
								class="avatar avatar-xl avatar-rounded border border-2 border-white m-auto d-flex mb-2">
								<img src="{{URL::asset('build/img/users/user-13.jpg')}}" class="w-auto h-auto" alt="Img">
							</span>
							<div class="text-center px-3 pb-3 border-bottom">
								<div class="mb-3">
									<h5 class="d-flex align-items-center justify-content-center mb-1">Stephan
										Peralt<i class="ti ti-discount-check-filled text-success ms-1"></i></h5>
									<p class="text-dark mb-1">EcoVision Enterprises</p>
									<span class="badge badge-soft-secondary fw-medium">Operational Manager</span>
								</div>
								<div>
									<div class="d-flex align-items-center justify-content-between mb-2">
										<span class="d-inline-flex align-items-center">
											<i class="ti ti-id me-2"></i>
											Client ID
										</span>
										<p class="text-dark">CLT-0024</p>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<span class="d-inline-flex align-items-center">
											<i class="ti ti-calendar-check me-2"></i>
											Added on
										</span>
										<p class="text-dark">1st Jan 2023</p>
									</div>
									<div class="row gx-2 mt-3">
										<div class="col-6">
											<div>
												<a href="{{url('voice-call')}}" class="btn btn-dark w-100"><i
														class="ti ti-phone-call me-1"></i>Call</a>
											</div>
										</div>
										<div class="col-6">
											<div>
												<a href="{{url('chat')}}" class="btn btn-primary w-100"><i
														class="ti ti-message-heart me-1"></i>Message</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="p-3 border-bottom">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6>Basic information</h6>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm" data-bs-toggle="modal"
										data-bs-target="#edit_client"><i class="ti ti-edit"></i></a>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<span class="d-inline-flex align-items-center">
										<i class="ti ti-phone me-2"></i>
										Phone
									</span>
									<p class="text-dark">(163) 2459 315</p>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2">
									<span class="d-inline-flex align-items-center">
										<i class="ti ti-mail-check me-2"></i>
										Email
									</span>
									<a href="javascript:void(0);"
										class="text-info d-inline-flex align-items-center">perralt12@example.com<i
											class="ti ti-copy text-dark ms-2"></i></a>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<span class="d-inline-flex align-items-center">
										<i class="ti ti-map-pin-check me-2"></i>
										Address
									</span>
									<p class="text-dark text-end">1861 Bayonne Ave, <br> Manchester, NJ, 08759</p>
								</div>
							</div>
							<div class="p-3">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h6>Social Links</h6>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm"><i
											class="ti ti-edit"></i></a>
								</div>
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="me-2"><img
											src="{{URL::asset('build/img/social/social-01.svg')}}" alt="Img"></a>
									<a href="javascript:void(0);" class="me-2"><img
											src="{{URL::asset('build/img/social/social-06.svg')}}" alt="Img"></a>
									<a href="javascript:void(0);" class="me-2"><img
											src="{{URL::asset('build/img/social/social-02.svg')}}" alt="Img"></a>
									<a href="javascript:void(0);" class="me-2"><img
											src="{{URL::asset('build/img/social/social-03.svg')}}" alt="Img"></a>
									<a href="javascript:void(0);" class="me-2"><img
											src="{{URL::asset('build/img/social/social-04.svg')}}" alt="Img"></a>
									<a href="javascript:void(0);" class="me-2"><img
											src="{{URL::asset('build/img/social/social-05.svg')}}" alt="Img"></a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-8">
					<div>
						<div class="bg-white rounded">
							<ul class="nav nav-tabs nav-tabs-bottom nav-justified flex-wrap mb-4" role="tablist">
								<li class="nav-item" role="presentation">
									<a class="nav-link active fw-medium d-flex align-items-center justify-content-center"
										href="#bottom-justified-tab1" data-bs-toggle="tab" aria-selected="false"
										role="tab">
										<i class="ti ti-star me-1"></i>
										Overview
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link fw-medium d-flex align-items-center justify-content-center"
										href="#bottom-justified-tab2" data-bs-toggle="tab" aria-selected="false"
										role="tab">
										<i class="ti ti-box me-1"></i>
										Projects
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link fw-medium d-flex align-items-center justify-content-center"
										href="#bottom-justified-tab3" data-bs-toggle="tab" aria-selected="true"
										role="tab">
										<i class="ti ti-basket-code me-1"></i>
										Tasks
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link fw-medium d-flex align-items-center justify-content-center"
										href="#bottom-justified-tab4" data-bs-toggle="tab" aria-selected="true"
										role="tab">
										<i class="ti ti-file-invoice me-1"></i>
										Invoices
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link fw-medium d-flex align-items-center justify-content-center"
										href="#bottom-justified-tab5" data-bs-toggle="tab" aria-selected="true"
										role="tab">
										<i class="ti ti-file-description me-1"></i>
										Notes
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link fw-medium d-flex align-items-center justify-content-center"
										href="#bottom-justified-tab6" data-bs-toggle="tab" aria-selected="true"
										role="tab">
										<i class="ti ti-folder-open me-1"></i>
										Documents
									</a>
								</li>
							</ul>
						</div>
						<div class="tab-content custom-accordion-items client-accordion">
							<div class="tab-pane active show" id="bottom-justified-tab1" role="tabpanel">
								<div class="accordion accordions-items-seperate" id="accordionExample">
									<div class="accordion-item">
										<div class="accordion-header" id="headingOne">
											<div class="accordion-button bg-white" data-bs-toggle="collapse"
												data-bs-target="#primaryBorderOne" aria-expanded="true"
												aria-controls="primaryBorderOne" role="button">
												<h5>Projects</h5>
											</div>
										</div>
										<div id="primaryBorderOne"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingOne" data-bs-parent="#accordionExample">
											<div class="accordion-body pb-0">
												<div class="row">
													<div class="col-xxl-6 col-lg-12 col-md-6">
														<div class="card">
															<div class="card-body">
																<div
																	class="d-flex align-items-center pb-3 mb-3 border-bottom">
																	<a href="{{url('project-details')}}"
																		class="flex-shrink-0 me-2">
																		<img src="{{URL::asset('build/img/social/project-01.svg')}}"
																			alt="Img">
																	</a>
																	<div>
																		<h6 class="mb-1"><a
																				href="{{url('project-details')}}">Hospital
																				Administration</a></h6>
																		<div class="d-flex align-items-center">
																			<span>8 tasks</span>
																			<span class="mx-1"><i
																					class="ti ti-point-filled text-primary"></i></span>
																			<span>15  Completed</span>
																		</div>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span
																				class="mb-1 d-block">Deadline</span>
																			<p class="text-dark">31 July 2025</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Value</span>
																			<p class="text-dark">$549987</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Project
																				Lead</span>
																			<h6
																				class="fw-normal d-flex align-items-center">
																				<img class="avatar avatar-xs rounded-circle me-1"
																					src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																					alt="Img">
																				Leona
																			</h6>
																		</div>
																	</div>
																</div>
																<div class="bg-light p-2">
																	<div class="row align-items-center">
																		<div class="col-6">
																			<span
																				class="fw-medium d-flex align-items-center">
																				<i
																					class="ti ti-clock text-primary me-2"></i>Total
																				565 Hrs
																			</span>
																		</div>
																		<div class="col-6">
																			<div>
																				<div
																					class="d-flex align-items-center justify-content-between mb-1">
																					<small class="text-dark">495
																						Hrs</small>
																					<small class="text-dark">70
																						Hrs</small>
																				</div>
																				<div class="progress  progress-xs">
																					<div class="progress-bar bg-warning"
																						role="progressbar"
																						style="width: 75%"></div>
																					<div class="progress-bar bg-success"
																						role="progressbar"
																						style="width: 25%"></div>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="col-xxl-6 col-lg-12 col-md-6">
														<div class="card">
															<div class="card-body">
																<div
																	class="d-flex align-items-center pb-3 mb-3 border-bottom">
																	<a href="{{url('project-details')}}"
																		class="flex-shrink-0 me-2">
																		<img src="{{URL::asset('build/img/social/project-02.svg')}}"
																			alt="Img">
																	</a>
																	<div>
																		<h6 class="mb-1"><a
																				href="{{url('project-details')}}">Video
																				Calling App</a></h6>
																		<div class="d-flex align-items-center">
																			<span>22 tasks</span>
																			<span class="mx-1"><i
																					class="ti ti-point-filled text-primary"></i></span>
																			<span>15 Completed</span>
																		</div>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span
																				class="mb-1 d-block">Deadline</span>
																			<p class="text-dark">16 Jan 2025</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Value</span>
																			<p class="text-dark">$279987</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Project
																				Lead</span>
																			<h6
																				class="fw-normal d-flex align-items-center">
																				<img class="avatar avatar-xs rounded-circle me-1"
																					src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																					alt="Img">
																				Mathis
																			</h6>
																		</div>
																	</div>
																</div>
																<div class="bg-light p-2">
																	<div class="row align-items-center">
																		<div class="col-6">
																			<span
																				class="fw-medium d-flex align-items-center">
																				<i
																					class="ti ti-clock text-primary me-2"></i>Total
																				700 Hrs
																			</span>
																		</div>
																		<div class="col-6">
																			<div>
																				<div
																					class="d-flex align-items-center justify-content-between mb-1">
																					<small class="text-dark">605
																						Hrs</small>
																					<small class="text-dark">95
																						Hrs</small>
																				</div>
																				<div class="progress  progress-xs">
																					<div class="progress-bar bg-warning"
																						role="progressbar"
																						style="width: 75%"></div>
																					<div class="progress-bar bg-success"
																						role="progressbar"
																						style="width: 25%"></div>
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
									<div class="accordion-item">
										<div class="accordion-header" id="headingTwo">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												data-bs-target="#primaryBorderTwo" aria-expanded="false"
												aria-controls="primaryBorderTwo" role="button">
												<h5>Tasks</h5>
											</div>
										</div>
										<div id="primaryBorderTwo" class="accordion-collapse collapse border-top"
											aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
											<div class="accordion-body">
												<div class="list-group list-group-flush">
													<div class="list-group-item border rounded mb-2 p-2">
														<div class="row align-items-center row-gap-3">
															<div class="col-md-7">
																<div
																	class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox">
																	</div>
																	<span
																		class="me-2 d-flex align-items-center rating-select"><i
																			class="ti ti-star-filled filled"></i></span>
																	<div class="strike-info">
																		<h4 class="fs-14">Patient appointment
																			booking</h4>
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
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox">
																	</div>
																	<span
																		class="me-2 rating-select d-flex align-items-center"><i
																			class="ti ti-star"></i></span>
																	<div class="strike-info">
																		<h4 class="fs-14">Appointment booking with
																			payment gateway</h4>
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
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox">
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
													<div class="list-group-item border rounded p-2">
														<div class="row align-items-center row-gap-3">
															<div class="col-md-7">
																<div
																	class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3 todo-strike-content">
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox" checked="">
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
												</div>
											</div>
										</div>
									</div>
									<div class="accordion-item">
										<div class="accordion-header" id="headingThree">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderThree"
												aria-expanded="false" aria-controls="primaryBorderThree">
												<h5>Invoices</h5>
											</div>
										</div>
										<div id="primaryBorderThree" class="accordion-collapse collapse border-top"
											aria-labelledby="headingThree" data-bs-parent="#accordionExample">
											<div class="accordion-body">
												<div class="row align-items-center g-3 mb-3">
													<div class="col-sm-8">
														<h6>Total No of Invoice : 45</h6>
													</div>
													<div class="col-sm-4">
														<div class="position-relative input-icon">
															<span class="input-icon-addon">
																<i class="ti ti-search"></i>
															</span>
															<input type="text" class="form-control"
																placeholder="Search">
														</div>
													</div>
												</div>
												<div class="list-group list-group-flush mb-3">
													<div class="list-group-item border rounded mb-2 p-2">
														<div class="row align-items-center g-3">
															<div class="col-sm-6">
																<div class="d-flex align-items-center">
																	<span
																		class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																			class="ti ti-file-invoice text-dark fs-24"></i></span>
																	<div>
																		<h6 class="fw-medium mb-1">Phase 2
																			Completion</h6>
																		<p><a href="#" class="text-info">#INV-123
																			</a> 11 Sep 2025, 05:35 pm</p>
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
																		<h6 class="fw-medium mb-1">Advance for
																			Project</h6>
																		<p><a href="#" class="text-info">#INV-124
																			</a> 14 Sep 2025, 05:35 pm</p>
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
																		<p><a href="#" class="text-info">#INV-125
																			</a> 15 Sep 2025, 05:35 pm</p>
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
																		<h6 class="fw-medium mb-1">Added New
																			Functionality</h6>
																		<p><a href="#" class="text-info">#INV-126
																			</a> 16 Sep 2025, 05:35 pm</p>
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
																		<h6 class="fw-medium mb-1">Phase 1
																			Completion</h6>
																		<p><a href="#" class="text-info">#INV-127
																			</a> 17 Sep 2025, 05:35 pm</p>
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
												<div class="text-center">
													<a href="#" class="btn btn-primary btn-sm">Load More</a>
												</div>
											</div>
										</div>
									</div>
									<div class="accordion-item">
										<div class="accordion-header" id="headingFour">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderFour"
												aria-expanded="false" aria-controls="primaryBorderFour">
												<h5>Notes</h5>
											</div>
										</div>
										<div id="primaryBorderFour" class="accordion-collapse collapse border-top"
											aria-labelledby="headingFour" data-bs-parent="#accordionExample">
											<div class="accordion-body">
												<div class="row align-items-center g-3 mb-3">
													<div class="col-sm-8">
														<h6>Total No of Notes : 45</h6>
													</div>
													<div class="col-sm-4">
														<div class="position-relative input-icon">
															<span class="input-icon-addon">
																<i class="ti ti-search"></i>
															</span>
															<input type="text" class="form-control"
																placeholder="Search">
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-4 col-sm-6 d-flex">
														<div class="card flex-fill">
															<div class="card-body">
																<div
																	class="d-flex align-items-center justify-content-between mb-2">
																	<h6 class="text-gray-5 fw-medium">15 May 2025
																	</h6>
																	<div class="dropdown">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown"
																			aria-expanded="false">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
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
																	& design</h6>
																<p class="text-truncate line-clamb-3">An office
																	management app project streamlines
																	administrative tasks by integrating
																	tools for scheduling, communication, and
																	task management, enhancing overall productivity
																	and efficiency.
																</p>
															</div>
														</div>
													</div>
													<div class="col-md-4 col-sm-6 d-flex">
														<div class="card flex-fill">
															<div class="card-body">
																<div
																	class="d-flex align-items-center justify-content-between mb-2">
																	<h6 class="text-gray-5 fw-medium">16 May 2025
																	</h6>
																	<div class="dropdown">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown"
																			aria-expanded="false">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
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
																		class="ti ti-point-filled text-success me-1"></i>Phase
																	1 Completion</h6>
																<p class="text-truncate line-clamb-3">
																	An office management app project streamlines
																	administrative tasks by integrating tools for
																	scheduling, communication, and task
																	management, enhancing overall productivity and
																	efficiency.
																</p>
															</div>
														</div>
													</div>
													<div class="col-md-4 col-sm-6 d-flex">
														<div class="card flex-fill">
															<div class="card-body">
																<div
																	class="d-flex align-items-center justify-content-between mb-2">
																	<h6 class="text-gray-5 fw-medium">17 May 2025
																	</h6>
																	<div class="dropdown">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown"
																			aria-expanded="false">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
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
																		class="ti ti-point-filled text-danger me-1"></i>Phase
																	2 Completion</h6>
																<p class="text-truncate line-clamb-3">
																	An office management app project streamlines
																	administrative tasks by integrating tools for
																	scheduling, communication, and task
																	management, enhancing overall productivity and
																	efficiency.
																</p>
															</div>
														</div>
													</div>
													<div class="col-md-12">
														<div class="text-center">
															<a href="#" class="btn btn-primary btn-sm">Load More</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="accordion-item">
										<div class="accordion-header" id="headingFive">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderFive"
												aria-expanded="false" aria-controls="primaryBorderFive">
												<h5>Documents</h5>
											</div>
										</div>
										<div id="primaryBorderFive" class="accordion-collapse collapse border-top"
											aria-labelledby="headingFive" data-bs-parent="#accordionExample">
											<div class="accordion-body">
												<div class="row align-items-center g-3 mb-3">
													<div class="col-sm-4">
														<h6>Total No of Documents : 45</h6>
													</div>
													<div class="col-sm-8">
														<div class="d-flex align-items-center">
															<div class="dropdown me-2">
																<a href="javascript:void(0);"
																	class="dropdown-toggle btn btn-white"
																	data-bs-toggle="dropdown" aria-expanded="false">
																	Sort By : Docs Type
																</a>
																<ul class="dropdown-menu dropdown-menu-end p-3">
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Docs</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Pdf</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Image</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Folder</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Xml</a>
																	</li>
																</ul>
															</div>
															<div class="position-relative input-icon flex-fill">
																<span class="input-icon-addon">
																	<i class="ti ti-search"></i>
																</span>
																<input type="text" class="form-control"
																	placeholder="Search">
															</div>
														</div>
													</div>
												</div>
												<div
													class="custom-datatable-filter table-responsive no-datatable-length border">
													<table class="table datatable">
														<thead class="thead-light">
															<tr>
																<th>Name</th>
																<th>Size</th>
																<th>Type</th>
																<th>Modified</th>
																<th>Share</th>
																<th></th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-01.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Secret</a>
																			</p>
																		</div>
																	</div>
																</td>
																<td>7.6 MB</td>
																<td>Doc</td>
																<td>
																	<p class="text-title mb-0">Mar 15, 2025</p>
																	<span>05:00:14 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-02.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Sophie
																					Headrick</a></p>
																		</div>
																	</div>
																</td>
																<td>7.4 MB</td>
																<td>PDF</td>
																<td>
																	<p class="text-title mb-0">Jan 8, 2025</p>
																	<span>08:20:13 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-03.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Gallery</a>
																			</p>
																		</div>
																	</div>
																</td>
																<td>6.1 MB</td>
																<td>Image</td>
																<td>
																	<p class="text-title mb-0">Aug 6, 2025</p>
																	<span>04:10:12 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
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
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
																				alt="img">
																		</span>
																		<a class="avatar bg-primary avatar-rounded text-fixed-white"
																			href="javascript:void(0);">
																			+1
																		</a>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-04.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Doris
																					Crowley</a></p>
																		</div>
																	</div>
																</td>
																<td>5.2 MB</td>
																<td>Folder</td>
																<td>
																	<p class="text-title mb-0">Jan 6, 2025</p>
																	<span>03:40:14 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-05.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Cheat_codez</a>
																			</p>
																		</div>
																	</div>
																</td>
																<td>8 MB</td>
																<td>Xml</td>
																<td>
																	<p class="text-title mb-0">Oct 12, 2025</p>
																	<span>05:00:14 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
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
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="bottom-justified-tab2" role="tabpanel">
								<div class="accordion accordions-items-seperate">
									<div class="accordion-item">
										<div class="accordion-header" id="headingOne2">
											<div class="accordion-button bg-white" data-bs-toggle="collapse"
												data-bs-target="#primaryBorderOne2" aria-expanded="true"
												aria-controls="primaryBorderOne2" role="button">
												<h5>Projects</h5>
											</div>
										</div>
										<div id="primaryBorderOne2"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingOne2">
											<div class="accordion-body pb-0">
												<div class="row">
													<div class="col-xxl-6 col-lg-12 col-md-6">
														<div class="card">
															<div class="card-body">
																<div
																	class="d-flex align-items-center pb-3 mb-3 border-bottom">
																	<a href="{{url('project-details')}}"
																		class="flex-shrink-0 me-2">
																		<img src="{{URL::asset('build/img/social/project-01.svg')}}"
																			alt="Img">
																	</a>
																	<div>
																		<h6 class="mb-1"><a
																				href="{{url('project-details')}}">Hospital
																				Administration</a></h6>
																		<div class="d-flex align-items-center">
																			<span>8 tasks</span>
																			<span class="mx-1"><i
																					class="ti ti-point-filled text-primary"></i></span>
																			<span>15  Completed</span>
																		</div>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span
																				class="mb-1 d-block">Deadline</span>
																			<p class="text-dark">31 July 2025</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Value</span>
																			<p class="text-dark">$549987</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Project
																				Lead</span>
																			<h6
																				class="fw-normal d-flex align-items-center">
																				<img class="avatar avatar-xs rounded-circle me-1"
																					src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
																					alt="Img">
																				Leona
																			</h6>
																		</div>
																	</div>
																</div>
																<div class="bg-light p-2">
																	<div class="row align-items-center">
																		<div class="col-6">
																			<span
																				class="fw-medium d-flex align-items-center">
																				<i
																					class="ti ti-clock text-primary me-2"></i>Total
																				565 Hrs
																			</span>
																		</div>
																		<div class="col-6">
																			<div>
																				<div
																					class="d-flex align-items-center justify-content-between mb-1">
																					<small class="text-dark">495
																						Hrs</small>
																					<small class="text-dark">70
																						Hrs</small>
																				</div>
																				<div class="progress  progress-xs">
																					<div class="progress-bar bg-warning"
																						role="progressbar"
																						style="width: 75%"></div>
																					<div class="progress-bar bg-success"
																						role="progressbar"
																						style="width: 25%"></div>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="col-xxl-6 col-lg-12 col-md-6">
														<div class="card">
															<div class="card-body">
																<div
																	class="d-flex align-items-center pb-3 mb-3 border-bottom">
																	<a href="{{url('project-details')}}"
																		class="flex-shrink-0 me-2">
																		<img src="{{URL::asset('build/img/social/project-02.svg')}}"
																			alt="Img">
																	</a>
																	<div>
																		<h6 class="mb-1"><a
																				href="{{url('project-details')}}">Video
																				Calling App</a></h6>
																		<div class="d-flex align-items-center">
																			<span>22 tasks</span>
																			<span class="mx-1"><i
																					class="ti ti-point-filled text-primary"></i></span>
																			<span>15 Completed</span>
																		</div>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span
																				class="mb-1 d-block">Deadline</span>
																			<p class="text-dark">16 Jan 2025</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Value</span>
																			<p class="text-dark">$279987</p>
																		</div>
																	</div>
																	<div class="col-sm-4">
																		<div class="mb-3">
																			<span class="mb-1 d-block">Project
																				Lead</span>
																			<h6
																				class="fw-normal d-flex align-items-center">
																				<img class="avatar avatar-xs rounded-circle me-1"
																					src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
																					alt="Img">
																				Mathis
																			</h6>
																		</div>
																	</div>
																</div>
																<div class="bg-light p-2">
																	<div class="row align-items-center">
																		<div class="col-6">
																			<span
																				class="fw-medium d-flex align-items-center">
																				<i
																					class="ti ti-clock text-primary me-2"></i>Total
																				700 Hrs
																			</span>
																		</div>
																		<div class="col-6">
																			<div>
																				<div
																					class="d-flex align-items-center justify-content-between mb-1">
																					<small class="text-dark">605
																						Hrs</small>
																					<small class="text-dark">95
																						Hrs</small>
																				</div>
																				<div class="progress  progress-xs">
																					<div class="progress-bar bg-warning"
																						role="progressbar"
																						style="width: 75%"></div>
																					<div class="progress-bar bg-success"
																						role="progressbar"
																						style="width: 25%"></div>
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
							</div>
							<div class="tab-pane" id="bottom-justified-tab3" role="tabpanel">
								<div class="accordion accordions-items-seperate">
									<div class="accordion-item">
										<div class="accordion-header" id="headingTwo2">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderTwo2"
												aria-expanded="true" aria-controls="primaryBorderTwo2">
												<h5>Tasks</h5>
											</div>
										</div>
										<div id="primaryBorderTwo2"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingTwo2">
											<div class="accordion-body">
												<div class="list-group list-group-flush">
													<div class="list-group-item border rounded mb-2 p-2">
														<div class="row align-items-center row-gap-3">
															<div class="col-md-7">
																<div
																	class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3">
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox">
																	</div>
																	<span
																		class="me-2 d-flex align-items-center rating-select"><i
																			class="ti ti-star-filled filled"></i></span>
																	<div class="strike-info">
																		<h4 class="fs-14">Patient appointment
																			booking</h4>
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
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox">
																	</div>
																	<span
																		class="me-2 rating-select d-flex align-items-center"><i
																			class="ti ti-star"></i></span>
																	<div class="strike-info">
																		<h4 class="fs-14">Appointment booking with
																			payment gateway</h4>
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
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox">
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
													<div class="list-group-item border rounded p-2">
														<div class="row align-items-center row-gap-3">
															<div class="col-md-7">
																<div
																	class="todo-inbox-check d-flex align-items-center flex-wrap row-gap-3 todo-strike-content">
																	<div class="form-check form-check-md me-2">
																		<input class="form-check-input"
																			type="checkbox" checked="">
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
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="bottom-justified-tab4" role="tabpanel">
								<div class="accordion accordions-items-seperate">
									<div class="accordion-item">
										<div class="accordion-header" id="headingThree2">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderThree2"
												aria-expanded="true" aria-controls="primaryBorderThree2">
												<h5>Invoices</h5>
											</div>
										</div>
										<div id="primaryBorderThree2"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingThree2">
											<div class="accordion-body">
												<div class="row align-items-center g-3 mb-3">
													<div class="col-sm-8">
														<h6>Total No of Invoice : 45</h6>
													</div>
													<div class="col-sm-4">
														<div class="position-relative input-icon">
															<span class="input-icon-addon">
																<i class="ti ti-search"></i>
															</span>
															<input type="text" class="form-control"
																placeholder="Search">
														</div>
													</div>
												</div>
												<div class="list-group list-group-flush mb-3">
													<div class="list-group-item border rounded mb-2 p-2">
														<div class="row align-items-center g-3">
															<div class="col-sm-6">
																<div class="d-flex align-items-center">
																	<span
																		class="avatar avatar-lg bg-light flex-shrink-0 me-2"><i
																			class="ti ti-file-invoice text-dark fs-24"></i></span>
																	<div>
																		<h6 class="fw-medium mb-1">Phase 2
																			Completion</h6>
																		<p><a href="#" class="text-info">#INV-123
																			</a> 11 Sep 2025, 05:35 pm</p>
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
																		<h6 class="fw-medium mb-1">Advance for
																			Project</h6>
																		<p><a href="#" class="text-info">#INV-124
																			</a> 14 Sep 2025, 05:35 pm</p>
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
																		<p><a href="#" class="text-info">#INV-125
																			</a> 15 Sep 2025, 05:35 pm</p>
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
																		<h6 class="fw-medium mb-1">Added New
																			Functionality</h6>
																		<p><a href="#" class="text-info">#INV-126
																			</a> 16 Sep 2025, 05:35 pm</p>
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
																		<h6 class="fw-medium mb-1">Phase 1
																			Completion</h6>
																		<p><a href="#" class="text-info">#INV-127
																			</a> 17 Sep 2025, 05:35 pm</p>
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
												<div class="text-center">
													<a href="#" class="btn btn-primary btn-sm">Load More</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="bottom-justified-tab5" role="tabpanel">
								<div class="accordion accordions-items-seperate">
									<div class="accordion-item">
										<div class="accordion-header" id="headingFour2">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderFour2"
												aria-expanded="true" aria-controls="primaryBorderFour2">
												<h5>Notes</h5>
											</div>
										</div>
										<div id="primaryBorderFour2"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingFour2">
											<div class="accordion-body">
												<div class="row align-items-center g-3 mb-3">
													<div class="col-sm-8">
														<h6>Total No of Notes : 45</h6>
													</div>
													<div class="col-sm-4">
														<div class="position-relative input-icon">
															<span class="input-icon-addon">
																<i class="ti ti-search"></i>
															</span>
															<input type="text" class="form-control"
																placeholder="Search">
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-4 col-sm-6 d-flex">
														<div class="card flex-fill">
															<div class="card-body">
																<div
																	class="d-flex align-items-center justify-content-between mb-2">
																	<h6 class="text-gray-5 fw-medium">15 May 2025
																	</h6>
																	<div class="dropdown">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown"
																			aria-expanded="false">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
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
																	& design</h6>
																<p class="text-truncate line-clamb-3">An office
																	management app project streamlines
																	administrative tasks by integrating
																	tools for scheduling, communication, and
																	task management, enhancing overall productivity
																	and efficiency.
																</p>
															</div>
														</div>
													</div>
													<div class="col-md-4 col-sm-6 d-flex">
														<div class="card flex-fill">
															<div class="card-body">
																<div
																	class="d-flex align-items-center justify-content-between mb-2">
																	<h6 class="text-gray-5 fw-medium">16 May 2025
																	</h6>
																	<div class="dropdown">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown"
																			aria-expanded="false">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
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
																		class="ti ti-point-filled text-success me-1"></i>Phase
																	1 Completion</h6>
																<p class="text-truncate line-clamb-3">
																	An office management app project streamlines
																	administrative tasks by integrating tools for
																	scheduling, communication, and task
																	management, enhancing overall productivity and
																	efficiency.
																</p>
															</div>
														</div>
													</div>
													<div class="col-md-4 col-sm-6 d-flex">
														<div class="card flex-fill">
															<div class="card-body">
																<div
																	class="d-flex align-items-center justify-content-between mb-2">
																	<h6 class="text-gray-5 fw-medium">17 May 2025
																	</h6>
																	<div class="dropdown">
																		<a href="javascript:void(0);"
																			class="d-inline-flex align-items-center"
																			data-bs-toggle="dropdown"
																			aria-expanded="false">
																			<i class="ti ti-dots-vertical"></i>
																		</a>
																		<ul
																			class="dropdown-menu dropdown-menu-end p-3">
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
																		class="ti ti-point-filled text-danger me-1"></i>Phase
																	2 Completion</h6>
																<p class="text-truncate line-clamb-3">
																	An office management app project streamlines
																	administrative tasks by integrating tools for
																	scheduling, communication, and task
																	management, enhancing overall productivity and
																	efficiency.
																</p>
															</div>
														</div>
													</div>
													<div class="col-md-12">
														<div class="text-center">
															<a href="#" class="btn btn-primary btn-sm">Load More</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="bottom-justified-tab6" role="tabpanel">
								<div class="accordion accordions-items-seperate">
									<div class="accordion-item">
										<div class="accordion-header" id="headingFive2">
											<div class="accordion-button collapsed" data-bs-toggle="collapse"
												role="button" data-bs-target="#primaryBorderFive2"
												aria-expanded="true" aria-controls="primaryBorderFive2">
												<h5>Documents</h5>
											</div>
										</div>
										<div id="primaryBorderFive2"
											class="accordion-collapse collapse show border-top"
											aria-labelledby="headingFive2">
											<div class="accordion-body">
												<div class="row align-items-center g-3 mb-3">
													<div class="col-sm-4">
														<h6>Total No of Documents : 45</h6>
													</div>
													<div class="col-sm-8">
														<div class="d-flex align-items-center">
															<div class="dropdown me-2">
																<a href="javascript:void(0);"
																	class="dropdown-toggle btn btn-white"
																	data-bs-toggle="dropdown" aria-expanded="false">
																	Sort By : Docs Type
																</a>
																<ul class="dropdown-menu dropdown-menu-end p-3">
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Docs</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Pdf</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Image</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Folder</a>
																	</li>
																	<li>
																		<a href="javascript:void(0);"
																			class="dropdown-item rounded-1">Xml</a>
																	</li>
																</ul>
															</div>
															<div class="position-relative input-icon flex-fill">
																<span class="input-icon-addon">
																	<i class="ti ti-search"></i>
																</span>
																<input type="text" class="form-control"
																	placeholder="Search">
															</div>
														</div>
													</div>
												</div>
												<div
													class="custom-datatable-filter table-responsive no-datatable-length border">
													<table class="table datatable">
														<thead class="thead-light">
															<tr>
																<th>Name</th>
																<th>Size</th>
																<th>Type</th>
																<th>Modified</th>
																<th>Share</th>
																<th></th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-01.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Secret</a>
																			</p>
																		</div>
																	</div>
																</td>
																<td>7.6 MB</td>
																<td>Doc</td>
																<td>
																	<p class="text-title mb-0">Mar 15, 2025</p>
																	<span>05:00:14 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-02.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Sophie
																					Headrick</a></p>
																		</div>
																	</div>
																</td>
																<td>7.4 MB</td>
																<td>PDF</td>
																<td>
																	<p class="text-title mb-0">Jan 8, 2025</p>
																	<span>08:20:13 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-03.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Gallery</a>
																			</p>
																		</div>
																	</div>
																</td>
																<td>6.1 MB</td>
																<td>Image</td>
																<td>
																	<p class="text-title mb-0">Aug 6, 2025</p>
																	<span>04:10:12 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
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
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
																				alt="img">
																		</span>
																		<a class="avatar bg-primary avatar-rounded text-fixed-white"
																			href="javascript:void(0);">
																			+1
																		</a>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-04.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Doris
																					Crowley</a></p>
																		</div>
																	</div>
																</td>
																<td>5.2 MB</td>
																<td>Folder</td>
																<td>
																	<p class="text-title mb-0">Jan 6, 2025</p>
																	<span>03:40:14 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}"
																				alt="img">
																		</span>
																	</div>
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td>
																	<div
																		class="d-flex align-items-center file-name-icon">
																		<a href="#"
																			class="avatar avatar-md bg-light"
																			data-bs-toggle="offcanvas"
																			data-bs-target="#preview">
																			<img src="{{URL::asset('build/img/icons/file-05.svg')}}"
																				class="img-fluid" alt="img"></a>
																		<div class="ms-2">
																			<p class="text-title fw-medium  mb-0"><a
																					href="#"
																					data-bs-toggle="offcanvas"
																					data-bs-target="#preview">Cheat_codez</a>
																			</p>
																		</div>
																	</div>
																</td>
																<td>8 MB</td>
																<td>Xml</td>
																<td>
																	<p class="text-title mb-0">Oct 12, 2025</p>
																	<span>05:00:14 PM</span>
																</td>
																<td>
																	<div
																		class="avatar-list-stacked avatar-group-sm">
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}"
																				alt="img">
																		</span>
																		<span class="avatar avatar-rounded">
																			<img class="border border-white"
																				src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
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
																</td>
																<td>
																	<div class="d-flex align-items-center">
																		<div class="rating-select me-2">
																			<a href="javascript:void(0);"><i
																					class="ti ti-star"></i></a>
																		</div>
																		<div class="dropdown">
																			<a href="#"
																				class="d-flex align-items-center justify-content-center"
																				data-bs-toggle="dropdown"
																				aria-expanded="false">
																				<i class="ti ti-dots fs-14"></i>
																			</a>
																			<ul
																				class="dropdown-menu dropdown-menu-right p-3">
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-trash me-2"></i>Permanent
																						Delete
																					</a>
																				</li>
																				<li>
																					<a class="dropdown-item rounded-1"
																						href="#">
																						<i
																							class="ti ti-edit-circle me-2"></i>Restore
																						File
																					</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</td>
															</tr>
														</tbody>
													</table>
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