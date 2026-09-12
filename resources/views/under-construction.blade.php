@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">
		<div class="content">

			<div class="container">
				<div>
					<!-- Page Wrapper -->
					<div class="Page-wrapper">
						<div class="row justify-content-center align-items-center">
							<div class="col-md-8 d-flex justify-content-center align-items-center mx-auto">
								<div>
									<div class="p-4 text-center">
										<img src="{{URL::asset('build/img/logo.svg')}}" alt="logo" class="img-fluid">
									</div>
									<div class="error-images mb-4">
										<img src="{{URL::asset('build/img/bg/under-construction.svg')}}" alt="image" class="img-fluid">
									</div>
									<div class="text-center">
										<h1 class="mb-3">Under Construction</h1>
										<p class="fs-16 text-center">We're currently upgrading our platform to serve
											you better <br> Stay tuned for exciting updates!
										</p>
										<div class="d-flex justify-content-center pb-4">
											<a href="{{url('index')}}" class="btn btn-primary d-flex align-items-center "><i class="ti ti-arrow-left me-2"></i>Back to Dashboard</a>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
					<!-- /Page Wrapper -->

				</div>

			</div>

		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection