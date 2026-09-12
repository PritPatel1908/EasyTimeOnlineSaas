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
					<h2 class="mb-1">Companies</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								CRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Companies Grid</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('companies-crm')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('companies-grid')}}"
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
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_company"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Company</a>
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
					<div class="d-flex align-items-center justify-content-between">
						<h5>Companies Grid</h5>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-12.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">BrightWave Innovations</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
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
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-13.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Stellar Dynamics</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-14.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Quantum Nexus</a></h6>
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
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-04.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									vaughan@example.com
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-15.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">EcoVision Enterprises</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}"
											alt="img">
									</span>
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
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-12.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									jessica@example.com
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-16.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Aurora Technologies</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-13.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-14.jpg')}}"
											alt="img">
									</span>
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
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-17.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">BlueSky Ventures</a></h6>
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
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-21.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
							</div>
							<div class="d-flex flex-column">
								<p class="text-dark d-inline-flex align-items-center mb-2">
									<i class="ti ti-mail-forward text-gray-5 me-2"></i>
									dawn@example.com
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-18.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">TerraFusion Energy</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-25.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-26.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-27.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-19.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">UrbanPulse Design</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-28.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-30.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-20.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Nimbus Networks</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-11.jpg')}}"
											alt="img">
									</span>
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
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
										class="ti ti-star-filled text-warning me-1"></i>4.4</span>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-21.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Epicurean Delights</a></h6>
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
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-19.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-22.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Hermann Groups</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-20.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-21.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-22.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-23.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-24.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
									<a href="{{url('company-details')}}"
										class="avatar avatar-xl avatar-rounded online border rounded-circle">
										<img src="{{URL::asset('build/img/company/company-23.svg')}}" class="img-fluid h-auto w-auto"
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
												data-bs-toggle="modal" data-bs-target="#edit_company"><i
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
								<h6 class="mb-1"><a href="{{url('company-details')}}">Beacon Softwares</a></h6>
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-25.jpg')}}"
											alt="img">
									</span>
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
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-29.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-primary avatar-rounded text-fixed-white fs-12"
										href="javascript:void(0);">
										+1
									</a>
								</div>
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
										class="ti ti-star-filled text-warning me-1"></i>4.2</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="text-center mb-4">
				<a href="#" class="btn btn-white border"><i class="ti ti-loader-3 text-primary me-2"></i>Load
					More</a>
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection