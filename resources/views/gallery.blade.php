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
					<h2 class="mb-1">Gallery</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Pages
							</li>
							<li class="breadcrumb-item active" aria-current="page">Gallery</li>
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

			<!-- Gallery -->
			<div class="card">
				<div class="card-body">
					<div class="row row-gap-4 justify-content-center">
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery.jpg')}}" class="img-fluid rounded w-100" alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-06.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-06.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-07.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-07.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-08.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-08.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-09.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-09.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-05.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-05.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-10.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-10.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-11.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-11.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12">
							<a href="{{URL::asset('build/img/social/gallery-12.jpg')}}" class="glightbox gallery-item" data-gallery="gallery-group">
								<img src="{{URL::asset('build/img/social/gallery-12.jpg')}}" class="img-fluid rounded w-100"
									alt="img">
							</a>
						</div>
					</div>
				</div>
			</div>
			<!-- /Gallery -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection