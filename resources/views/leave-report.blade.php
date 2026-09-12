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
					<h2 class="mb-1">Leave Report</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Reports
							</li>
							<li class="breadcrumb-item active" aria-current="page">Leave Report</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
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
					<div class="head-icons ms-2">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->
			<div class="row">
				<div class="col-xl-6 d-flex">
					<div class="row flex-fill">
						<!-- Total Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div
										class="d-flex align-items-center justify-content-between mb-2 overflow-hidden">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Leaves</p>
											<h4>15</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-primary bg-transparent-primary rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-calendar-x text-primary"></i></span></a>
										</div>
									</div>
									<div class="p-2 bg-gray-100 br-5">
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-12 fw-normal mb-0">Last Month</p>
											<span class="fs-12 fw-normal text-success d-flex align-items-center"><i
													class="ti ti-arrow-wave-right-up text-success me-1"></i>+17.02%</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /Total Companies -->

						<!-- Total Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div
										class="d-flex align-items-center justify-content-between mb-2 overflow-hidden">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Approved Leaves</p>
											<h4>15</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-success bg-transparent-success rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-calendar-x text-success"></i></span></a>
										</div>
									</div>
									<div class="p-2 bg-gray-100 br-5">
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-12 fw-normal mb-0">Last Month</p>
											<span class="fs-12 fw-normal text-success d-flex align-items-center"><i
													class="ti ti-arrow-wave-right-up text-success me-1"></i>+17.02%</span>
										</div>
									</div>

								</div>
							</div>
						</div>
						<!-- /Total Companies -->

						<!-- Inactive Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div
										class="d-flex align-items-center justify-content-between mb-2 overflow-hidden">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Pending Requests</p>
											<h4>5</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-skyblue bg-transparent-skyblue rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-calendar-x text-skyblue"></i></span></a>
										</div>
									</div>
									<div class="p-2 bg-gray-100 br-5">
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-12 fw-normal mb-0">Last Month</p>
											<span class="fs-12 fw-normal text-success d-flex align-items-center"><i
													class="ti ti-arrow-wave-right-up text-success me-1"></i>+17.02%</span>
										</div>
									</div>

								</div>
							</div>
						</div>
						<!-- /Inactive Companies -->

						<!-- Company Location -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div
										class="d-flex align-items-center justify-content-between mb-2 overflow-hidden">
										<div>
											<p class="fs-12 fw-normal mb-1 text-truncate">Rejected Leaves</p>
											<h4>5</h4>
										</div>
										<div class="leave-report-icon">
											<a href="#"><span
													class="p-2 border border-danger bg-transparent-danger rounded-circle d-flex align-items-center justify-content-center"><i
														class="ti ti-calendar-x text-danger"></i></span></a>
										</div>
									</div>
									<div class="p-2 bg-gray-100 br-5">
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-12 fw-normal mb-0">Last Month</p>
											<span class="fs-12 fw-normal text-success d-flex align-items-center"><i
													class="ti ti-arrow-wave-right-up text-success me-1"></i>+17.02%</span>
										</div>
									</div>

								</div>
							</div>
						</div>
						<!-- /Company Location -->
					</div>
				</div>
				<div class="col-xl-6 d-flex">
					<div class="card flex-fill">
						<div class="card-header border-0 pb-0">
							<div class="d-flex flex-wrap justify-content-between align-items-center row-gap-2">
								<div class="d-flex align-items-center ">
									<span class="me-2"><i class="ti ti-chart-bar text-danger"></i></span>
									<h5>Leaves </h5>
								</div>
								<div class="d-flex align-items-center">
									<p class="d-inline-flex align-items-center me-2 mb-0">
										<i class="ti ti-square-filled fs-12 text-success me-2"></i>
										Annual
									</p>
									<p class="d-inline-flex align-items-center mb-0 me-2">
										<i class="ti ti-square-filled fs-12 text-warning me-2"></i>
										Casual
									</p>
									<p class="d-inline-flex align-items-center mb-0 me-2">
										<i class="ti ti-square-filled fs-12 text-dark me-2"></i>
										Medical
									</p>
									<p class="d-inline-flex align-items-center">
										<i class="ti ti-square-filled fs-12 text-primary me-2"></i>
										Others
									</p>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-sm fs-12 btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										This Year
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2023</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2022</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body py-0">
							<div id="leave-report"> </div>
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Invoice List</h5>
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
								$0.00 - $0.00
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">$3000</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">$2500</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">$2800</a>
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
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Sent</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Partially Paid</a>
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
									<th>Invoice ID</th>
									<th>Client Name</th>
									<th>Company Name</th>
									<th>Created Date</th>
									<th>Due Date</th>
									<th>Amount</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-001</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-01.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Michael Walker</a></p>
												<span class="fs-12">CEO</span>
											</div>
										</div>
									</td>
									<td>BrightWave Innovations</td>
									<td>14 Jan 2024</td>
									<td>15 Jan 2024</td>
									<td>$3000</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Paid
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-002</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-02.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Sophie Headrick</a></p>
												<span class="fs-12">Manager</span>
											</div>
										</div>
									</td>
									<td>Stellar Dynamics</td>
									<td>21 Jan 2024</td>
									<td>25 Jan 2024</td>
									<td>$2500</td>
									<td>
										<span
											class="badge badge-soft-purple d-inline-flex align-items-center badge-xs">
											Sent
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-003</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-03.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Cameron Drake</a></p>
												<span class="fs-12">Director</span>
											</div>
										</div>
									</td>
									<td>Quantum Nexus</td>
									<td>20 Feb 2024</td>
									<td>22 Feb 2024</td>
									<td>$2800</td>
									<td>
										<span
											class="badge badge-soft-warning d-inline-flex align-items-center badge-xs">
											Partially Paid
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-004</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-04.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Doris Crowley</a></p>
												<span class="fs-12">Consultant</span>
											</div>
										</div>
									</td>
									<td>EcoVision Enterprises</td>
									<td>15 Mar 2024</td>
									<td>17 Mar 2024</td>
									<td>$3300</td>
									<td>
										<span
											class="badge badge-soft-purple d-inline-flex align-items-center badge-xs">
											Sent
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-005</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-05.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Thomas Bordelon</a></p>
												<span class="fs-12">Manager</span>
											</div>
										</div>
									</td>
									<td>Aurora Technologies</td>
									<td>12 Apr 2024</td>
									<td>16 Apr 2024</td>
									<td>$3600</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Paid
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-006</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-06.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Kathleen Gutierrez</a></p>
												<span class="fs-12">Director</span>
											</div>
										</div>
									</td>
									<td>BlueSky Ventures</td>
									<td>20 Apr 2024</td>
									<td>21 Apr 2024</td>
									<td>$2000</td>
									<td>
										<span
											class="badge badge-soft-warning d-inline-flex align-items-center badge-xs">
											Partially Paid
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-007</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-07.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Bruce Wright</a></p>
												<span class="fs-12">CEO</span>
											</div>
										</div>
									</td>
									<td>TerraFusion Energy</td>
									<td>06 Jul 2024</td>
									<td>06 Jul 2024</td>
									<td>$3400</td>
									<td>
										<span
											class="badge badge-soft-purple d-inline-flex align-items-center badge-xs">
											Sent
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-008</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-08.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Estelle Morgan</a></p>
												<span class="fs-12">Manager</span>
											</div>
										</div>
									</td>
									<td>UrbanPulse Design</td>
									<td>02 Sep 2024</td>
									<td>04 Sep 2024</td>
									<td>$4000</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Paid
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-009</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-09.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Stephen Dias</a></p>
												<span class="fs-12">CEO</span>
											</div>
										</div>
									</td>
									<td>Nimbus Networks</td>
									<td>15 Nov 2024</td>
									<td>15 Nov 2024</td>
									<td>$4500</td>
									<td>
										<span
											class="badge badge-soft-warning d-inline-flex align-items-center badge-xs">
											Partially Paid
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td><a href="{{url('invoice-details')}}" class="link-default">Inv-010</a></td>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/reports/user-10.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Angela Thomas</a></p>
												<span class="fs-12">Consultant</span>
											</div>
										</div>
									</td>
									<td>Epicurean Delights</td>
									<td>10 Dec 2024</td>
									<td>11 Dec 2024</td>
									<td>$3800</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											Paid
										</span>
									</td>
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