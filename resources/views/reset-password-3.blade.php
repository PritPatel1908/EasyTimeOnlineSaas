@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="container-fuild">
		<div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
			<div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
				<div class="col-md-4 mx-auto vh-100">
					<form action="{{url('success-3')}}" class="vh-100">
						<div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
							<div class=" mx-auto mb-5 text-center">
								<img src="{{URL::asset('build/img/logo.svg')}}" class="img-fluid" alt="Logo">
							</div>
							<div class="">
								<div class="text-center mb-3">
									<h2 class="mb-2">Reset Password</h2>
									<p class="mb-0">Your new password must be different from previous used
										passwords.</p>
								</div>
								<div>
									<div class="input-block mb-3">
										<div class="mb-3">
											<label class="form-label">Password</label>
											<div class="pass-group" id="passwordInput">
												<input type="password" class="form-control pass-input">
												<span class="ti toggle-password ti-eye-off"></span>
											</div>
										</div>
										<div class="password-strength d-flex" id="passwordStrength">
											<span id="poor"></span>
											<span id="weak"></span>
											<span id="strong"></span>
											<span id="heavy"></span>
										</div>
										<div id="passwordInfo" class="mb-2"></div>
										<p class="fs-12">Use 8 or more characters with a mix of letters, numbers &
											symbols.</p>
									</div>
									<div class="mb-3">
										<label class="form-label">Confirm Password</label>
										<div class="pass-group">
											<input type="password" class="pass-inputs form-control">
											<span class="ti toggle-passwords ti-eye-off"></span>
										</div>
									</div>
									<div class="mb-3">
										<button type="submit" class="btn btn-primary w-100">Submit</button>
									</div>
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

    <!-- ========================
        End Page Content
    ========================= -->

@endsection