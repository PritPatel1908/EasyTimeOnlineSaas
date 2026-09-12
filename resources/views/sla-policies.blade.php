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
					<h2 class="mb-1">SLA Policies</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Tickets
							</li>
							<li class="breadcrumb-item active" aria-current="page">SLA Policies</li>
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
							data-bs-target="#add_modal"><i class="ti ti-circle-plus me-2"></i>Add New Policy</a>
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
					<h5>SLA Policies List</h5>
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
									<th>Priority</th>
									<th>Description</th>
									<th>First Response Time</th>
									<th>Resolution Time</th>
									<th>Escalation Time</th>
									<th>Escalates To</th>
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
										<p class="text-dark fw-medium mb-0">Critical</p>
									</td>
									<td>System wide outage affecting all tenants</td>
									<td>1 Hour</td>
									<td>2 – 4 Hours</td>
									<td>1 hour</td>
									<td>Support Lead</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<p class="text-dark fw-medium mb-0">High</p>
									</td>
									<td>Core module failure affecting primary workflow</td>
									<td>4 Hours</td>
									<td>8 Hours</td>
									<td>2 hours</td>
									<td>Support Lead</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<p class="text-dark fw-medium mb-0">Medium</p>
									</td>
									<td>Minor issue affecting a limited user group</td>
									<td>8 Hours</td>
									<td>24 Hours</td>
									<td>4 hours</td>
									<td>Support Team</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<p class="text-dark fw-medium mb-0">Low</p>
									</td>
									<td>Low impact problem affecting few users</td>
									<td>24 Hours</td>
									<td>1 - 2 Days</td>
									<td>6 hours</td>
									<td>Support Team</td>
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