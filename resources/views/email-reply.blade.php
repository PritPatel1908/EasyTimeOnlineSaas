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
				<div class="mail-detail bg-white border-bottom p-3">
					<div data-simplebar class="active slimscroll h-100">
						<div class="slimscroll-active-sidebar">
							<div
								class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 border-bottom mb-3 pb-3">
								<div class="dropdown">
									<button class="btn btn-white border dropdown-toggle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<span class="badge badge-dark rounded-circle badge-xs me-1">5</span>
										Peoples
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1"
												href="javascript:void(0);">Peoples</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);">Rufana</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);">Sean
												Hill</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);">Cameron
												Drake</a>
										</li>
									</ul>
								</div>
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-arrow-back-up"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-arrow-back-up-double"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-arrow-forward"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-bookmarks-filled"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-archive-filled"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-mail-opened-filled"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-printer"></i></a>
									<a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i
											class="ti ti-star-filled text-warning"></i></a>
								</div>
							</div>
							<div class="bg-light-500 rounded p-3 mb-3">
								<div class="d-flex align-items-center flex-fill border-bottom mb-3 pb-3">
									<a href="javascript:void(0);"
										class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
										<img src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}" alt="Img">
									</a>
									<div class="flex-fill">
										<div
											class="d-flex align-items-start justify-content-between flex-wrap row-gap-2">
											<div>
												<h6 class="mb-1"><a href="javascript:void(0);">Angela Thomas</a>
												</h6>
												<p>Subject: Client Dashboard</p>
											</div>
											<div class="d-flex align-items-center">
												<p class="me-2 mb-0">12:45 AM</p>
												<a href="javascript:void(0);"
													class="btn btn-icon btn-sm rounded-circle"><i
														class="ti ti-arrow-back-up"></i></a>
												<a href="javascript:void(0);"
													class="btn btn-icon btn-sm rounded-circle"><i
														class="ti ti-arrow-back-up-double"></i></a>
												<a href="javascript:void(0);"
													class="btn btn-icon btn-sm rounded-circle"><i
														class="ti ti-printer"></i></a>
											</div>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center flex-wrap row-gap-2">
									<p class="fs-12 mb-0 text-dark me-3"><span class="text-gray">From: </span> Arman
										Janes</p>
									<p class="fs-12 mb-0 text-dark me-3"><span class="text-gray">To: </span> Angela
										Thomas</p>
									<p class="fs-12 mb-0 text-dark"><span class="text-gray">Cc: </span> Angela
										Thomas, Justin Lapointe</p>
								</div>
							</div>
							<div class="card shadow-none">
								<div class="card-body">
									<div>
										<h6 class="mb-3">Dear Angela</h6>
										<p class="text-dark">I am writing to request a meeting to discuss the
											progress and next steps for Project.
											We have reached a critical milestone, and I believe a
											discussion will help align our efforts and ensure we are on track to
											meet our goals.
										</p>
										<p class="text-dark">
											am available on Tuesday and Thursday afternoons,
											but I am flexible and can adjust to a time that suits you best
										</p>
										<p class="text-dark">
											Looking forward to your response.
										</p>
										<p class="text-dark">Best regards, <br><b
												class="fw-medium d-flex mt-1">Arman</b> </p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between my-3 pt-3 border-top">
										<h5>Attachments</h5>
										<a href="javascript:void(0);" class="text-primary fw-medium">Download
											All</a>
									</div>
									<div class="d-flex align-items-center email-attach">
										<a href="{{URL::asset('build/img/media/email-attach-big-01.jpg')}}" class="glightbox avatar avatar-xl me-3 gallery-item" data-gallery="gallery-group">
											<img src="{{URL::asset('build/img/media/email-attach-01.jpg')}}" class=" rounded"
												alt="img">
											<span class="avatar avatar-md avatar-rounded"><i
													class="ti ti-eye"></i></span>
										</a>
										<a href="{{URL::asset('build/img/media/email-attach-big-02.jpg')}}" class="glightbox avatar avatar-xl me-3 gallery-item" data-gallery="gallery-group">
											<img src="{{URL::asset('build/img/media/email-attach-02.jpg')}}" class="rounded"
												alt="img">
											<span class="avatar avatar-md avatar-rounded"><i
													class="ti ti-eye"></i></span>
										</a>
										<a href="{{URL::asset('build/img/media/email-attach-big-03.jpg')}}" class="glightbox avatar avatar-xl me-3 gallery-item" data-gallery="gallery-group">
											<img src="{{URL::asset('build/img/media/email-attach-03.jpg')}}" class="rounded"
												alt="img">
											<span class="avatar avatar-md avatar-rounded"><i
													class="ti ti-eye"></i></span>
										</a>
									</div>
								</div>
							</div>
							<div class="card shadow-none">
								<div class="card-body">
									<div class="bg-light-500 rounded p-3 mb-3">
										<div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
											<a href="javascript:void(0);"
												class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}" alt="Img">
											</a>
											<div class="flex-fill">
												<div
													class="d-flex align-items-start justify-content-between flex-wrap row-gap-2">
													<div>
														<h6 class="mb-1"><a href="javascript:void(0);">Arman
																Janes</a></h6>
														<div class="d-flex align-items-center">
															<p class="mb-0"><span>To: </span> Me</p>
															<div class="dropdown">
																<button
																	class="btn btn-icon dropdown-toggle bg-transparent text-dark border-0 p-0 btn-sm"
																	type="button" data-bs-toggle="dropdown"
																	aria-expanded="false">
																</button>
																<ul class="dropdown-menu dropdown-menu-end">
																	<li>
																		<a class="dropdown-item"
																			href="javascript:void(0);"><span
																				class="text-gray">From :</span>
																			Arman Janes arman343@example.com</a>
																	</li>
																	<li>
																		<a class="dropdown-item"
																			href="javascript:void(0);"><span
																				class="text-gray">To :</span> Angela
																			Thomas ange4565@example.com</a>
																	</li>
																	<li>
																		<a class="dropdown-item"
																			href="javascript:void(0);"><span
																				class="text-gray">Date :</span> 12
																			May 2025, 09:45 PM </a>
																	</li>
																</ul>
															</div>
														</div>
													</div>
													<div class="d-flex align-items-center">
														<p class="me-2 mb-0">Yesterday 01:22 AM</p>
														<a href="javascript:void(0);"
															class="btn btn-icon btn-sm rounded-circle"><i
																class="ti ti-arrow-back-up"></i></a>
														<a href="javascript:void(0);"
															class="btn btn-icon btn-sm rounded-circle"><i
																class="ti ti-arrow-back-up-double"></i></a>
														<a href="javascript:void(0);"
															class="btn btn-icon btn-sm rounded-circle"><i
																class="ti ti-printer"></i></a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div>
										<h6 class="mb-2">Dear Arman</h6>
										<p class="text-dark">Introduction Mail from Techsolutions!!!</p>
										<p class="text-dark">Best regards <br><b
												class="fw-medium d-inline-flex mt-1">Arman</b></p>
									</div>
									<form action="{{url('email')}}">
										<div class="border rounded mt-3">
											<div class="p-3 position-relative pb-2 border-bottom">
												<div class="tag-with-img d-flex align-items-center">
													<label class="form-label me-2">To</label>
													<input class="input-tags form-control border-0 h-100"
														id="inputBox" type="text" data-role="tagsinput" name="Label"
														value="Angela Thomas">
												</div>
												<div class="d-flex align-items-center email-cc">
													<a href="javascript:void(0);" class="d-inline-flex me-2">Cc</a>
													<a href="javascript:void(0);" class="d-inline-flex">Bcc</a>
												</div>
											</div>
											<div class="p-3">
												<div class="mb-3">
													<textarea rows="2" class="form-control border-0 p-0"></textarea>
												</div>
											</div>
											<div
												class="d-flex align-items-center justify-content-between border-top p-3">
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-paperclip"></i></a>
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-photo"></i></a>
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-link"></i></a>
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-pencil"></i></a>
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-mood-smile"></i></a>
												</div>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-calendar-repeat"></i></a>
													<a href="javascript:void(0);"
														class="btn btn-icon btn-sm rounded-circle"><i
															class="ti ti-trash"></i></a>
													<button type="submit"
														class="btn btn-primary d-inline-flex align-items-center ms-2">Send
														<i class="ti ti-arrow-right ms-2"></i></button>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="text-center">
								<a href="javascript:void(0);" class="btn btn-dark btn-sm">View Older Messages</a>
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