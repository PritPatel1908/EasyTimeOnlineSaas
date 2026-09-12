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
					<h2 class="mb-1">Leads</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								CRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Leads Grid</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
							<a href="{{url('leads')}}" class="btn btn-icon btn-sm me-1"><i
									class="ti ti-list-tree"></i></a>
							<a href="{{url('leads-grid')}}" class="btn btn-icon btn-sm active bg-primary text-white"><i
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_leads"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Lead</a>
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

			<!-- Leads Grid -->
			<div class="card">
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between">
						<h5>Leads Grid</h5>
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


			<!-- Leads Kanban -->
			<div class="d-flex overflow-x-auto align-items-start mb-4">
				<div class="kanban-list-items bg-white">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<h4 class="fw-semibold d-flex align-items-center mb-1"><i
											class="ti ti-circle-filled fs-8 text-warning me-2"></i>Contacted
									</h4>
									<span class="fw-medium text-default">02 Leads - $7,50,000</span>
								</div>
								<div class="d-flex align-items-center">
									<div class="action-icon d-inline-flex">
										<a href="javascript:void(0);"><i class="ti ti-circle-plus"></i></a>
										<a href="#" class="" data-bs-toggle="modal" data-bs-target="#edit_leads"><i
												class="ti ti-edit"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
												class="ti ti-trash"></i></a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="kanban-drag-wrap pt-4">
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-warning border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">SM</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Linda White</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$03,50,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											linda@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(193) 7839 748
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Austin, United States
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-04.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-warning border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">CJ</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Chris Johnson</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$3,50,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											chris@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(162) 8920 713
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Atlanta, United States
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-07.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="kanban-list-items bg-white">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<h4 class="fw-semibold d-flex align-items-center mb-1"><i
											class="ti ti-circle-filled fs-8 text-purple me-2"></i>Not
										Contacted</h4>
									<span class="fw-medium text-default">02 Leads - $7,60,000</span>
								</div>
								<div class="d-flex align-items-center">
									<div class="action-icon d-inline-flex">
										<a href="javascript:void(0);"><i class="ti ti-circle-plus"></i></a>
										<a href="#" class="" data-bs-toggle="modal" data-bs-target="#edit_leads"><i
												class="ti ti-edit"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
												class="ti ti-trash"></i></a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="kanban-drag-wrap pt-4">
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-purple border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">EJ</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Emily Johnson</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$3,50,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											emily@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(179) 7382 829
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Newyork, United States
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-06.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-purple border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">MG</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Maria Garcia</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$4,10,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											maria@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(120) 3728 039
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Denver, United States
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-05.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="kanban-list-items bg-white">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<h4 class="fw-semibold d-flex align-items-center mb-1"><i
											class="ti ti-circle-filled fs-8 text-success me-2"></i>Closed
									</h4>
									<span class="fw-medium text-default">45 Leads - $15,44,540</span>
								</div>
								<div class="d-flex align-items-center">
									<div class="action-icon d-inline-flex">
										<a href="javascript:void(0);"><i class="ti ti-circle-plus"></i></a>
										<a href="#" class="" data-bs-toggle="modal" data-bs-target="#edit_leads"><i
												class="ti ti-edit"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
												class="ti ti-trash"></i></a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="kanban-drag-wrap pt-4">
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-success border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">JS</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">John Smith</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$3,20,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											john@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(123) 4567 890
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Chester, United Kingdom
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-01.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-success border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">DL</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">David Lee</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$3,10,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											david@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(183) 9302 890
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Charlotte, United States
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-08.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div class="card kanban-card mb-0">
								<div class="card-body">
									<div class="d-block">
										<div class="border-success border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">RM</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Robert Martinez</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$4,50,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											robert@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(163) 2459 315
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Bristol, United Kingdom
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-09.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="kanban-list-items bg-white me-0">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<h4 class="fw-semibold d-flex align-items-center mb-1"><i
											class="ti ti-circle-filled fs-8 text-danger me-2"></i>Lost</h4>
									<span class="fw-medium text-default">15 Leads - $14,89,543</span>
								</div>
								<div class="d-flex align-items-center">
									<div class="action-icon d-inline-flex">
										<a href="javascript:void(0);"><i class="ti ti-circle-plus"></i></a>
										<a href="#" class="" data-bs-toggle="modal" data-bs-target="#edit_leads"><i
												class="ti ti-edit"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
												class="ti ti-trash"></i></a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="kanban-drag-wrap pt-4">
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-danger border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">MB</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Michael Brown</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$4,10,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											micael@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(184) 2719 738
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											London, United Kingdom
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-03.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div class="card kanban-card">
								<div class="card-body">
									<div class="d-block">
										<div class="border-danger border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">KD</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">Karen Davis</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$4,00,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											darleeo@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(163) 2459 315
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Detroit, United States
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-02.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div>
							<div class="card kanban-card mb-0">
								<div class="card-body">
									<div class="d-block">
										<div class="border-danger border border-2 mb-3"></div>
										<div class="d-flex align-items-center mb-3">
											<a href="{{url('leads-details')}}"
												class="avatar avatar-lg bg-gray flex-shrink-0 me-2"><span
													class="avatar-title text-dark">JA</span></a>
											<h6 class="fw-medium"><a href="{{url('leads-details')}}">James Anderson</a>
											</h6>
										</div>
									</div>
									<div class="mb-3 d-flex flex-column">
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-report-money text-dark me-1"></i>
											$3,40,000
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-mail text-dark me-1"></i>
											james@gmail.com
										</p>
										<p class="text-default d-inline-flex align-items-center mb-2">
											<i class="ti ti-phone text-dark me-1"></i>
											(168) 8392 823
										</p>
										<p class="text-default d-inline-flex align-items-center">
											<i class="ti ti-map-pin-pin text-dark me-1"></i>
											Manchester, United Kingdom
										</p>
									</div>
									<div
										class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
										<a href="javascript:void(0);"
											class="avatar avatar-sm  avatar-rounded flex-shrink-0 me-2"><img
												src="{{URL::asset('build/img/company/company-03.svg')}}" alt="image"></a>
										<div class="icons-social d-flex align-items-center">
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-phone-call"></i></a>
											<a href="#"
												class="d-flex align-items-center justify-content-center me-2"><i
													class="ti ti-brand-hipchat"></i></a>
											<a href="#" class="d-flex align-items-center justify-content-center"><i
													class="ti ti-color-swatch"></i></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Leads Kanban -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection