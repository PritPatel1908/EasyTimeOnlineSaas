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
							<h2 class="mb-1">C3 Charts</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Charts
									</li>
									<li class="breadcrumb-item active" aria-current="page">C3 Charts</li>
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
									<div id="chart-bar-stacked"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<div class="card-title">Multiple Bar Chart</div>
								</div>
								<div class="card-body">
									<div id="chart-bar"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<div class="card-title">Horizontal Bar Chart</div>
								</div>
								<div class="card-body">
									<div id="chart-bar-rotated"></div>
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
									<div id="chart-sracked"></div>
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
									<div id="chart-spline-rotated"></div>
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
									<div id="chart-area-spline-sracked"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<div class="card-title">Pie Chart</div>
								</div>
								<div class="card-body">
									<div id="chart-pie"></div>
								</div>
							</div>
						</div>
						<!-- /Chart -->

						<!-- Chart -->
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<div class="card-title">Donut Chart</div>
								</div>
								<div class="card-body">
									<div id="chart-donut"></div>
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