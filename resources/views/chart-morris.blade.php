@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">
		<div class="content">

			<div class="page-wrapper cardhead">

				<!-- Start Content -->
				<div class="content">

					<!-- Breadcrumb -->
					<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
						<div class="my-auto mb-2">
							<h2 class="mb-1">Morris Charts</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Charts
									</li>
									<li class="breadcrumb-item active" aria-current="page">Morris Charts</li>
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
									<div class="card-title">Bar Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisBar1" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<div class="card-title">Stacked Bar Chart </div>
								</div>
								<div class="card-body">
									<div id="morrisBar3" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<div class="card-title">Line Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisLine1" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<div class="card-title">Area Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisArea1" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<div class="card-title">Line Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisBar6" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<div class="card-title">Line Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisBar7" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<div class="card-title">Donut Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisDonut1" class="chart-set"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<div class="card-title">Line Chart</div>
								</div>
								<div class="card-body">
									<div id="morrisline" class="chart-set"></div>
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