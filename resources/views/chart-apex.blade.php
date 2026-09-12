@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">
		<div class="content">

			<div class="page-wrapper cardhead">

				<!-- Start Content -->
				<div class="content ">

					<!-- Breadcrumb -->
					<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
						<div class="my-auto mb-2">
							<h2 class="mb-1">Apex Charts</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Charts
									</li>
									<li class="breadcrumb-item active" aria-current="page">Apex Charts</li>
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

					<div class="row">

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Apex Simple</h5>
								</div>
								<div class="card-body">
									<div id="s-line" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Area Chart</h5>
								</div>
								<div class="card-body">
									<div id="s-line-area" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Column Chart</h5>
								</div>
								<div class="card-body">
									<div id="s-col" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Column Stacked Chart</h5>
								</div>
								<div class="card-body">
									<div id="s-col-stacked" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->


						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Bar Chart</h5>
								</div>
								<div class="card-body">
									<div id="s-bar" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Mixed Chart</h5>
								</div>
								<div class="card-body">
									<div id="mixed-chart" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card w-100">
								<div class="card-header">
									<h5 class="card-title">Donut Chart</h5>
								</div>
								<div class="card-body">
									<div id="donut-chart" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card w-100">
								<div class="card-header">
									<h5 class="card-title">Radial Chart</h5>
								</div>
								<div class="card-body">
									<div id="radial-chart" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

					</div>
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