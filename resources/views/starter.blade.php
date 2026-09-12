@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">
		<div class="content">

			<div class="page-wrapper vh-100 d-flex flex-column justify-content-between">

				<!-- Start Content -->
				<div class="content flex-fill h-100">

					<!-- Breadcrumb -->
					<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
						<div class="my-auto mb-2">
							<h2 class="mb-1">Starter </h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Pages
									</li>
									<li class="breadcrumb-item active" aria-current="page">Starter </li>
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

				</div>
				<!-- End Content -->

				@include('partials.footer')

			</div>

		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection