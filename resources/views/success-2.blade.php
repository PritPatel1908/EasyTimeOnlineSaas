@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="container-fuild">
		<div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
			<div class="row">
				<div class="col-lg-5">
					<div
						class="d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 bg-primary-transparent">
						<div>
							<img src="{{URL::asset('build/img/bg/authentication-bg-07.svg')}}" alt="Img">
						</div>
					</div>
				</div>
				<div class="col-lg-7 col-md-12 col-sm-12">
					<div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
						<div class="col-md-7 mx-auto vh-100">
							<form action="{{url('two-step-verification-2')}}" class="vh-100">
								<div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
									<div class=" mx-auto mb-5 text-center">
										<img src="{{URL::asset('build/img/logo.svg')}}" class="img-fluid" alt="Logo">
									</div>
									<div class="">
										<div class="text-center mb-3">
											<img src="{{URL::asset('build/img/icons/success-tick.svg')}}" alt="icon"
												class="img-fluid mb-3">
											<h2 class="mb-2">Success</h2>
											<p class="mb-0">Your new password has been successfully saved</p>
										</div>
										<div class="mb-3">
											<button type="submit" class="btn btn-primary w-100">Back to Sign
												In</button>
										</div>
									</div>
									<div class="mt-5 pb-4 text-center">
										<p class="mb-0 text-gray-9">Copyright &copy; 2026 - Smarthr</p>
									</div>
								</div>
							</form>
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