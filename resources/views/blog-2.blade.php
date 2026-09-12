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
					<h2 class="mb-1">Blogs</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Content
							</li>
							<li class="breadcrumb-item active" aria-current="page">Blogs</li>
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
					<div class="mb-2">
						<a href="#" data-bs-toggle="modal" data-bs-target="#add_blog"
							class="btn btn-primary d-flex align-items-center"><i
								class="ti ti-circle-plus me-2"></i>Add Blog</a>
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
				<div class="card-body p-3">
					<div class="d-flex align-items-center justify-content-between">
						<h5>Blogs</h5>
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
									Status
								</a>
								<ul class="dropdown-menu  dropdown-menu-end p-3">
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
									</li>
									<li>
										<a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
									</li>
								</ul>
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
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3">
								<div class="row mb-3">
									<div class="col-md-10">
										<div class="d-flex align-items-center">
											<div class="avatar avatar-xxl rounded me-3">
												<img src="{{URL::asset('build/img/blog-2/blog-01.jpg')}}" class="rounded" alt="Img">
											</div>
											<div>
												<div class="d-flex align-items-center mb-2">
													<p class="me-2 mb-0"><i class="ti ti-calendar me-1"></i>12 Sep
														2024</p>
													<span class="badge badge-soft-info ms-2">Evlovution</span>
												</div>
												<p class="fw-medium text-dark mb-2">The Evolution of HRMS: From
													Manual to Digital</p>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-xs avatar-rounded me-2">
														<img src="{{URL::asset('build/img/users/user-29.jpg')}}" alt="Img">
													</span>
													<p class="text-gray mb-0">Gertrude Bowie</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="d-flex justify-content-end">
											<a href="#" class="me-2 text-gray" data-bs-toggle="modal"
												data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
											<a href="#" class="text-gray" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-10">
									<div class="d-flex align-items-center">
										<div class="border-end pe-4">
											<h6>3000</h6>
											<p class="fs-12">Likes</p>
										</div>
										<div class="border-end px-4">
											<h6>454</h6>
											<p class="fs-12">Comments</p>
										</div>
										<div class="border-end px-4">
											<h6>102</h6>
											<p class="fs-12">Share</p>
										</div>
										<div class="ps-4">
											<h6>350</h6>
											<p class="fs-12">Reviews</p>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="d-flex justify-content-end">
										<span class="badge badge-success"><i
												class="ti ti-point-filled"></i>Active</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3">
								<div class="row mb-3">
									<div class="col-md-10">
										<div class="d-flex align-items-center">
											<div class="avatar avatar-xxl rounded me-3">
												<img src="{{URL::asset('build/img/blog-2/blog-02.jpg')}}" class="rounded" alt="Img">
											</div>
											<div>
												<div class="d-flex align-items-center mb-2">
													<p class="me-2 mb-0"><i class="ti ti-calendar me-1"></i>05 Oct
														2024</p>
													<span class="badge badge-soft-info ms-2">Evlovution</span>
												</div>
												<p class="fw-medium text-dark mb-2">HRMS Implementation: A
													Step-by-Step Guide</p>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-xs avatar-rounded me-2">
														<img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="Img">
													</span>
													<p class="text-gray mb-0">Edward Marcus</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="d-flex justify-content-end">
											<a href="#" class="me-2 text-gray" data-bs-toggle="modal"
												data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
											<a href="#" class="text-gray" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-10">
									<div class="d-flex align-items-center">
										<div class="border-end pe-4">
											<h6>3000</h6>
											<p class="fs-12">Likes</p>
										</div>
										<div class="border-end px-4">
											<h6>454</h6>
											<p class="fs-12">Comments</p>
										</div>
										<div class="border-end px-4">
											<h6>102</h6>
											<p class="fs-12">Share</p>
										</div>
										<div class="ps-4">
											<h6>350</h6>
											<p class="fs-12">Reviews</p>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="d-flex justify-content-end">
										<span class="badge badge-success"><i
												class="ti ti-point-filled"></i>Active</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3">
								<div class="row mb-3">
									<div class="col-md-10">
										<div class="d-flex align-items-center">
											<div class="avatar avatar-xxl rounded me-3">
												<img src="{{URL::asset('build/img/blog-2/blog-03.jpg')}}" class="rounded" alt="Img">
											</div>
											<div>
												<div class="d-flex align-items-center mb-2">
													<p class="me-2 mb-0"><i class="ti ti-calendar me-1"></i>18 Oct
														2024</p>
													<span class="badge badge-soft-info ms-2">Security</span>
												</div>
												<p class="fw-medium text-dark mb-2">Data Security in HRMS: What
													Matters</p>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-xs avatar-rounded me-2">
														<img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="Img">
													</span>
													<p class="text-gray mb-0">Mark Phillips</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="d-flex justify-content-end">
											<a href="#" class="me-2 text-gray" data-bs-toggle="modal"
												data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
											<a href="#" class="text-gray" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-10">
									<div class="d-flex align-items-center">
										<div class="border-end pe-4">
											<h6>3000</h6>
											<p class="fs-12">Likes</p>
										</div>
										<div class="border-end px-4">
											<h6>454</h6>
											<p class="fs-12">Comments</p>
										</div>
										<div class="border-end px-4">
											<h6>102</h6>
											<p class="fs-12">Share</p>
										</div>
										<div class="ps-4">
											<h6>350</h6>
											<p class="fs-12">Reviews</p>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="d-flex justify-content-end">
										<span class="badge badge-success"><i
												class="ti ti-point-filled"></i>Active</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3">
								<div class="row mb-3">
									<div class="col-md-10">
										<div class="d-flex align-items-center">
											<div class="avatar avatar-xxl rounded me-3">
												<img src="{{URL::asset('build/img/blog-2/blog-04.jpg')}}" class="rounded" alt="Img">
											</div>
											<div>
												<div class="d-flex align-items-center mb-2">
													<p class="me-2 mb-0"><i class="ti ti-calendar me-1"></i>27 Oct
														2024</p>
													<span class="badge badge-soft-info ms-2">Recruitment</span>
												</div>
												<p class="fw-medium text-dark mb-2">Improving Recruitment with HRMS
												</p>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-xs avatar-rounded me-2">
														<img src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="Img">
													</span>
													<p class="text-gray mb-0">Nidia Hale</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="d-flex justify-content-end">
											<a href="#" class="me-2 text-gray" data-bs-toggle="modal"
												data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
											<a href="#" class="text-gray" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-10">
									<div class="d-flex align-items-center">
										<div class="border-end pe-4">
											<h6>3000</h6>
											<p class="fs-12">Likes</p>
										</div>
										<div class="border-end px-4">
											<h6>454</h6>
											<p class="fs-12">Comments</p>
										</div>
										<div class="border-end px-4">
											<h6>102</h6>
											<p class="fs-12">Share</p>
										</div>
										<div class="ps-4">
											<h6>350</h6>
											<p class="fs-12">Reviews</p>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="d-flex justify-content-end">
										<span class="badge badge-success"><i
												class="ti ti-point-filled"></i>Active</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3">
								<div class="row mb-3">
									<div class="col-md-10">
										<div class="d-flex align-items-center">
											<div class="avatar avatar-xxl rounded me-3">
												<img src="{{URL::asset('build/img/blog-2/blog-05.jpg')}}" class="rounded" alt="Img">
											</div>
											<div>
												<div class="d-flex align-items-center mb-2">
													<p class="me-2 mb-0"><i class="ti ti-calendar me-1"></i>08 Nov
														2024</p>
													<span class="badge badge-soft-info ms-2">Implementation</span>
												</div>
												<p class="fw-medium text-dark mb-2">Impact of HRMS on Company
													Culture</p>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-xs avatar-rounded me-2">
														<img src="{{URL::asset('build/img/users/user-35.jpg')}}" alt="Img">
													</span>
													<p class="text-gray mb-0">Rebecca Dale</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="d-flex justify-content-end">
											<a href="#" class="me-2 text-gray" data-bs-toggle="modal"
												data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
											<a href="#" class="text-gray" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-10">
									<div class="d-flex align-items-center">
										<div class="border-end pe-4">
											<h6>3000</h6>
											<p class="fs-12">Likes</p>
										</div>
										<div class="border-end px-4">
											<h6>454</h6>
											<p class="fs-12">Comments</p>
										</div>
										<div class="border-end px-4">
											<h6>102</h6>
											<p class="fs-12">Share</p>
										</div>
										<div class="ps-4">
											<h6>350</h6>
											<p class="fs-12">Reviews</p>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="d-flex justify-content-end">
										<span class="badge badge-success"><i
												class="ti ti-point-filled"></i>Active</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3">
								<div class="row mb-3">
									<div class="col-md-10">
										<div class="d-flex align-items-center">
											<div class="avatar avatar-xxl rounded me-3">
												<img src="{{URL::asset('build/img/blog-2/blog-06.jpg')}}" class="rounded" alt="Img">
											</div>
											<div>
												<div class="d-flex align-items-center mb-2">
													<p class="me-2 mb-0"><i class="ti ti-calendar me-1"></i>17 Nov
														2024</p>
													<span class="badge badge-soft-info ms-2">Implementation</span>
												</div>
												<p class="fw-medium text-dark mb-2">Key Benefits of Implementing
													HRMS</p>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-xs avatar-rounded me-2">
														<img src="{{URL::asset('build/img/users/user-52.jpg')}}" alt="Img">
													</span>
													<p class="text-gray mb-0">Jimmy Johnson</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="d-flex justify-content-end">
											<a href="#" class="me-2 text-gray" data-bs-toggle="modal"
												data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
											<a href="#" class="text-gray" data-bs-toggle="modal"
												data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
										</div>
									</div>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-10">
									<div class="d-flex align-items-center">
										<div class="border-end pe-4">
											<h6>3000</h6>
											<p class="fs-12">Likes</p>
										</div>
										<div class="border-end px-4">
											<h6>454</h6>
											<p class="fs-12">Comments</p>
										</div>
										<div class="border-end px-4">
											<h6>102</h6>
											<p class="fs-12">Share</p>
										</div>
										<div class="ps-4">
											<h6>350</h6>
											<p class="fs-12">Reviews</p>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="d-flex justify-content-end">
										<span class="badge badge-success"><i
												class="ti ti-point-filled"></i>Active</span>
									</div>
								</div>
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