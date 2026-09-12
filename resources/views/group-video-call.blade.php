@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content pb-4">

			<!-- Breadcrumb -->
			<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">Group Video Call</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Applications
							</li>
							<li class="breadcrumb-item active" aria-current="page">Group Video Call</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="input-icon-start position-relative d-inline-flex me-1">
							<span class="input-icon-addon">
								<i class="ti ti-search"></i>
							</span>
							<input type="text" class="form-control" placeholder="Search Notes">
						</div>
					</div>
					<div class="mb-2">
						<a href="#" class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add People</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="d-flex row-gap-3">
				<div class="main-img flex-fill">
					<div class="call-img ">
						<img src="{{URL::asset('build/img/call/call-01.jpg')}}" class="img-fluid rounded w-100" alt="img">
						<div class="call-overlay-top w-100">
							<div class="d-flex justify-content-between align-items-center">
								<div class="d-flex">
									<a href="#"
										class="pin-icon d-flex justify-content-center align-items-center rounded-circle me-2"><i
											class="ti ti-pinned"></i></a>
									<span class="badge badge-dark d-flex align-items-center">John L. Hoover</span>
								</div>
								<div class="d-flex justify-content-between align-items-center">
									<span class="badge badge-light d-flex align-items-center text-dark me-2"><i
											class="ti ti-point-filled"></i>10:15</span>
									<a href="javascript:void(0);"
										class="pin-icon win-maximize bg-light d-flex justify-content-center align-items-center text-gray rounded-circle"><i
											class="ti ti-maximize"></i></a>
								</div>
							</div>
						</div>
						<div
							class="call-overlay-bottom d-flex justify-content-sm-between align-items-center flex-wrap w-100">
							<a href="javascript:void(0);"
								class="options-icon d-flex align-items-center justify-content-center guest-off rounded"><i
									class="ti ti-user-off"></i></a>
							<div class="call-option rounded-pill d-flex justify-content-center align-items-center">
								<a href="javascript:void(0);"
									class="options-icon bg-light d-flex justify-content-center align-items-center rounded me-2"><i
										class="ti ti-microphone"></i></a>
								<a href="javascript:void(0);"
									class="options-icon bg-light d-flex justify-content-center align-items-center rounded me-2"><i
										class="ti ti-video"></i></a>
								<a href="javascript:void(0);"
									class="options-icon bg-light d-flex justify-content-center align-items-center rounded me-2"><i
										class="ti ti-message"></i></a>
								<a href="javascript:void(0);"
									class="call-icon bg-danger d-flex justify-content-center align-items-center rounded"><i
										class="ti ti-phone"></i></a>
								<a href="javascript:void(0);"
									class="options-icon bg-light d-flex justify-content-center align-items-center rounded mx-2"><i
										class="ti ti-volume"></i></a>
								<a href="javascript:void(0);"
									class="options-icon bg-light d-flex justify-content-center align-items-center rounded me-2"><i
										class="ti ti-mood-smile"></i></a>
								<a href="javascript:void(0);"
									class="options-icon bg-light d-flex justify-content-center align-items-center rounded"><i
										class="ti ti-device-imac-share"></i></a>
							</div>
							<a href="javascript:void(0);"
								class="options-icon bg-light d-flex align-items-center justify-content-center rounded"
								id="show-message"><i class="ti ti-dots"></i></a>
						</div>
					</div>
				</div>
				<div class="chat-active-users">
					<div class="others guests">
						<div class="call-img mb-3">
							<img src="{{URL::asset('build/img/call/call-02.jpg')}}" class="img-fluid rounded" alt="img">
							<div class="call-overlay-sidetop">
								<a href="#" class="pin-icon bg-light rounded-circle"><i
										class="ti ti-pinned"></i></a>
							</div>
							<div
								class="call-overlay-sidedown d-flex justify-content-center align-items-center w-100">
								<span class="badge badge-light badge-sm  text-dark fs-10">Joanne Conner</span>
							</div>
						</div>
						<div class="call-img mb-3">
							<img src="{{URL::asset('build/img/call/call-03.jpg')}}" class="img-fluid rounded" alt="img">
							<div
								class="call-overlay-sidedown d-flex justify-content-center align-items-center w-100">
								<span class="badge badge-light badge-sm  text-dark fs-10">Thomas Benson</span>
							</div>
						</div>
						<div class="call-img mb-3">
							<img src="{{URL::asset('build/img/call/call-04.jpg')}}" class="img-fluid rounded" alt="img">
							<div
								class="call-overlay-sidedown d-flex justify-content-center align-items-center w-100">
								<span class="badge badge-light badge-sm  text-dark fs-10">Kallie Amaro</span>
							</div>
						</div>
						<div class="call-img mb-3">
							<img src="{{URL::asset('build/img/call/call-05.jpg')}}" class="img-fluid rounded" alt="img">
							<div
								class="call-overlay-sidedown d-flex justify-content-center align-items-center w-100">
								<span class="badge badge-light badge-sm  text-dark fs-10">Joanne Conner </span>
							</div>
						</div>
						<div class="call-img">
							<img src="{{URL::asset('build/img/call/call-06.jpg')}}" class="img-fluid rounded" alt="img">
							<div
								class="call-overlay-sidedown d-flex justify-content-center align-items-center w-100">
								<span class="badge badge-light badge-sm  text-dark fs-10">Diane Hollis</span>
							</div>
						</div>
					</div>
				</div>
				<div class="right-user-side chat-rooms" id="chat-room">
					<div class="card slime-grp border-0 mb-0">
						<div class="card-header p-3 pb-0 border-0">
							<div class="border-bottom">
								<ul class="nav nav-pills chat-user-tabs align-items-center mb-3" id="pills-tab"
									role="tablist">
									<li class="nav-item flex-fill" role="presentation">
										<button class="nav-link btn-light btn w-100 active" data-bs-toggle="pill"
											data-bs-target="#pills-home" type="button" role="tab"
											aria-selected="true">
											Chat
										</button>
									</li>
									<li class="nav-item" role="presentation">
										<button class="nav-link btn btn-light w-100" data-bs-toggle="pill"
											data-bs-target="#pills-profile" type="button" role="tab"
											aria-selected="false" tabindex="-1">
											Partcipants(10)
										</button>
									</li>
									<li><a href="#"
											class="close_profile close_profile4 avatar avatar-sm mb-0 rounded-circle bg-danger">
											<i class="ti ti-x"></i>
										</a></li>
								</ul>
							</div>
						</div>
						<div data-simplebar class="card-body slimscroll p-3">
							<div class="tab-content">
								<div class="tab-pane fade show active" id="pills-home" role="tabpanel">
									<div class="chat-msg-blk p-0">
										<div class="chats">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Hi Everyone.!</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
										<div class="chats chats-right">
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Good Morning..! Today we have meeting about the new policy.
													</h4>
												</div>
												<div class="chat-profile-name text-end">
													<h6><i class="bx bx-check-double"></i> 10:00</h6>
												</div>
											</div>
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 ms-2">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="image">
											</div>
										</div>
										<div class="chats">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Great.! This is the second new product that comes in this
														week.</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
										<div class="chats">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Nice..which category it belongs to?</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
										<div class="chats">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Great.! This is the second new product that comes in
														this week.</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
										<div class="chats">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Hi.! Good Morning all.</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
										<div class="chats">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Nice..which category it belongs to?</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
										<div class="chats chats-right">
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Good Morning..! Today we have meeting about the new
														product.</h4>
												</div>
												<div class="chat-profile-name text-end">
													<h6><i class="bx bx-check-double"></i> 10:00</h6>
												</div>
											</div>
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 ms-2">
												<img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="image">
											</div>
										</div>
										<div class="chats mb-0">
											<div class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
												<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="image">
											</div>
											<div class="chat-content flex-fill">
												<div class="message-content">
													<h4>Great.! This is the second new product that comes in
														this week.</h4>
												</div>
												<div class="chat-profile-name d-flex justify-content-end">
													<h6>10:00 AM</h6>
												</div>
											</div>
										</div>
									</div>
									<div class="chat-footer">
										<form>
											<div class="smile-col comman-icon">
												<a href="#"><i class="far fa-smile"></i></a>
											</div>
											<div class="attach-col comman-icon">
												<a href="#"><i class="fas fa-paperclip"></i></a>
											</div>
											<div class="micro-col comman-icon">
												<a href="#"><i class="bx bx-microphone"></i></a>
											</div>
											<input type="text" class="form-control chat_form"
												placeholder="Enter Message.....">
											<div class="send-chat comman-icon">
												<a href="#" class="rounded"><i data-feather="send"></i></a>
											</div>
										</form>
									</div>
								</div>
								<div class="tab-pane fade" id="pills-profile" role="tabpanel">
									<div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-11.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Anthony Lewis</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Harvey Smith</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Doglas Martini</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Brian Villalobos</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Linda Ray</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Benjamin</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Kaitlin</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Freda</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Alwin</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
											</div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between bg-light rounded p-2">
											<div class="d-flex align-items-center">
												<a href="#"
													class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
													<img src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}" alt="Img">
												</a>
												<div>
													<h6 class="d-inline-flex align-items-center fw-medium">
														<a href="#">Joseph Collins</a>
													</h6>
												</div>
											</div>
											<div class="d-flex align-items-center">
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-microphone"></i></a>
												<a href="#" class="btn btn-sm btn-icon"><i
														class="ti ti-video"></i></a>
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

    <!-- ========================
        End Page Content
    ========================= -->

@endsection