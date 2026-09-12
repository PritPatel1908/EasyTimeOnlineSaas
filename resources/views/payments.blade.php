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
					<h2 class="mb-1">Payments</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Sales
							</li>
							<li class="breadcrumb-item active" aria-current="page">Payments</li>
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



			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Payment List</h5>
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

									<th>Invoice ID</th>
									<th>Client Name</th>
									<th>Company Name</th>
									<th>Payment Type</th>
									<th>Paid Date</th>

									<th>Paid Amount</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info"> Inv-001</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-39.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Michael Walker</a></h6>
												<span class="d-block mt-1">CEO</span>
											</div>
										</div>
									</td>
									<td>BrightWave Innovations</td>
									<td>Paypal</td>
									<td>15 Jan 2024</td>
									<td>$3000</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-002</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-40.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Sophie Headrick Manager</a></h6>
												<span class="d-block mt-1">Manager</span>
											</div>
										</div>
									</td>
									<td>Stellar Dynamics</td>
									<td>Paypal</td>
									<td>25 Jan 2024</td>
									<td>$2500</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-003</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-41.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Cameron Drake Director</a></h6>
												<span class="d-block mt-1">Director</span>
											</div>
										</div>
									</td>
									<td>Quantum Nexus</td>
									<td>Paypal</td>
									<td>22 Feb 2024</td>
									<td>$2800</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-004</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-42.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Doris Crowley Consultant</a></h6>
												<span class="d-block mt-1">Consultant</span>
											</div>
										</div>
									</td>
									<td>EcoVision Enterprises</td>
									<td>Paypal</td>
									<td>17 Mar 2024</td>
									<td>$3300</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-005</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-43.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Thomas Bordelon</a></h6>
												<span class="d-block mt-1">Manager</span>
											</div>
										</div>
									</td>
									<td>Aurora Technologies</td>
									<td>Paypal</td>
									<td>16 Apr 2024</td>
									<td>$3600</td>
								</tr>

								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-006</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-44.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Kathleen Gutierrez</a></h6>
												<span class="d-block mt-1">Director</span>
											</div>
										</div>
									</td>
									<td>BlueSky Ventures</td>
									<td>Paypal</td>
									<td>21 Apr 2024</td>
									<td>$2000</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-007</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-45.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Bruce Wright</a></h6>
												<span class="d-block mt-1">CEO</span>
											</div>
										</div>
									</td>
									<td>TerraFusion Energy</td>
									<td>Paypal</td>
									<td>06 Jul 2024</td>
									<td>$3400</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-008</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-46.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Estelle Morgan</a></h6>
												<span class="d-block mt-1">Manager</span>
											</div>
										</div>
									</td>
									<td>UrbanPulse Design</td>
									<td>Paypal</td>
									<td>04 Sep 2024</td>
									<td>$4000</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-009</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-47.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Stephen Dias</a></h6>
												<span class="d-block mt-1">CEO</span>
											</div>
										</div>
									</td>
									<td>Nimbus Networks</td>
									<td>Paypal</td>
									<td>15 Nov 2024</td>
									<td>$4500</td>
								</tr>
								<tr>
									<td><a href="{{url('invoice-details')}}" class="link-info">Inv-010</a></td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-48.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<h6 class="fw-medium"><a href="#">Angela Thomas</a></h6>
												<span class="d-block mt-1">Consultant</span>
											</div>
										</div>
									</td>
									<td>Epicurean Delights</td>
									<td>Paypal</td>
									<td>11 Dec 2024</td>
									<td>$3800</td>
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