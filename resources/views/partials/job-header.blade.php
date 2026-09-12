<header class="header header-two">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <a href="{{url('index')}}" class="logo">
                    <img src="{{URL::asset('build/img/logo.svg')}}" alt="Logo">
                </a>
                <a href="{{url('index')}}" class="dark-logo">
                    <img src="{{URL::asset('build/img/logo-white.svg')}}" alt="Logo">
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{url('login')}}" class="btn btn-dark d-inline-flex align-items-center me-2"><i class="ti ti-lock me-1"></i>Log in</a>
                <a href="{{url('register')}}" class="btn btn-primary d-inline-flex align-items-center"><i class="ti ti-user-circle me-1"></i>Register</a>
            </div>
        </div>
    </div>
</header>