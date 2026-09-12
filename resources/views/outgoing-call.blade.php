@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content pb-4">
			<!-- Breadcrumb -->
			<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">Outgoing Call</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Applications
							</li>
							<li class="breadcrumb-item active" aria-current="page">Outgoing Call</li>
						</ol>
					</nav>
				</div>
				<div class="head-icons">
					<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
						data-bs-original-title="Collapse" id="collapse-header">
						<i class="ti ti-chevrons-up"></i>
					</a>
				</div>
			</div>

			<div class="row">

				<!-- Call -->
				<div class="col-xxl-12">
					<div class="card incoming-call mb-0">
						<div class="card-body text-center d-flex flex-column justify-content-center">
							<div class="voice-call-img mb-3">
								<img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid rounded-circle" alt="img">
							</div>
							<h4 class="fs-20 fw-bold">Anthony Lewis</h4>
							<p>Calling...</p>
							<div class="d-flex align-items-center justify-content-center">
								<a href="javascript:void(0);"
									class="btn btn-light call-item p-0 d-flex align-items-center justify-content-center me-3"><i
										class="ti ti-video fs-20"></i></a>
								<a href="javascript:void(0);"
									class="btn btn-light call-item p-0 d-flex align-items-center justify-content-center me-3"><i
										class="ti ti-microphone fs-20"></i></a>
								<a href="javascript:void(0);"
									class="btn btn-danger call-item p-0 d-flex align-items-center justify-content-center me-3"><i
										class="ti ti-phone-off fs-20"></i></a>
								<a href="javascript:void(0);"
									class="btn btn-light call-item p-0 d-flex align-items-center justify-content-center me-3"><i
										class="ti ti-user-plus fs-20"></i></a>
								<a href="javascript:void(0);"
									class="btn btn-light call-item p-0 d-flex align-items-center justify-content-center"><i
										class="ti ti-volume fs-20"></i></a>
							</div>
						</div>
					</div>
				</div>
				<!-- /Call -->

			</div>

		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection