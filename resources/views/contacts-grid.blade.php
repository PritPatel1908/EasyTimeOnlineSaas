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
					<h2 class="mb-1">Contacts</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								CRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Contacts Grid</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('contacts')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('contacts-grid')}}" class="btn btn-icon btn-sm active bg-primary text-white"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_contact"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Contact</a>
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

			<!-- Contact Grid -->
			<div class="card">
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between">
						<h5>Contact Grid</h5>
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Sort By : Last 7 Days
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-49.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Darlee Robertson</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Facility Manager</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									darlee@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(163) 2459 315
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Germany
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>4.2</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-50.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Sharon Roy</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Installer</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									sharon@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(146) 1249 296
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									USA
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>5.0</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-51.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Vaughan Lewis</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Senior Manager</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									vaughan@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(135) 3489 516
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Canada
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>3.5</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Jessica Louise</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Test Engineer</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									jessica@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(158) 3459 596
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									India
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>4.5</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-52.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Carol Thomas</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">UI /UX Designer</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									carol@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(196) 4862 196
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									China
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>3.5</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-53.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Dawn Mercha</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">UI /UX Designer</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									carol@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(163) 6498 256
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Japan
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>3.5</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-57.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Rachel Hampton</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Software Developer</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									rachel@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(154) 6481 075
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Indonesia
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>3.1</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-54.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Jonelle Curtiss</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Supervisor</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									jonella@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(184) 6348 195
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Cuba
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>5.0</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Jonathan Smith</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Team Lead Dev</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									jonathan@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(175) 2496 125
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Israel
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>2.7</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Patricia Carter</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Team Lead Dev</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									patricia@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(132) 3145 977
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Colombia
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>3.0</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Jeffrey Jarrett</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Team Lead Dev</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									jeffrey@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(167) 4526 5496
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Iran
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>4.6</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="form-check form-check-md">
									<input class="form-check-input" type="checkbox">
								</div>
								<div>
									<a href="{{url('contact-details')}}"
										class="avatar avatar-xl avatar-rounded online border p-1 border-primary rounded-circle">
										<img src="{{URL::asset('build/img/users/user-24.jpg')}}" class="img-fluid h-auto w-auto"
											alt="img">
									</a>
								</div>
								<div class="dropdown">
									<button class="btn btn-icon btn-sm rounded-circle" type="button"
										data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end p-3">
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#edit_contact"><i
													class="ti ti-edit me-1"></i>Edit</a>
										</li>
										<li>
											<a class="dropdown-item rounded-1" href="javascript:void(0);"
												data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash me-1"></i>Delete</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="text-center mb-3">
								<h6 class="mb-1"><a href="{{url('contact-details')}}">Gloria Rubio</a></h6>
								<span class="badge bg-pink-transparent fs-10 fw-medium">Team Lead Dev</span>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									gloria@example.com
								</p>
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-phone text-gray-5 me-2"></i>
									(134) 7589 6348
								</p>
								<p class="text-dark d-inline-flex align-items-center">
									<i class="ti ti-map-pin text-gray-5 me-2"></i>
									Brazil
								</p>
							</div>
							<div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
								<div class="icons-social d-flex align-items-center">
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-mail"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-phone-call"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-message-2"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
											class="ti ti-brand-skype"></i></a>
									<a href="#" class="avatar avatar-rounded avatar-sm"><i
											class="ti ti-brand-facebook"></i></a>
								</div>
								<span class="d-inline-flex align-items-center"><i
										class="ti ti-star-filled text-warning me-1"></i>4.1</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Contact Grid -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection