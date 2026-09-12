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
					<h2 class="mb-1">Budgets</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Accounting
							</li>
							<li class="breadcrumb-item active" aria-current="page">Budgets</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_budgets"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Budget</a>
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

			<!-- Budgets list -->
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Budget List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

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
									<th>Budget Title</th>
									<th>Budget Type</th>
									<th>Start Date</th>
									<th>End Date</th>
									<th>Total Revenue</th>
									<th>Total Expense</th>
									<th>Tax Amount</th>
									<th>Budget Amount</th>
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
										<h6 class="fw-medium"><a href="javascript:void(0);">Office Supplies</a></h6>
									</td>
									<td>
										Category
									</td>
									<td>
										14 Jan 2024
									</td>
									<td>
										13 Nov 2024
									</td>
									<td>
										250000
									</td>
									<td>
										150000
									</td>
									<td>
										10000
									</td>
									<td>
										90000
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_budgets"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
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
										<h6 class="fw-medium"><a href="javascript:void(0);">Recruitment</a></h6>
									</td>
									<td>
										Category
									</td>
									<td>
										21 Jan 2024
									</td>
									<td>
										20 Nov 2024
									</td>
									<td>
										300000
									</td>
									<td>
										200000
									</td>
									<td>
										15000
									</td>
									<td>
										85000
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_budgets"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
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
										<h6 class="fw-medium"><a href="javascript:void(0);">Tender</a></h6>
									</td>
									<td>
										Project
									</td>
									<td>
										10 Feb 2024
									</td>
									<td>
										08 Dec 2024
									</td>
									<td>
										200000
									</td>
									<td>
										170000
									</td>
									<td>
										5000
									</td>
									<td>
										25000
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_budgets"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
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
										<h6 class="fw-medium"><a href="javascript:void(0);">Salary 2024</a></h6>
									</td>
									<td>
										Category
									</td>
									<td>
										18 Feb 2024
									</td>
									<td>
										16 Dec 2024
									</td>
									<td>
										300000
									</td>
									<td>
										200000
									</td>
									<td>
										15000
									</td>
									<td>
										85000
									</td>
									<td>
										<div class="action-icon d-inline-flex">
											<a href="#" class="me-2" data-bs-toggle="modal"
												data-bs-target="#edit_budgets"><i class="ti ti-edit"></i></a>
											<a href="javascript:void(0);" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- /Budgets list -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection