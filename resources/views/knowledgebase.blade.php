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
					<h2 class="mb-1">Knowledgebase</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Help & Supports
							</li>
							<li class="breadcrumb-item active" aria-current="page">Knowledgebase</li>
						</ol>
					</nav>
				</div>
				<div class="mb-2">
					<div class="d-flex align-items-center">
						<div class="head-icons ms-2 mb-0">
							<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
								data-bs-original-title="Collapse" id="collapse-header">
								<i class="ti ti-chevrons-up"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="card">
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between">
						<h5>Knowledgebase</h5>
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
							<div class="dropdown">
								<a href="javascript:void(0);"
									class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
									data-bs-toggle="dropdown">
									Sort By : Last 7 Days
								</a>
								<ul class="dropdown-menu  dropdown-menu-end p-3">
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Recently
											Added</a>
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
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7
											Days</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Introduction to HRMS <span
										class="text-primary">( 06 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">What
									is an HRMS and Why is it Important? </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">The
									Key Features of an HRMS Explained </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How
									HRMS Helps Automate HR Tasks </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">HRMS
									Terminology : A Beginner’s Guide </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">Cloud
									vs On-Premise HRMS vs Hybrid </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Employee Self-Service (ESS)
									<span class="text-primary">( 10 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									view & update your personal information </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">Steps
									to Apply for Leave via the Employee Portal </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									access and download your payslips </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Submitting & Tracking Expense
									Reimbursements </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									track your attendance and work hours </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Manager Self-Service (MSS) <span
										class="text-primary">( 12 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Approve or Reject Employee Requests </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Viewing and managing team
									attendance </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									conduct performance reviews </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Approving expense claims for
									your team </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									update & view team’s work schedules </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Payroll Management <span
										class="text-primary">( 08 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How
									Payroll is Processed : A Step-by-Step Guide </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Deductions, Overtime, and
									Bonuses </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">What
									to Do if There’s a Payroll Discrepancy </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Access Historical Payroll Information </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Managing Employee Tax
									Information and Filing </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Attendance & Time Tracking <span
										class="text-primary">( 07 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									clock in/out using the hrms portal </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Submitting timesheets for
									approval </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Tracking overtime & managing
									work hours in hrms </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									view and manage shifts and schedules </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Generating attendance reports
									for your team </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Leave Management <span
										class="text-primary">( 06 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Request Casual or Medical Leave </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How
									Leave Balances Are Calculated in Hrms </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">Leave
									Approval Workflow : Guide for Managers </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Viewing Your Leave History &
									Pending Requests </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Understanding Different Types of
									Leaves </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Recruitment & Onboarding <span
										class="text-primary">( 10 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Apply for Internal Job Postings in Hrms </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Creating & Posting Job Openings
									as a Recruiter </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Tracking Applicants and
									Scheduling Interviews </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Complete New Hire Onboarding Process </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Uploading & Verifying New
									Employee Documents </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Performance Management <span
										class="text-primary">( 13 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Setting and Tracking Your
									Employee Goals </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Conducting 360-degree Feedback
									in Hrms </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Complete a Performance Review </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Update Employee Development Plans </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Generating Performance Reports
									and Metrics </a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="{{url('knowledgebase-view')}}"
									class="text-dark fs-16 fw-medium text-truncate">Reports & Analytics <span
										class="text-primary">( 15 )</span></a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Generate Employee Attendance Reports </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Creating Custom Payroll Reports
								</a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Analyzing Workforce Metrics in
									Hrms </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Track Performance Metrics and KPIs </a>
							</div>
							<div class="d-flex align-items-center">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Viewing and Analyzing
									Compensation </a>
							</div>
						</div>
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