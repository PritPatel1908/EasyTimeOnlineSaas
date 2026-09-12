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
					<h2 class="mb-1">Pipeline</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								CRM
							</li>
							<li class="breadcrumb-item active" aria-current="page">Pipeline List</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
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
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_pipeline"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Pipeline</a>
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

			<!-- Leads List -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Pipeline List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="me-3">
							<div class="input-icon position-relative">
								<span class="input-icon-addon">
									<i class="ti ti-calendar text-gray-9"></i>
								</span>
								<input type="text" class="form-control date-range bookingrange"
									placeholder="dd/mm/yyyy - dd/mm/yyyy">
							</div>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Stage
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Won</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">In Pipeline</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Conversation</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Follow Up</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								$0.00 - $0.00
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">$10 - $20</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">$20 - $30</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">$40 - $50</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">InActive</a>
								</li>
							</ul>
						</div>
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
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
				<div class="card-body p-0">
					<div class="custom-datatable-filter table-responsive">
						<table class="table datatable">
							<thead class="thead-light">
								<tr>
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Pipeline Name</th>
									<th>Total Deal Value</th>
									<th>No of Deals</th>
									<th>Stages</th>
									<th>Created Date</th>
									<th>Status</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Sales</h6>
									</td>
									<td>
										$4,50,000
									</td>
									<td>315</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-success" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Won</span>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Marketing</h6>
									</td>
									<td>
										$3,15,000
									</td>
									<td>447</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-purple" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">In Pipeline</span>
										</div>
									</td>
									<td>21 Jan 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Calls</h6>
									</td>
									<td>
										$8,40,000
									</td>
									<td>654</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-success" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Won</span>
										</div>
									</td>
									<td>20 Feb 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Email</h6>
									</td>
									<td>
										$6,10,000
									</td>
									<td>545</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-skyblue" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Conversation</span>
										</div>
									</td>
									<td>15 Mar 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Chats</h6>
									</td>
									<td>
										$4,70,000
									</td>
									<td>787</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-skyblue" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Won</span>
										</div>
									</td>
									<td>12 Apr 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Operational</h6>
									</td>
									<td>
										$5,50,000
									</td>
									<td>787</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-warning" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Follow Up</span>
										</div>
									</td>
									<td>20 Apr 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Collabrative</h6>
									</td>
									<td>
										$5,00,000
									</td>
									<td>315</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-success" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Won</span>
										</div>
									</td>
									<td>06 Jul 2024</td>
									<td><span class="badge badge-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inactive </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Differentiate</h6>
									</td>
									<td>
										$4,50,000
									</td>
									<td>478</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-pink" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Schedule servise</span>
										</div>
									</td>
									<td>02 Sep 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Interact</h6>
									</td>
									<td>
										$6,20,000
									</td>
									<td>664</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-success" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Won</span>
										</div>
									</td>
									<td>15 Nov 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<h6 class="fs-14 fw-medium">Identify</h6>
									</td>
									<td>
										$7,40,000
									</td>
									<td>128</td>
									<td>
										<div class=" d-flex align-items-center">
											<div class="progress me-2" role="progressbar" aria-label="Basic example"
												aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
												style="height: 5px; min-width: 80px;">
												<div class="progress-bar bg-danger" style="width: 100%"></div>
											</div>
											<span class="fs-14 fw-normal">Lost</span>
										</div>
									</td>
									<td>10 Dec 2024</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Active </span>
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_pipeline"><i class="ti ti-edit"></i></a>
											<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
													class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- /Leads List -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection