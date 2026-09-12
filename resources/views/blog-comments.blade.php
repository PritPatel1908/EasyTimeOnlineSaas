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
					<h2 class="mb-1">Blog Comments</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Blogs
							</li>
							<li class="breadcrumb-item active" aria-current="page"> Comments</li>
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
					<div class="head-icons">
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
					<h5>Blog Comments List</h5>
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
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Comment</th>
									<th>Created Date</th>
									<th>Review</th>
									<th>Blog</th>
									<th>By</th>
									<th></th>
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

									<td>Useful breakdown of HRMS evolution!</td>
									<td>12 Sep 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td>The Evolution of HRMS: From Manual to Digital</td>
									<td>Gertrude</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Easy-to-follow HRMS guide!</td>
									<td>24 Oct 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td>HRMS Implementation: A Step-by-Step Guide</td>
									<td>Edward</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Essential tips on HRMS data security!</td>
									<td>18 Feb 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> Data Security in HRMS: What Matters</td>
									<td>Mark</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Great HRMS recruitment tips</td>
									<td>17 Oct 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> Improving Recruitment with HRMS</td>
									<td>Nidia</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Great look at how HRMS affects culture</td>
									<td>20 Jul 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> Impact of HRMS on Company Culture</td>
									<td>Rebecca</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Valuable points on HRMS benefits</td>
									<td>10 Apr 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> Key Benefits of Implementing HRMS</td>
									<td>Jimmy</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Great points on why an HRMS is crucial</td>
									<td>29 Aug 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> Why Your Company Needs an HRMS</td>
									<td>Richard</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Great take on HRMS technology’s future</td>
									<td>22 Feb 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> The Future of HRMS Technology</td>
									<td>Rachael</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Valuable insights on scaling HR with HRMS!</td>
									<td>03 Nov 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> Scaling Your HR Operations with HRMS</td>
									<td>Tammy</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

									<td>Useful points on how HRMS drives success</td>
									<td>17 Dec 2024</td>
									<td><span class="text-warning">
											<i class="ti ti-star-filled "></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
											<i class="ti ti-star-filled"></i>
										</span></td>
									<td> How HRMS Drives Organizational Success</td>
									<td>Judith</td>
									<td>
										<div class="dropdown me-3">
											<a href="javascript:void(0);"
												class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
												data-bs-toggle="dropdown">
												Unpublish
											</a>
											<ul class="dropdown-menu  dropdown-menu-end p-3">
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Unpublish</a>
												</li>
												<li>
													<a href="javascript:void(0);"
														class="dropdown-item rounded-1">Publish</a>
												</li>

											</ul>
										</div>
									</td>
									<td>
										<div class="action-icon d-inline-flex">

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

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection