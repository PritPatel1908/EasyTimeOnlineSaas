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
					<h2 class="mb-1">Search Result</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Pages
							</li>
							<li class="breadcrumb-item active" aria-current="page">Search Result</li>
						</ol>
					</nav>
				</div>
				<div class="head-icons ms-2">
					<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
						data-bs-original-title="Collapse" id="collapse-header">
						<i class="ti ti-chevrons-up"></i>
					</a>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="card">
				<div class="card-body">
					<form action="{{url('search-result')}}">
						<div class="d-flex align-items-center">
							<input type="text" class="form-control flex-fill me-3" value="SmartHR">
							<button type="submit" class="btn btn-primary">Search</button>
						</div>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="mb-3">Search result for "SmartHR"</h5>
					<div class="row">
						<div class="col-md-6">
							<div class="card shadow-none">
								<div class="card-body">
									<a href="#"
										class="text-info text-truncate mb-2">https://themeforest.net/search/smarthr</a>
									<p class="text-truncate line-clamb-2 mb-2">SmartHR - Html, Vue 3, Angular 17+ &
										Node HR Project Management & CRM Admin Dashboard Template</p>
									<div class="d-flex align-items-center flex-wrap row-gap-2">
										<span class="text-gray-9 me-3 pe-3 border-end">1.7K Sales</span>
										<div class="text-gray-9 d-flex align-items-center me-3 pe-3 border-end">
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-gray-2 me-1"></i>
											(45)
										</div>
										<span class="text-gray-9">$35</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card shadow-none">
								<div class="card-body">
									<a href="#"
										class="text-info text-truncate mb-2">https://themeforest.net/item/smarthr-react-admin-template/28253842</a>
									<p class="text-truncate line-clamb-2 mb-2">SmartHR - React 18+ & Html HRMS,
										Payroll, CRM and HR Project Management Admin Dashboard Template</p>
									<div class="d-flex align-items-center flex-wrap row-gap-2">
										<span class="text-gray-9 me-3 pe-3 border-end">513 Sales</span>
										<div class="text-gray-9 d-flex align-items-center me-3 pe-3 border-end">
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-gray-2 me-1"></i>
											(18)
										</div>
										<span class="text-gray-9">$35</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card shadow-none">
								<div class="card-body">
									<a href="#"
										class="text-info text-truncate mb-2">https://themeforest.net/item/smarthr-laravel-admin-template/27532539</a>
									<p class="text-truncate line-clamb-2 mb-2">SmartHR - Laravel 10 + Html HRMS,
										Payroll, CRM and HR Project Management Admin Dashboard Template</p>
									<div class="d-flex align-items-center flex-wrap row-gap-2">
										<span class="text-gray-9 me-3 pe-3 border-end">339 Sales</span>
										<div class="text-gray-9 d-flex align-items-center me-3 pe-3 border-end">
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-gray-2 me-1"></i>
											(8)
										</div>
										<span class="text-gray-9">$35</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card shadow-none">
								<div class="card-body">
									<a href="#"
										class="text-info text-truncate mb-2">https://themeforest.net/item/dhr-html-mobile-template/22227174</a>
									<p class="text-truncate line-clamb-2 mb-2">SmartHR - HRMS, Payroll, and HR
										Project Management Mobile Template (Framework7 + Bootstrap + PWA)</p>
									<div class="d-flex align-items-center">
										<span class="text-gray-9 me-3 pe-3 border-end">293 Sales</span>
										<div class="text-gray-9 d-flex align-items-center me-3 pe-3 border-end">
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-warning me-1"></i>
											<i class="ti ti-star-filled text-gray-2 me-1"></i>
											(5)
										</div>
										<span class="text-gray-9">$24</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<h5 class="mb-3">Images</h5>
					<div class="row g-3">
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-15.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-15.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-16.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-16.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-17.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-17.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-18.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-18.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-19.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-19.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-20.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-20.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-21.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-21.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-22.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-22.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-23.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-23.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-24.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-24.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-25.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-25.jpg')}}" class="rounded" alt="img">
							</a>
						</div>
						<div class="col-xl-2 col-md-4 col-6">
							<a href="{{URL::asset('build/img/media/media-26.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/media/media-26.jpg')}}" class="rounded" alt="img">
							</a>
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