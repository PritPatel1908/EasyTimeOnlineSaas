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
						<div class="dropdown ">
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
				<div class="col-xl-8">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-3">
								<i class="ti ti-folder text-primary fs-24 me-1"></i>
								<a href="#" class="text-dark fs-16 fw-medium text-truncate">What is an HRMS and Why
									is it Important?</a>
							</div>
							<div class="d-flex align-items-center mb-3 ps-4">
								<span class="badge badge-primary me-2"> Introduction to HRMS </span>
								<a href="javascript:void(0);"
									class="border-start text-gray fs-14 fw-normal ps-2 me-2"><img
										src="{{URL::asset('build/img/users/user-38.jpg')}}"
										class="avatar avatar-xs me-2 flex-shrink-0" alt="Profile">Gertrude Bowie</a>
								<a href="javascript:void(0);" class="border-start text-gray fs-14 fw-normal ps-2"><i
										class="ti ti-calendar"></i> 05 Oct 2024 </a>
							</div>
							<div class="col-xl-12 border-bottom mb-3 pb-4">
								<p class="fs-14 fw-normal mb-1 pb-3">A Human Resource Management System (HRMS) is a
									software solution designed to manage and streamline various HR functions within
									an organization. From employee data management to payroll, attendance tracking,
									and performance reviews, HRMS centralizes all human resources tasks into a
									single platform, improving efficiency and reducing manual administrative work.
								</p>
								<div class="mb-4">
									<h6 class="fs-14 fw-medium pb-3">Why is an HRMS Important?</h6>
									<p class="fs-14 fw-normal">An HRMS plays a vital role in modern businesses by
										automating many HR processes. Key reasons for using an HRMS include:</p>
									<div class="knowledgebase ps-3">
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Efficiency: It automates
											repetitive tasks such as payroll calculations, attendance tracking, and
											benefits management, freeing up HR teams to focus on more strategic
											tasks.</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Data Accuracy: By using
											automated systems, HRMS minimizes human errors in processes like payroll
											and data entry.</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Compliance: Ensures adherence
											to labor laws and regulations by tracking key metrics such as overtime,
											leave, and employee benefits.</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Employee Self-Service:
											Employees can access their personal information, request leave, download
											payslips, and more, directly from the system without needing HR
											intervention.</p>
									</div>
								</div>
								<div>
									<h6 class="fs-14 fw-medium pb-3">Benefits of Implementing an HRMS</h6>
									<p class="fs-14 fw-normal">An HRMS brings several advantages to organizations of
										all sizes:</p>
									<div class="knowledgebase ps-3">
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Centralized Data: All employee
											and HR-related data are stored in one place, making it easy to retrieve
											information and generate reports.</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Cost Savings: By automating
											tasks like payroll and attendance, companies save time and reduce the
											cost of manual HR processes.</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Enhanced Productivity: HR
											professionals can focus on strategic initiatives rather than day-to-day
											administrative tasks.</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Improved Decision-Making: With
											real-time access to HR analytics, organizations can make data-driven
											decisions regarding workforce planning, performance, and compensation.
										</p>
										<p class="d-flex align-items-baseline"> <i
												class="ti ti-point-filled me-1"></i> Employee Engagement: Employee
											self-service features empower staff to manage their own HR tasks,
											leading to higher satisfaction and reduced HR workload.</p>
									</div>
								</div>
							</div>
							<div class="col-lx-12 border-bottom mb-3">
								<div class="card">
									<div class="card-body">
										<div class="d-flex align-items-center justify-content-between">
											<div>
												<h6 class="fs-16 fw-medium mb-1">Was this helpful to you</h6>
												<p class="fs-12 fw-normal text-gray">30 found this helpful</p>
											</div>
											<div class="d-flex">
												<a href="javascript:void(0);" class="badge bg-success p-2 me-1"><i
														class="ti ti-thumb-up fs-16"></i></a>
												<a href="javascript:void(0);" class="badge bg-danger p-2"><i
														class="ti ti-thumb-down fs-16"></i></a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lx-12 border-bottom mb-3">
								<h6 class="fs-16 fw-medium pb-3">Comments</h6>
								<div class="card mb-3">
									<div class="card-body">
										<div class="d-flex align-items-center mb-3">
											<span class="avatar avatar-lg me-2 flex-shrink-0">
												<img src="{{URL::asset('build/img/users/user-49.jpg')}}" class="rounded-circle"
													alt="Profile">
											</span>
											<div>
												<h6 class="text-dark fs-16 fw-medium">Adrian Hendriques</h6>
												<span class="text-gray fs-14 fw-normal">2 days ago</span>
											</div>
										</div>
										<div class="mb-3">
											<p class="fs-14 fw-normal text-gray">This article effectively outlines
												how an HRMS enhances efficiency by automating HR tasks and improving
												data accuracy. Its focus on employee self-service and data-driven
												insights shows how it benefits both HR teams and organizational
												productivity.</p>
										</div>
										<div class="d-flex align-items-center justify-content-between">
											<div>
												<a href="javascript:void(0);"
													class="fs-14 fw-normal text-gray me-3"> <i
														class="ti ti-arrow-back-up fs-14 fw-normal text-gray me-1"></i>
													Reply </a>
												<a href="javascript:void(0);"
													class="fs-14 fw-normal text-gray me-3"> <i
														class="ti ti-thumb-up fs-14 fw-normal text-gray me-1"></i>
													Like </a>
												<a href="javascript:void(0);" class="fs-14 fw-normal text-gray"> <i
														class="ti ti-thumb-down fs-14 fw-normal text-gray me-1"></i>
													Dislike </a>
											</div>
											<div>
												<a href="javascript:void(0);"
													class="fs-14 fw-normal text-gray me-3"> <i
														class="ti ti-thumb-up fs-14 fw-normal text-gray me-1"></i>
													45 </a>
												<a href="javascript:void(0);" class="fs-14 fw-normal text-gray"> <i
														class="ti ti-thumb-down fs-14 fw-normal text-gray me-1"></i>
													21 </a>
											</div>
										</div>
									</div>
								</div>
								<div class="card">
									<div class="card-body">
										<div class="d-flex align-items-center mb-3">
											<span class="avatar avatar-lg me-2 flex-shrink-0">
												<img src="{{URL::asset('build/img/users/user-55.jpg')}}" class="rounded-circle"
													alt="Profile">
											</span>
											<div>
												<h6 class="text-dark fs-16 fw-medium">Anthony Lewis</h6>
												<span class="text-gray fs-14 fw-normal">2 days ago</span>
											</div>
										</div>
										<div class="mb-3">
											<p class="fs-14 fw-normal text-gray">This article thoroughly explains
												how an HRMS boosts organizational efficiency by automating HR
												processes and ensuring accurate data management. The emphasis on
												employee self-service and real-time analytics illustrates its
												significant impact on productivity and decision-making.</p>
										</div>
										<div class="d-flex align-items-center justify-content-between">
											<div>
												<a href="javascript:void(0);"
													class="fs-14 fw-normal text-gray me-3"> <i
														class="ti ti-arrow-back-up fs-14 fw-normal text-gray me-1"></i>
													Reply </a>
												<a href="javascript:void(0);"
													class="fs-14 fw-normal text-gray me-3"> <i
														class="ti ti-thumb-up fs-14 fw-normal text-gray me-1"></i>
													Like </a>
												<a href="javascript:void(0);" class="fs-14 fw-normal text-gray"> <i
														class="ti ti-thumb-down fs-14 fw-normal text-gray me-1"></i>
													Dislike </a>
											</div>
											<div>
												<a href="javascript:void(0);"
													class="fs-14 fw-normal text-gray me-3"> <i
														class="ti ti-thumb-up fs-14 fw-normal text-gray me-1"></i>
													45 </a>
												<a href="javascript:void(0);" class="fs-14 fw-normal text-gray"> <i
														class="ti ti-thumb-down fs-14 fw-normal text-gray me-1"></i>
													21 </a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<h6 class="fs-16 fw-medium pb-3">Leave A Reply</h6>
								<div class="col-md-6">
									<div class="mb-3">
										<label class="form-label">Name</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-6">
									<div class="mb-3">
										<label class="form-label">Email</label>
										<input type="email" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Website</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Comment</label>
										<textarea class="form-control" rows="3"></textarea>
									</div>
								</div>
								<div>
									<button type="submit" class="btn btn-primary">Post Comment</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 theiaStickySidebar">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center border-bottom mb-3 pb-3">
								<a href="javascript:void(0);"
									class="text-dark fs-16 fw-semibold text-truncate">Categories</a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Employee Self-Service (ESS)
									<span class="text-primary">( 10 )</span> </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Manager Self-Service (MSS) <span
										class="text-primary">( 12 )</span> </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Payroll Management <span
										class="text-primary">( 08 )</span> </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Attendance & Time Tracking <span
										class="text-primary">( 07 )</span> </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">Leave
									Management <span class="text-primary">( 06 )</span> </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Performance Management <span
										class="text-primary">( 13 )</span> </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-folder text-primary fs-16 me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Reports & Analytics <span
										class="text-primary">( 15 )</span> </a>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center border-bottom mb-3 pb-3">
								<a href="javascript:void(0);"
									class="text-dark fs-16 fw-semibold text-truncate">Popular Articles</a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">What
									is an HRMS and Why is it Important? </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									view & update your personal information </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);"
									class="text-gray fs-14 fw-normal text-truncate">Viewing and managing team
									attendance </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How
									Payroll is Processed : A Step-by-Step Guide </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									clock in/out using the hrms portal </a>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center border-bottom mb-3 pb-3">
								<a href="javascript:void(0);"
									class="text-dark fs-16 fw-semibold text-truncate">Latest Articles</a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									update & view team’s work schedules </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									clock in/out using the hrms portal </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									Apply for Internal Job Postings in Hrms </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									track your attendance and work hours </a>
							</div>
							<div class="d-flex align-items-center mb-2 pb-1">
								<i class="ti ti-file me-1"></i>
								<a href="javascript:void(0);" class="text-gray fs-14 fw-normal text-truncate">How to
									conduct performance reviews </a>
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