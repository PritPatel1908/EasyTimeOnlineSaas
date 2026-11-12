@extends('layout.mainlayout')
@section('content')
	<div class="container-fuild">
		<div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
			<div class="row">
				<div class="col-lg-5">
					<div class="login-background position-relative d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100">
						<div class="bg-overlay-img">
							<img src="{{ URL::asset('build/img/bg/bg-01.png') }}" class="bg-1" alt="Img">
							<img src="{{ URL::asset('build/img/bg/bg-02.png') }}" class="bg-2" alt="Img">
							<img src="{{ URL::asset('build/img/bg/bg-03.png') }}" class="bg-3" alt="Img">
						</div>
						<div class="authentication-card w-100">
							<div class="authen-overlay-item border w-100">
								<h1 class="text-white fs-40 fw-bold">Empowering people <br> through seamless HR <br> management.</h1>
								<div class="my-4 mx-auto authen-overlay-img">
									<img src="{{ URL::asset('build/img/bg/authentication-bg-01.png') }}" alt="Img">
								</div>
								<p class="text-white fs-20 fw-semibold text-center">Efficiently manage your workforce, streamline <br> operations effortlessly.</p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-7 col-md-12 col-sm-12">
					<div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
						<div class="col-md-7 mx-auto">
							<div class="d-flex flex-column justify-content-between p-4">
								<div class="mx-auto mb-5 text-center">
									<img src="{{ ($companyImageUrls['company_white_logo'] ?? URL::asset('build/img/logo.svg')) }}" class="img-fluid" alt="Logo">
								</div>
								<div>
									@if ($errors->any())
										<div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
									@endif
									@if (session('success'))
										<div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
									@endif
									<div class="text-center mb-3">
										<h2 class="mb-2">Verify your sign in</h2>
										@if ($codeExpired)
											<p class="mb-0">The code sent to {{ $email }} has expired. Request a new code to continue.</p>
										@else
											<p class="mb-0">Enter the 6-digit code sent to {{ $email }}.</p>
										@endif
									</div>
									@if (! $codeExpired)
										<form method="POST" action="{{ url('/login/otp') }}">
											@csrf
											<div class="mb-3">
												<label for="otp" class="form-label">Verification code</label>
												<input id="otp" name="otp" type="text" inputmode="numeric"
													autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6"
													class="form-control text-center @error('otp') is-invalid @enderror"
													required autofocus>
												@error('otp')
													<div class="invalid-feedback">{{ $message }}</div>
												@enderror
											</div>
											<button type="submit" class="btn btn-primary w-100">Verify and Sign In</button>
										</form>
									@endif
									<form method="POST" action="{{ url('/login/otp/resend') }}" class="text-center mt-3">
										@csrf
										<button type="submit" class="btn btn-link">Send a new code</button>
									</form>
									<a href="{{ url('/login') }}" class="d-block text-center small">Use a different account</a>
								</div>
								<div class="mt-5 text-center">
									<p class="mb-0 text-gray-9">Copyright &copy; 2026 - Smarthr</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
