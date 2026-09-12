@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="container">
		<div>
			<div class="row justify-content-center align-items-center">

				<div class="col-md-8 d-flex justify-content-center align-items-center mx-auto">
					<div>
						<div class="p-4 text-center">
							<img src="{{URL::asset('build/img/logo.svg')}}" alt="logo" class="img-fluid">
						</div>
						<div class="error-images mb-4">
							<img src="{{URL::asset('build/img/bg/error-500.svg')}}" alt="image" class="img-fluid">
						</div>
						<div class="text-center">
							<h1 class="mb-3">Oops, something went wrong</h1>
							<p class="fs-16 text-center">Server Error 500. We apologise and are fixing the <br> problem. Please try again at a  later stage</p>
							<div class="d-flex justify-content-center pb-4">
								<a href="{{url('index')}}" class="btn btn-primary d-flex align-items-center "><i class="ti ti-arrow-left me-2"></i>Back to Dashboard</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection