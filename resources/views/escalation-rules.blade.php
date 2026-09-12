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
					<h2 class="mb-1">Escalation Rules</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Tickets
							</li>
							<li class="breadcrumb-item active" aria-current="page">Escalation Rules</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2 me-2">
						<div class="dropdown">
							<a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-file-export me-1"></i>Export
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="#" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
								</li>
							</ul>
						</div>

					</div>
					<div class="mb-2">
						<a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
							data-bs-target="#add_modal"><i class="ti ti-circle-plus me-2"></i>Add New Rule</a>
					</div>
					<div class="ms-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Escalation Rules List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Priority
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Critical</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">High</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Medium</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Low</a>
								</li>
							</ul>
						</div>
						<div class="dropdown">
							<a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Sort By : Last 7 Days
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="#" class="dropdown-item rounded-1">Recently Added</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Ascending</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Descending</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Last Month</a>
								</li>
								<li>
									<a href="#" class="dropdown-item rounded-1">Last 7 Days</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card-body p-0">
					<div class="custom-datatable-filter table-responsive rounded-0">
						<table class="table">
							<thead class="thead-light">
								<tr>
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Rule ID</th>
									<th>Trigger Type</th>
									<th>Priority</th>
									<th>Condition</th>
									<th>Escalation Level</th>
									<th>Escalates To</th>
									<th>Time Threshold</th>
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
									<td>#ER005</td>
									<td>
										<p class="text-dark fw-medium mb-0">SLA Breach</p>
									</td>
									<td>Critical</td>
									<td>No resolution</td>
									<td>Level 1</td>
									<td>Team Lead</td>
									<td>30 mins</td>
									<td><span
											class="badge badge-success d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Active</span></td>
									<td><span
											class="badge border border-purple text-purple d-inline-flex align-items-center badge-xs">Notify</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>#ER004</td>
									<td>
										<p class="text-dark fw-medium mb-0">SLA Breach</p>
									</td>
									<td>High</td>
									<td>No resolution</td>
									<td>Level 1</td>
									<td>Support Lead</td>
									<td>1 hour</td>
									<td><span
											class="badge badge-success d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Active</span></td>
									<td><span
											class="badge border border-pink text-pink d-inline-flex align-items-center badge-xs">Notify
											+ Assign</span></td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>#ER003</td>
									<td>
										<p class="text-dark fw-medium mb-0">Status Based</p>
									</td>
									<td>Medium</td>
									<td>Ticket in Progress</td>
									<td>Level 1</td>
									<td>Supervisor</td>
									<td>4 hours</td>
									<td><span
											class="badge badge-success d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Active</span></td>
									<td><span
											class="badge border border-danger text-danger d-inline-flex align-items-center badge-xs">Reminder</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>#ER002</td>
									<td>
										<p class="text-dark fw-medium mb-0">Priority Based</p>
									</td>
									<td>Medium</td>
									<td>Ticket not assigned</td>
									<td>Level 1</td>
									<td>Admin</td>
									<td>6 hours</td>
									<td><span
											class="badge badge-success d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Active</span></td>
									<td><span
											class="badge border border-purple text-purple d-inline-flex align-items-center badge-xs">Notify</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>#ER001</td>
									<td>
										<p class="text-dark fw-medium mb-0">Time Based</p>
									</td>
									<td>Low</td>
									<td>Pending too long</td>
									<td>Level 2</td>
									<td>Support Lead</td>
									<td>8 hours</td>
									<td><span
											class="badge badge-success d-inline-flex align-items-center badge-xs"><i
												class="ti ti-point-filled me-1"></i>Active</span></td>
									<td><span
											class="badge border border-pink text-pink d-inline-flex align-items-center badge-xs">Notify
											+ Assign</span></td>
								</tr>
							</tbody>
						</table>
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