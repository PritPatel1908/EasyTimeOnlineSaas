@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="container-fuild">
		<div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
			<div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
				<div class="col-md-4 mx-auto vh-100">
					<form action="{{url('login-3')}}" class="vh-100">
						<div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
							<div class=" mx-auto mb-5 text-center">
								<img src="{{URL::asset('build/img/logo.svg')}}" class="img-fluid" alt="Logo">
							</div>
							<div class="">
								<div class="text-center mb-3">
									<h2 class="mb-2">Sign Up</h2>
									<p class="mb-0">Please enter your details to sign up</p>
								</div>
								<div class="mb-3">
									<label class="form-label">Name</label>
									<div class="input-group">
										<input type="text" value="" class="form-control border-end-0">
										<span class="input-group-text border-start-0">
											<i class="ti ti-user"></i>
										</span>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Email Address</label>
									<div class="input-group">
										<input type="text" value="" class="form-control border-end-0">
										<span class="input-group-text border-start-0">
											<i class="ti ti-mail"></i>
										</span>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Password</label>
									<div class="pass-group">
										<input type="password" class="pass-input form-control">
										<span class="ti toggle-password ti-eye-off"></span>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Confirm Password</label>
									<div class="pass-group">
										<input type="password" class="pass-inputs form-control">
										<span class="ti toggle-passwords ti-eye-off"></span>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-3">
									<div class="d-flex align-items-center">
										<div class="form-check form-check-md mb-0">
											<input class="form-check-input" id="remember_me" type="checkbox">
											<label for="remember_me" class="form-check-label mt-0">Agree to <span class="text-primary"> Terms & Privacy</span></label>
										</div>
									</div>
								</div>
								<div class="mb-3">
									<button type="submit" class="btn btn-primary w-100">Sign In</button>
								</div>
								<div class="text-center">
									<h6 class="fw-normal text-dark mb-0">Already have an account?
										<a href="{{url('login-3')}}" class="hover-a">  Sign In </a>
									</h6>
								</div>
								<div class="login-or">
									<span class="span-or">Or</span>
								</div>
								<div class="mt-2">
									<div class="d-flex align-items-center justify-content-center flex-wrap">
										<div class="text-center me-2 flex-fill">
											<a href="javascript:void(0);"
												class="br-10 p-2 btn btn-info d-flex align-items-center justify-content-center">
												<img class="img-fluid m-1" src="{{URL::asset('build/img/icons/facebook-logo.svg')}}" alt="Facebook">
											</a>
										</div>
										<div class="text-center me-2 flex-fill bg-white">
											<a href="javascript:void(0);"
												class="br-10 p-2 btn btn-outline-light border d-flex align-items-center justify-content-center">
												<img class="img-fluid m-1" src="{{URL::asset('build/img/icons/google-logo.svg')}}" alt="Google">
											</a>
										</div>
										<div class="text-center flex-fill">
											<a href="javascript:void(0);"
												class="bg-dark br-10 p-2 btn btn-dark d-flex align-items-center justify-content-center">
												<img class="img-fluid m-1" src="{{URL::asset('build/img/icons/apple-logo.svg')}}" alt="Apple">
											</a>
										</div>
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