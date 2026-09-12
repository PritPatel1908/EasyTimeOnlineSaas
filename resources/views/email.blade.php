@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content p-0">

			<div class="d-md-flex">
				<div class="email-sidebar border-end border-bottom">
					<div data-simplebar class="active slimscroll h-100">
						<div class="slimscroll-active-sidebar">
							<div class="p-3">
								<div class="shadow-md bg-white rounded p-2 mb-4">
									<div class="d-flex align-items-center">
										<a href="javascript:void(0);" class="avatar avatar-md flex-shrink-0 me-2">
											<img src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}" class="rounded-circle"
												alt="Img">
										</a>
										<div>
											<h6 class="mb-1"><a href="javascript:void(0);">James Hong</a></h6>
											<p>Jnh343@example.com</p>
										</div>
									</div>
								</div>
								<a href="javascript:void(0);" class="btn btn-primary w-100" id="compose_mail"><i
										class="ti ti-edit me-2"></i>Compose</a>
								<div class="mt-4">
									<h5 class="mb-2">Emails</h5>
									<div class="d-block mb-4 pb-4 border-bottom email-tags">
										<a href="{{url('email')}}"
											class="d-flex align-items-center justify-content-between p-2 rounded active">
											<span class="d-flex align-items-center fw-medium"><i
													class="ti ti-inbox text-gray me-2"></i>Inbox</span>
											<span class="badge badge-danger rounded-pill badge-xs">56</span>
										</a>
										<a href="javascript:void(0);"
											class="d-flex align-items-center justify-content-between p-2 rounded">
											<span class="d-flex align-items-center fw-medium"><i
													class="ti ti-star text-gray me-2"></i>Starred</span>
											<span class="fw-semibold fs-12 badge text-gray rounded-pill">46</span>
										</a>
										<a href="javascript:void(0);"
											class="d-flex align-items-center justify-content-between p-2 rounded">
											<span class="d-flex align-items-center fw-medium"><i
													class="ti ti-rocket text-gray me-2"></i>Sent</span>
											<span class="badge text-gray rounded-pill">14</span>
										</a>
										<a href="javascript:void(0);"
											class="d-flex align-items-center justify-content-between p-2 rounded">
											<span class="d-flex align-items-center fw-medium"><i
													class="ti ti-file text-gray me-2"></i>Drafts</span>
											<span class="badge text-gray rounded-pill">12</span>
										</a>
										<a href="javascript:void(0);"
											class="d-flex align-items-center justify-content-between p-2 rounded">
											<span class="d-flex align-items-center fw-medium"><i
													class="ti ti-trash text-gray me-2"></i>Deleted</span>
											<span class="badge text-gray rounded-pill">08</span>
										</a>
										<a href="javascript:void(0);"
											class="d-flex align-items-center justify-content-between p-2 rounded">
											<span class="d-flex align-items-center fw-medium"><i
													class="ti ti-info-octagon text-gray me-2"></i>Spam</span>
											<span class="badge text-gray rounded-pill">0</span>
										</a>
										<div>
											<div class="more-menu">
												<a href="javascript:void(0);"
													class="d-flex align-items-center justify-content-between p-2 rounded">
													<span class="d-flex align-items-center fw-medium"><i
															class="ti ti-location-up text-gray me-2"></i>Important</span>
													<span class="badge text-gray rounded-pill">12</span>
												</a>
												<a href="javascript:void(0);"
													class="d-flex align-items-center justify-content-between p-2 rounded">
													<span class="d-flex align-items-center fw-medium"><i
															class="ti ti-transition-top text-gray me-2"></i>All
														Emails</span>
													<span class="badge text-gray rounded-pill">34</span>
												</a>
											</div>
											<div class="view-all mt-2">
												<a href="javascript:void(0);"
													class="viewall-button fw-medium"><span>Show More</span><i
														class="fa fa-chevron-down fs-10 ms-2"></i></a>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-4 pb-4">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<h5>Labels</h5>
										<a href="javascript:void(0);"><i
												class="ti ti-square-rounded-plus-filled text-primary fs-16"></i></a>
									</div>
									<div>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-square-rounded text-success me-2"></i>
											Team Events
										</a>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-square-rounded text-warning me-2"></i>
											Work
										</a>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-square-rounded text-danger me-2"></i>
											External
										</a>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-square-rounded text-skyblue me-2"></i>
											Projects
										</a>
										<div>
											<div class="more-menu-2">
												<a href="javascript:void(0);"
													class="fw-medium d-flex align-items-center text-dark py-1">
													<i class="ti ti-square-rounded text-purple me-2"></i>
													Applications
												</a>
												<a href="javascript:void(0);"
													class="fw-medium d-flex align-items-center text-dark py-1">
													<i class="ti ti-square-rounded text-info me-2"></i>
													Desgin
												</a>
											</div>
											<div class="view-all mt-2">
												<a href="javascript:void(0);"
													class="viewall-button-2 fw-medium"><span>Show More</span><i
														class="fa fa-chevron-down fs-10 ms-2"></i></a>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-4 pb-4">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<h5>Folders</h5>
										<a href="javascript:void(0);"><i
												class="ti ti-square-rounded-plus-filled text-primary fs-16"></i></a>
									</div>
									<div>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-folder-filled text-danger me-2"></i>
											Projects
										</a>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-folder-filled text-warning me-2"></i>
											Personal
										</a>
										<a href="javascript:void(0);"
											class="fw-medium d-flex align-items-center text-dark py-1">
											<i class="ti ti-folder-filled text-success me-2"></i>
											Finance
										</a>
										<div>
											<div class="more-menu-3">
												<a href="javascript:void(0);"
													class="fw-medium d-flex align-items-center text-dark py-1">
													<i class="ti ti-folder-filled text-info me-2"></i>
													Projects
												</a>
												<a href="javascript:void(0);"
													class="fw-medium d-flex align-items-center text-dark py-1">
													<i class="ti ti-folder-filled text-primary me-2"></i>
													Personal
												</a>
											</div>
											<div class="view-all mt-2">
												<a href="javascript:void(0);"
													class="viewall-button-3 fw-medium"><span>Show More</span><i
														class="fa fa-chevron-down fs-10 ms-2"></i></a>
											</div>
										</div>
									</div>
								</div>
								<div class="bg-dark rounded text-center position-relative p-4">
									<span class="avatar avatar-lg rounded-circle bg-white mb-2">
										<i class="ti ti-alert-triangle text-dark"></i>
									</span>
									<h6 class="text-white mb-3">Enjoy Unlimited Access on a small price monthly.
									</h6>
									<a href="javascript:void(0);" class="btn btn-white">Upgrade Now <i
											class="ti ti-arrow-right"></i></a>
									<div class="box-bg">
										<span class="bg-right"><img src="{{URL::asset('build/img/bg/email-bg-01.png')}}"
												alt="Img"></span>
										<span class="bg-left"><img src="{{URL::asset('build/img/bg/email-bg-02.png')}}"
												alt="Img"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="bg-white flex-fill border-end border-bottom mail-notifications">
					<div data-simplebar class="active slimscroll h-100">
						<div class="slimscroll-active-sidebar">
							<div class="p-3">
								<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
									<div>
										<h5 class="mb-1">Inbox</h5>
										<div class="d-flex align-items-center">
											<span>2345 Emails</span>
											<i class="ti ti-point-filled text-primary mx-1"></i>
											<span>56 Unread</span>
										</div>
									</div>
									<div class="d-flex align-items-center">
										<div class="position-relative input-icon me-3">
											<span class="input-icon-addon">
												<i class="ti ti-search"></i>
											</span>
											<input type="text" class="form-control" placeholder="Search Email">
										</div>
										<div class="d-flex align-items-center">
											<a href="javascript:void(0);"
												class="btn btn-icon btn-sm rounded-circle"><i
													class="ti ti-filter-edit"></i></a>
											<a href="javascript:void(0);"
												class="btn btn-icon btn-sm rounded-circle"><i
													class="ti ti-settings"></i></a>
											<a href="javascript:void(0);"
												class="btn btn-icon btn-sm rounded-circle"><i
													class="ti ti-refresh"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="list-group list-group-flush mails-list">
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar bg-purple avatar-rounded me-2">
												<span class="avatar-title">CD</span>
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Justin
																Lapointe</a></h6>
														<span class="fw-semibold">Client Dashboard</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-success"></i>3:13
															PM</span>
													</div>
												</div>
												<p>It seems that recipients are receiving...</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark me-2"><i
													class="ti ti-folder-open me-2"></i>3</span>
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i
													class="ti ti-photo me-2"></i>+24</span>
										</div>
										<div class="d-flex align-items-center">
											<span><i class="ti ti-star-filled text-warning"></i></span>
											<span class="badge badge-soft-info mx-2"><i
													class="ti ti-square me-1"></i>Projects</span>
											<a href="javascript:void(0);"
												class="badge badge-dark rounded-pill badge-xs">+1</a>
										</div>
									</div>
								</div>
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar avatar-md avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Rufana</a></h6>
														<span class="fw-semibold">UI project</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>Regardless, you can usually expect an increase</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<a href="javascript:void(0);"><img src="{{URL::asset('build/img/icons/google-meet.svg')}}"
												alt="Img"></a>
										<div class="d-flex align-items-center">
											<span><i class="ti ti-star-filled text-warning"></i></span>
											<span class="badge badge-soft-purple mx-2"><i
													class="ti ti-square me-1"></i>Applications</span>
											<a href="javascript:void(0);"
												class="badge badge-dark rounded-pill badge-xs">+1</a>
										</div>
									</div>
								</div>
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar avatar-md avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Cameron
																Drake</a></h6>
														<span class="fw-semibold">You’re missing</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>Here are a few catchy email subject line examples </p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark fs-14"><i
													class="ti ti-video me-2"></i>1</span>
										</div>
										<div class="d-flex align-items-center">
											<span><i class="ti ti-star-filled text-warning"></i></span>
											<span class="badge badge-soft-danger mx-2"><i
													class="ti ti-square me-1"></i>External</span>
											<a href="javascript:void(0);"
												class="badge badge-dark rounded-pill badge-xs">+1</a>
										</div>
									</div>
								</div>
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar avatar-md avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Sean Hill</a>
														</h6>
														<span class="fw-semibold">How Have You Progressed</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>You can write effective retargeting subject</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i
													class="ti ti-photo me-2"></i>1</span>
										</div>
										<div class="d-flex align-items-center">
											<span class="badge badge-soft-success"><i
													class="ti ti-square me-1"></i>Team Events</span>
										</div>
									</div>
								</div>
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar avatar-md avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Kevin Alley</a>
														</h6>
														<span class="fw-semibold">Flash. Sale. Alert.</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>You can also use casual language,</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i
													class="ti ti-link me-2"></i>1</span>
										</div>
										<div class="d-flex align-items-center">
											<span class="badge badge-soft-danger me-2"><i
													class="ti ti-square me-1"></i>External</span>
											<a href="javascript:void(0);"
												class="badge badge-dark rounded-pill badge-xs">+1</a>
										</div>
									</div>
								</div>
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar avatar-md avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Linda Zimmer</a>
														</h6>
														<span class="fw-semibold">Products the celebs are</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>It seems that recipients are receiving...</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i
													class="ti ti-link me-2"></i>1</span>
										</div>
										<div class="d-flex align-items-center">
											<span class="badge badge-soft-warning me-2"><i
													class="ti ti-square me-1"></i>Work</span>
											<a href="javascript:void(0);"
												class="badge badge-dark rounded-pill badge-xs">+1</a>
										</div>
									</div>
								</div>
								<div class="list-group-item border-bottom p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}"
												class="avatar bg-success avatar-rounded me-2">
												<span class="avatar-title">ER</span>
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Emly Reachel</a>
														</h6>
														<span class="fw-semibold">No Subject</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>Announcing Fake Name Generator Premium</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i
													class="ti ti-folder-open me-2"></i>3</span>
										</div>
										<div class="d-flex align-items-center">
											<span class="badge badge-soft-info"><i
													class="ti ti-square me-1"></i>Projects</span>
										</div>
									</div>
								</div>
								<div class="list-group-item p-3">
									<div class="d-flex align-items-center mb-2">
										<div
											class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="{{url('email-reply')}}" class="avatar avatar-md avatar-rounded me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div class="d-flex align-items-start justify-content-between">
													<div>
														<h6 class="mb-1"><a href="{{url('email-reply')}}">Sean Hill</a>
														</h6>
														<span class="fw-semibold">You’re missing</span>
													</div>
													<div class="d-flex align-items-center">
														<div class="dropdown">
															<button class="btn btn-icon btn-sm rounded-circle"
																type="button" data-bs-toggle="dropdown"
																aria-expanded="false">
																<i class="ti ti-dots"></i>
															</button>
															<ul class="dropdown-menu dropdown-menu-end p-3">
																<li>
																	<a class="dropdown-item rounded-1"
																		href="{{url('email-reply')}}">Open Email</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Reply All</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Forward As
																		Attachment</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mark As
																		Unread</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move to Junk</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Mute</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Delete</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Archive</a>
																</li>
																<li>
																	<a class="dropdown-item rounded-1"
																		href="javascript:void(0);">Move To</a>
																</li>
															</ul>
														</div>
														<span><i class="ti ti-point-filled text-danger"></i>3:13
															PM</span>
													</div>
												</div>
												<p>Regardless, you can usually expect an increase</p>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark me-2"><i
													class="ti ti-folder-open me-2"></i>3</span>
											<span
												class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i
													class="ti ti-photo me-2"></i>+24</span>
										</div>
										<div class="d-flex align-items-center">
											<span><i class="ti ti-star-filled text-warning"></i></span>
											<span class="badge badge-soft-info mx-2"><i
													class="ti ti-square me-1"></i>Applications</span>
											<a href="javascript:void(0);"
												class="badge badge-dark rounded-pill badge-xs">+1</a>
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