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
					<h2 class="mb-1">Asset Management</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">Asset Management</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
					<a href="#" class="btn btn-white me-2 mb-2"> <i class="ti ti-user"></i> Add Assignee</a>
					<a href="#" class="btn btn-secondary mb-2 me-2"> <i class="ti ti-calendar"></i> Schedule
						Maintainance</a>
					<a href="#" class="btn btn-primary-gradient mb-2"> <i class="ti ti-plus"></i> Add New Job</a>
					<div class="ms-2 mb-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- start row -->
			<div class="row">
				<div class="col-xl-8">
					<!-- Candidate Hiring Analysis -->
					<div class="card position-relative analysis-card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-3">
								<h3 class="mb-0 card-title">Purchase Trend</h3>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Weekly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Yearly
											</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="mb-1 d-flex align-items-center gap-2 flex-wrap">
								<h4 class="mb-0">654</h4>
								<div
									class="border rounded-pill  bg-light fs-13 text-gray-9 d-flex align-items-center gap-2 p-2px ps-2">
									+18%
									<p
										class="btn btn-sm btn-icon pe-none bg-success rounded-circle d-flex align-items-center justify-content-center  text-white">
										<i class="ti ti-arrow-up-right fs-20"></i>
									</p>
								</div>
								<p class="mb-0 fs-14">vs last year</p>
							</div>

							<div id="purchase-trend" style="position:relative"></div>

						</div>
					</div>
				</div>

				<div class="col-xl-4 d-flex flex-column">
					<!-- Salary Range Distribution -->
					<div class="card">
						<div class="card-body">
							<div class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3">
								<h5 class="mb-2">Assets by Department</h5>
								<a href="#"><i class="ti ti-refresh fs-20 text-secondary"></i></a>
							</div>

							<div id="assets-department"></div>

						</div>
					</div>
				</div>

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">


				<div class="col-xl-4">
					<div class="card">
						<div class="card-body">
							<div class="mb-3 d-flex align-items-center justify-content-between flex-wrap">
								<p class="mb-0 d-flex align-items-center"><i
										class="ti ti-device-desktop text-primary fs-20 me-1"></i> Total Assets</p>
								<p class="d-flex align-items-center fs-16 text-success fw-bold"><i
										class="ti ti-square-rounded-chevron-up me-1"></i>9.25%</p>
							</div>
							<h2>1,247</h2>
							<div id="total-assets"></div>
						</div>
					</div>
				</div>
				<!-- end col -->

				<div class="col-xl-4 ">
					<div class="card">
						<div class="card-body">
							<div class="mb-3 d-flex align-items-center justify-content-between flex-wrap">
								<p class="mb-0 d-flex align-items-center"><i
										class="ti ti-user-star text-secondary fs-20 me-1"></i> Assets Assigned</p>
								<p class="d-flex align-items-center fs-16 text-success fw-bold"><i
										class="ti ti-square-rounded-chevron-up me-1"></i>9.25%</p>
							</div>
							<h2>892</h2>
							<div id="assets-assigned"></div>
						</div>
					</div>
				</div>
				<!-- end col -->

				<div class="col-xl-4 ">
					<div class="card">
						<div class="card-body">
							<div class="mb-3 d-flex align-items-center justify-content-between flex-wrap">
								<p class="mb-0 d-flex align-items-center"><i
										class="ti ti-cylinder-plus text-purple fs-20 me-1"></i> Assets Available</p>
								<p class="d-flex align-items-center fs-16 text-danger fw-bold"><i
										class="ti ti-square-rounded-chevron-down me-1"></i>9.25%</p>
							</div>
							<h2>287</h2>
							<div id="assets-available"></div>
						</div>
					</div>
				</div>
				<!-- end col -->

			</div>
			<!-- end row -->

			<div class="row">
				<div class="col-xl-5">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-3">
								<h3 class="mb-0 card-title">Asset Value</h3>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Weekly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="mb-1 d-flex align-items-center gap-2 flex-wrap mb-3">
								<h4 class="mb-0">$2.4M</h4>
								<div
									class="border rounded-pill bg-light fs-13 text-gray-9 d-flex align-items-center gap-2 p-2px ps-2">
									+22%
									<p
										class="btn pe-none btn-sm btn-icon bg-success rounded-circle d-flex align-items-center justify-content-center text-white">
										<i class="ti ti-arrow-up-right fs-20 "></i>
									</p>
								</div>
								<p class="mb-0 fs-14">vs last month</p>
							</div>
							<div class="d-flex align-items-center justify-content-between px-2 asset-value-lable">
								<p class="fs-12 m-0 fw-semibold">0</p>
								<p class="fs-12 m-0 fw-semibold">2M</p>
								<p class="fs-12 m-0 fw-semibold">4M</p>
							</div>
							<div id="asset-value"></div>
						</div>
					</div>

					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-3">
								<h3 class="mb-0 card-title">Depreciated Value</h3>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Monthly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="d-flex justify-content-between align-items-center">
								<div class="mb-1 d-flex flex-column gap-2">
									<h4 class="mb-0">$1.8M</h4>
									<div class="d-flex align-items-center gap-2">
										<div
											class="border rounded-pill bg-light fs-13 text-gray-9 d-flex align-items-center gap-2 p-2px ps-2">
											+22% <p
												class="btn pe-none btn-sm btn-icon bg-danger rounded-circle d-flex align-items-center justify-content-center  text-white">
												<i class="ti ti-arrow-down-right fs-20"></i>
											</p>
										</div>
										<p class="mb-0 fs-14">vs last month</p>
									</div>
								</div>
								<div id="depreciated-value"></div>
							</div>

						</div>
					</div>
				</div>
				<div class="col-xl-7 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-4">
								<h3 class="mb-0 card-title">Assets by Category</h3>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Monthly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="row d-flex align-items-center justify-content-center ">
								<div class="col-md-6">
									<div id="asset-categories-chart"></div>
								</div>

								<div class="col-md-6">
									<div class="asset-list">

										<div class="asset-item border rounded-pill">
											<div class="bar">
												<div class="fill fill-40">
													<span class="percent">40%</span>
												</div>
												<span class="label">Laptops</span>
											</div>
										</div>

										<div class="asset-item border rounded-pill">
											<div class="bar">
												<div class="fill fill-20">
													<span class="percent">20%</span>
												</div>
												<span class="label">Mouse</span>
											</div>
										</div>

										<div class="asset-item border rounded-pill">
											<div class="bar">
												<div class="fill fill-15">
													<span class="percent">15%</span>
												</div>
												<span class="label">Writing Pad</span>
											</div>
										</div>

										<div class="asset-item border rounded-pill">
											<div class="bar">
												<div class="fill fill-15">
													<span class="percent">15%</span>
												</div>
												<span class="label">Keyboard</span>
											</div>
										</div>

										<div class="asset-item border rounded-pill">
											<div class="bar">
												<div class="fill fill-5">
													<span class="percent">5%</span>
												</div>
												<span class="label">Chairs</span>
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>


			<div class="card job-opening-table">
				<div class="card-body">

					<div class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3">
						<h3 class="mb-0 card-title">Active Job Openings</h3>
						<a href="#" class="btn btn-primary-gradient btn-sm d-inline-flex align-items-center">
							View All
						</a>
					</div>

					<div class="mb-4 d-flex align-items-center gap-2">
						<div class="input-group input-group-flat d-inline-flex w-100">
							<input type="text" class="form-control" placeholder="Search in HRMS">
							<span class="input-icon-addon border-end">
								<i class="ti ti-search"></i>
							</span>
						</div>
						<div class="dropdown w-100">
							<a href="javascript:void(0);"
								class="dropdown-toggle w-100 btn btn-white d-inline-flex align-items-center justify-content-between"
								data-bs-toggle="dropdown">
								All Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">In Use</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Available</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Under Repair</a>
								</li>
							</ul>
						</div>
						<div class="dropdown w-100">
							<a href="javascript:void(0);"
								class="dropdown-toggle w-100 btn btn-white d-inline-flex align-items-center justify-content-between"
								data-bs-toggle="dropdown">
								All Categories
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Laptops</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Mouse</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Keyboard</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Chairs</a>
								</li>
							</ul>
						</div>
					</div>

				</div>

				<div class="table-responsive">
					<table class="table table-nowrap mb-0">
						<thead>
							<tr>
								<th>Asset ID</th>
								<th>Asset Name</th>
								<th>Assigned To</th>
								<th>Purchase Date</th>
								<th>Warranty Expiry</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-782</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Dell XPS 13"</p>
								</td>
								<td>
									<p>Sarah Johnson</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2022-08-20</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2025-08-20</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>In Use
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-815</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">HP EliteBook</p>
								</td>
								<td>
									<p>David Lee</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2022-09-10</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2025-09-10</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Active
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-236</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Lenovo ThinkPad</p>
								</td>
								<td>
									<p>Emily Wilson</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2022-10-01</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2025-10-01</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>In Use
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-947</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Apple iPad Pro</p>
								</td>
								<td>
									<p>Unassigned</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2022-11-15</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2025-11-15</p>
								</td>
								<td>
									<span class="badge badge-purple d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Available
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-529</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Samsung Monitor</p>
								</td>
								<td>
									<p>Ashley Green</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2022-12-05</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2025-12-05</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Active
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-681</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Logitech Mouse</p>
								</td>
								<td>
									<p>Andrew Brown</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2023-01-25</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2026-01-25</p>
								</td>
								<td>
									<span class="badge badge-danger d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Under Repair
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-354</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Ergonomic Keyboard</p>
								</td>
								<td>
									<p>Jessica White</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2023-02-14</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2026-02-14</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Active
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-496</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Office Chair</p>
								</td>
								<td>
									<p>Jason Hill</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2023-03-08</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2026-03-08</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Active
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-123</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Standing Desk</p>
								</td>
								<td>
									<p>Megan King</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2023-04-02</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2026-04-02</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Active
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

							<tr>
								<td><a href="#">
										<p class="text-gray-5">AP-069</p>
									</a></td>
								<td>
									<p class="fw-medium text-dark">Plantronics Headset</p>
								</td>
								<td>
									<p>Brandon Wright</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2023-05-19</p>
								</td>
								<td>
									<p><i class="ti ti-calendar-up me-1"></i>2026-05-19</p>
								</td>
								<td>
									<span class="badge badge-success d-inline-flex align-items-center badge-xs">
										<i class="ti ti-point-filled "></i>Active
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-1"><i class="ti ti-edit"></i></a>
										<a href="#"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>

						</tbody>

					</table>
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