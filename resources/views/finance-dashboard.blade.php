@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">

			<!-- Breadcrumb -->
			<div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">Finance Dashboard</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">Finance Dashboard</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-3 mb-2">
					<div class="input-icon position-relative">
						<span class="input-icon-addon">
							<i class="ti ti-calendar text-gray-9"></i>
						</span>
						<input type="text" class="form-control date-range bookingrange"
							placeholder="dd/mm/yyyy - dd/mm/yyyy">
					</div>
					<a href="#" class="btn btn-primary d-inline-flex align-items-center"><i
							class="ti ti-file-export me-1"></i>Export CSV</a>
					<div class="mt-2 head-icons">
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

				<div class="col-xl-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div class="avatar drop-shadow-xs border border-white avatar-rounded z-1">
									<img src="{{URL::asset('build/img/icons/title-icon-01.svg')}}" alt="icon"
										class="img-fluid w-auto h-auto rounded-0">
									<img src="{{URL::asset('build/img/bg/title-bg-01.png')}}" alt="bg"
										class="position-absolute top-0 start-0 title-bg z-n1 rounded-0">
								</div>
								<a href="{{url('budgets')}}"
									class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
										class="ti ti-arrow-up-right"></i></a>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div>
									<p class="mb-1">Budget Remaining</p>
									<h2 class="d-inline-flex align-items-center">$2,458,900 <span
											class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-xs bg-success text-white pe-none rounded-circle ms-2"><i
												class="ti ti-caret-up-filled"></i></span></h2>
								</div>
								<div>
									<span class="border rounded-pill d-inline-flex align-items-center py-1 px-2"><i
											class="ti ti-calendar-stats me-1"></i>Updated : 15 Jan 2025</span>
								</div>
							</div>
							<div class="row g-2">
								<div class="col-5">
									<p class="fs-12 text-dark mb-1">Salary Budget</p>
									<div class="progress progress-xl mb-1" role="progressbar" aria-valuenow="10"
										aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped" style="width: 100%">
										</div>
									</div>
									<p class="mb-0">$1,229,450</p>
								</div>
								<div class="col-3">
									<p class="fs-12 text-dark mb-1">Benefits</p>
									<div class="progress progress-xl mb-1" role="progressbar" aria-valuenow="10"
										aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-secondary"
											style="width: 100%">
										</div>
									</div>
									<p class="mb-0">$491,780</p>
								</div>
								<div class="col-4">
									<p class="fs-12 text-dark mb-1">HR Operations</p>
									<div class="progress progress-xl mb-1" role="progressbar" aria-valuenow="10"
										aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-dark" style="width: 100%">
										</div>
									</div>
									<p class="mb-0">$737,670</p>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xl-3 col-md-6 d-flex">
					<div class="card z-1 flex-fill overflow-hidden">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
									<div class="avatar drop-shadow-xs border border-white avatar-rounded z-1">
										<img src="{{URL::asset('build/img/icons/title-icon-02.svg')}}" alt="icon"
											class="img-fluid w-auto h-auto rounded-0">
										<img src="{{URL::asset('build/img/bg/title-bg-02.png')}}" alt="bg"
											class="position-absolute top-0 start-0 title-bg z-n1 rounded-0">
									</div>
									<a href="#"
										class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
											class="ti ti-refresh"></i></a>
								</div>
								<div class="mb-4">
									<p class="mb-1">Total Payroll</p>
									<h2 class="mb-0 d-inline-flex align-items-center">$2,458,900 <span
											class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-xs bg-success text-white pe-none rounded-circle ms-2"><i
												class="ti ti-caret-up-filled"></i></span></h2>
								</div>
							</div>
							<div id="payroll-payment"></div>
						</div> <!-- end card body -->
						<img src="{{URL::asset('build/img/bg/dashboard-bg.png')}}" alt="bg"
							class="img-fluid position-absolute top-0 start-0 z-n1 w-100">
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xl-3 col-md-6 d-flex">
					<div class="card z-1 flex-fill overflow-hidden">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
									<div class="avatar drop-shadow-xs border border-white avatar-rounded z-1">
										<img src="{{URL::asset('build/img/icons/title-icon-03.svg')}}" alt="icon"
											class="img-fluid w-auto h-auto rounded-0">
										<img src="{{URL::asset('build/img/bg/title-bg-03.png')}}" alt="bg"
											class="position-absolute top-0 start-0 title-bg z-n1 rounded-0">
									</div>
									<a href="#"
										class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
											class="ti ti-refresh"></i></a>
								</div>
								<div class="mb-4">
									<p class="mb-1">Reimbrusement</p>
									<h2 class="mb-0 d-inline-flex align-items-center">$124,200 <span
											class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-xs bg-danger text-white pe-none rounded-circle ms-2"><i
												class="ti ti-caret-down-filled"></i></span></h2>
								</div>
							</div>
							<div id="reimbrusement-chart" class="mt-auto"></div>
						</div> <!-- end card body -->
						<img src="{{URL::asset('build/img/bg/attendance-bg.png')}}" alt="bg"
							class="img-fluid position-absolute top-0 start-0 z-n1 w-100">
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">

				<div class="col-xl-8 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h2 class="card-title mb-0 text-decoration-underline">Headcount vs Payroll Growth
								</h2>
								<div class="d-flex align-items-center gap-3">
									<p class="mb-0"><i class="ti ti-square-filled text-primary me-2"></i>Payroll
										Growth</p>
									<p class="mb-0"><i class="ti ti-square-filled text-gray-2 me-2"></i>Head Count
									</p>
								</div>
								<a href="#"
									class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
										class="ti ti-download"></i></a>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
								<div>
									<h3 class="main-title mb-1">+14%</h3>
									<p class="mb-0">Increased From the Last Quarter</p>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="btn btn-white btn-sm d-inline-flex align-items-center rounded-pill"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar-due me-1"></i>1st Quarter
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												1st Quarter
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												2nd Quarter
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												3rd Quarter
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												4th Quarter
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div id="headcount-chart"></div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xl-4 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h2 class="card-title mb-0 text-decoration-underline">HR Cost Breakdown</h2>
								<a href="#"
									class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
										class="ti ti-refresh"></i></a>
							</div>
							<div class="mb-2">
								<canvas id="cost_chart" height="180"></canvas>
							</div>

							<div class="row g-3">
								<div class="col-6 col-sm-4">
									<div class="border-start border-2 border-secondary ps-2">
										<p class="fs-12 mb-1">Salaries</p>
										<h3 class="fs-14 mb-0">184K</h3>
									</div>
								</div>
								<div class="col-6 col-sm-4">
									<div class="border-start border-2 border-secondary-800 ps-2">
										<p class="fs-12 mb-1">Benefits</p>
										<h3 class="fs-14 mb-0">89K</h3>
									</div>
								</div>
								<div class="col-6 col-sm-4">
									<div class="border-start border-2 border-secondary-700 ps-2">
										<p class="fs-12 mb-1">Bonuses</p>
										<h3 class="fs-14 mb-0">66K</h3>
									</div>
								</div>
								<div class="col-6 col-sm-4">
									<div class="border-start border-2 border-secondary-600 ps-2">
										<p class="fs-12 mb-1">Overtime</p>
										<h3 class="fs-14 mb-0">96K</h3>
									</div>
								</div>
								<div class="col-6 col-sm-4">
									<div class="border-start border-2 border-secondary-500 ps-2">
										<p class="fs-12 mb-1">Training</p>
										<h3 class="fs-14 mb-0">42K</h3>
									</div>
								</div>
								<div class="col-6 col-sm-4">
									<div class="border-start border-2 border-secondary-400 ps-2">
										<p class="fs-12 mb-1">Incentives</p>
										<h3 class="fs-14 mb-0">56K</h3>
									</div>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>

			<!-- start row -->
			<div class="row">

				<div class="col-xxl-4 col-xl-5 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
									<div class="d-flex align-items-center">
										<div
											class="avatar drop-shadow-xs border border-white avatar-rounded z-1 me-2">
											<img src="{{URL::asset('build/img/icons/title-icon-04.svg')}}" alt="icon"
												class="img-fluid w-auto h-auto">
											<img src="{{URL::asset('build/img/bg/title-bg-04.png')}}" alt="icon"
												class="position-absolute top-0 start-0 title-bg z-n1">
										</div>
										<div>
											<h2 class="sub-title mb-1">How can i help today</h2>
											<p class="fs-13 mb-0">AI HR Finance Assistant</p>
										</div>
									</div>
									<a href="#"
										class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
											class="ti ti-refresh"></i></a>
								</div>
								<div class="card bg-light border-0 shadow-none rounded-4 mb-1">
									<div class="card-body">
										<span class="d-flex align-items-center gap-1 mb-2 text-primary"> <i
												class="ti ti-sparkles"></i> AI Assistant</span>
										<p class="mb-0">👋 Hi! I'm your AI HR Finance Assistant. I can help you
											analyze payroll costs, forecast budgets, identify compensation trends.
										</p>
									</div>
								</div>
							</div>
							<div class="chat-input-item bg-white w-100 rounded-3 mt-3">
								<input type="text" class="form-control"
									placeholder="Ask me anything about Finance....">
								<button type="submit" class="btn btn-dark btn-icon rounded-3"><i
										class="ti ti-send"></i></button>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xxl-8 col-xl-7 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h2 class="card-title mb-0 text-decoration-underline">Department Budget vs Actual
								</h2>
								<a href="#"
									class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
										class="ti ti-download"></i></a>
							</div>
							<div id="budget-chart"></div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>

			<!-- start row -->
			<div class="row">

				<div class="col-xxl-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div class="d-flex align-items-center gap-2 flex-wrap">
									<h2 class="card-title mb-0 text-decoration-underline">Financial Health</h2>
									<div class="dropdown">
										<a href="javascript:void(0);"
											class="btn btn-white btn-sm d-inline-flex align-items-center rounded-pill"
											data-bs-toggle="dropdown">
											<i class="ti ti-calendar-due me-1"></i>Yearly
										</a>
										<ul class="dropdown-menu mt-2 p-3">
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
											<li>
												<a href="javascript:void(0);" class="dropdown-item rounded-1">
													Today
												</a>
											</li>
										</ul>
									</div>
								</div>
								<a href="#"
									class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
										class="ti ti-download"></i></a>
							</div>
							<div class="d-sm-flex align-items-end gap-3 justify-content-between">
								<div id="finance-chart" class="w-100 mb-sm-0 mb-2"></div>
								<div class="flex-shrink-0 text-sm-end">
									<p class="mb-1">Total Amount</p>
									<h2 class="main-title mb-3">$4,56,545</h2>
									<p class="fs-12"><span
											class="badge bg-success d-inline-flex align-items-center me-1"><i
												class="ti ti-arrow-up-right me-1"></i>2.5%</span> vs Last Year</p>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xxl-3 col-md-6 d-flex">
					<div class="card bg-secondary z-1 overflow-hidden flex-fill">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<h2 class="card-title text-decoration-underline text-white mb-2">Payroll Forecast
								</h2>
								<p class="text-white mb-3">Upgrade to Payroll Forecasting to predict upcoming salary
									expenses, optimize cash flow, and make confident financial decisions.</p>
							</div>
							<a href="#" class="btn btn-xl btn-primary w-100">Upgrade Now</a>
						</div> <!-- end card body -->
						<img src="{{URL::asset('build/img/bg/payroll-bg.png')}}" alt="bg"
							class="img-fluid position-absolute top-0 start-0 h-100 w-100 z-n1 blend-luminosity">
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xxl-3 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h2 class="card-title mb-0 text-decoration-underline">Pending Payments</h2>
								<a href="#"
									class="btn btn-icon d-inline-flex align-items-center justify-content-center btn-light rounded-circle"><i
										class="ti ti-download"></i></a>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<p class="mb-0">Total Amount</p>
								<h3 class="card-title mb-0">$69856</h3>
							</div>
							<div class="avatar-list-stacked avatar-group mb-3">
								<a href="#" class="avatar avatar-rounded">
									<img class="border border-white" src="{{URL::asset('build/img/users/user-31.jpg')}}" alt="user">
								</a>
								<a href="#" class="avatar avatar-rounded">
									<img class="border border-white" src="{{URL::asset('build/img/users/user-32.jpg')}}" alt="user">
								</a>
								<a href="#" class="avatar avatar-rounded">
									<img class="border border-white" src="{{URL::asset('build/img/users/user-29.jpg')}}" alt="user">
								</a>
								<a href="#" class="avatar avatar-rounded">
									<img class="border border-white" src="{{URL::asset('build/img/users/user-56.jpg')}}" alt="user">
								</a>
								<a class="avatar bg-light border avatar-rounded fs-16 text-dark fw-normal" href="#">
									+9
								</a>
							</div>
							<a href="#" class="btn btn-dark d-flex align-items-center justify-content-center"><i
									class="ti ti-moneybag me-1"></i>Transfer Now</a>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">

				<div class="col-xl-12">
					<div class="card flex-fill">
						<div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
							<h2 class="mb-0 card-title text-decoration-underline">Recent HR Transactions</h2>
							<a href="{{url('payments')}}" class="btn btn-md btn-primary">View All</a>
						</div>
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-nowrap table-striped mb-0">
									<thead>
										<tr>
											<th>Transaction ID</th>
											<th>Employee</th>
											<th>Catgeory</th>
											<th>Amount</th>
											<th>Date</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><a href="{{url('invoice-details')}}">TRX565545878</a></td>
											<td>
												<div class="d-flex align-items-center">
													<a href="{{url('employee-details')}}"
														class="avatar avatar-md border avatar-rounded">
														<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid"
															alt="employee">
													</a>
													<div class="ms-2">
														<p class="fw-medium mb-0"><a
																href="{{url('employee-details')}}">Anthony Lewis</a></p>
													</div>
												</div>
											</td>
											<td>Bonus</td>
											<td class="text-dark">$15,000</td>
											<td><i class="ti ti-calendar-up me-1"></i><span class="text-dark">Jan
													06, 2026</span></td>
											<td>
												<span class="badge badge-success-transparent">Approved</span>
											</td>
											<td>
												<div class="action-icon d-inline-flex">
													<a href="#" class="me-2"><i class="ti ti-printer"></i></a>
													<a href="javascript:void(0);" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><a href="{{url('invoice-details')}}">TRX654412454</a></td>
											<td>
												<div class="d-flex align-items-center">
													<a href="{{url('employee-details')}}"
														class="avatar avatar-md border avatar-rounded">
														<img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="img-fluid"
															alt="employee">
													</a>
													<div class="ms-2">
														<p class="fw-medium mb-0"><a
																href="{{url('employee-details')}}">Brian Villalobos</a>
														</p>
													</div>
												</div>
											</td>
											<td>Training</td>
											<td class="text-dark">$2,500</td>
											<td><i class="ti ti-calendar-up me-1"></i><span class="text-dark">Jan
													05, 2026</span></td>
											<td>
												<span class="badge badge-pink-transparent">Pending</span>
											</td>
											<td>
												<div class="action-icon d-inline-flex">
													<a href="#" class="me-2"><i class="ti ti-printer"></i></a>
													<a href="javascript:void(0);" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><a href="{{url('invoice-details')}}">TRX552145547</a></td>
											<td>
												<div class="d-flex align-items-center">
													<a href="{{url('employee-details')}}"
														class="avatar avatar-md border avatar-rounded">
														<img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid"
															alt="employee">
													</a>
													<div class="ms-2">
														<p class="fw-medium mb-0"><a
																href="{{url('employee-details')}}">Harvey Smith</a></p>
													</div>
												</div>
											</td>
											<td>Commission</td>
											<td class="text-dark">$124,500</td>
											<td><i class="ti ti-calendar-up me-1"></i><span class="text-dark">Jan
													04, 2026</span></td>
											<td>
												<span class="badge badge-purple-transparent">Processing</span>
											</td>
											<td>
												<div class="action-icon d-inline-flex">
													<a href="#" class="me-2"><i class="ti ti-printer"></i></a>
													<a href="javascript:void(0);" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><a href="{{url('invoice-details')}}">TRX254124457</a></td>
											<td>
												<div class="d-flex align-items-center">
													<a href="{{url('employee-details')}}"
														class="avatar avatar-md border avatar-rounded">
														<img src="{{URL::asset('build/img/users/user-33.jpg')}}" class="img-fluid"
															alt="employee">
													</a>
													<div class="ms-2">
														<p class="fw-medium mb-0"><a
																href="{{url('employee-details')}}">Stephan Peralt</a></p>
													</div>
												</div>
											</td>
											<td>Reimbursement</td>
											<td class="text-dark">$1,250</td>
											<td><i class="ti ti-calendar-up me-1"></i><span class="text-dark">Jan
													03, 2026</span></td>
											<td>
												<span class="badge badge-pink-transparent">Pending</span>
											</td>
											<td>
												<div class="action-icon d-inline-flex">
													<a href="#" class="me-2"><i class="ti ti-printer"></i></a>
													<a href="javascript:void(0);" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</td>
										</tr>
										<tr>
											<td><a href="{{url('invoice-details')}}">TRX124512442</a></td>
											<td>
												<div class="d-flex align-items-center">
													<a href="{{url('employee-details')}}"
														class="avatar avatar-md border avatar-rounded">
														<img src="{{URL::asset('build/img/users/user-34.jpg')}}" class="img-fluid"
															alt="employee">
													</a>
													<div class="ms-2">
														<p class="fw-medium mb-0"><a
																href="{{url('employee-details')}}">Doglas Martini</a></p>
													</div>
												</div>
											</td>
											<td>Payroll</td>
											<td class="text-dark">$782,000</td>
											<td><i class="ti ti-calendar-up me-1"></i><span class="text-dark">Jan
													02, 2026</span></td>
											<td>
												<span class="badge badge-secondary-transparent">Processed</span>
											</td>
											<td>
												<div class="action-icon d-inline-flex">
													<a href="#" class="me-2"><i class="ti ti-printer"></i></a>
													<a href="javascript:void(0);" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->
			</div>
			<!-- end row -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection