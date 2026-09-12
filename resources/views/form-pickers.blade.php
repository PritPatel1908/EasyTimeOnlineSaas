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
							<h2 class="mb-1">Form Pickers</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Forms
									</li>
									<li class="breadcrumb-item active" aria-current="page">Form Pickers</li>
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
						<!-- Flat Picker -->
						<div class="col-12 mb-6">
							<div class="card">
								<h5 class="card-header">Flatpickr</h5>
								<div class="card-body">
									<div class="row">
										<!-- Date Picker-->
										<div class="col-md-6 col-12 mb-3">
											<label for="flatpickr-date" class="form-label">Date Picker</label>
											<input type="text" class="form-control" placeholder="YYYY-MM-DD"
												id="flatpickr-date" />
										</div>
										<!-- /Date Picker -->

										<!-- Time Picker-->
										<div class="col-md-6 col-12 mb-3">
											<label for="flatpickr-time" class="form-label">Time Picker</label>
											<input type="text" class="form-control" placeholder="HH:MM"
												id="flatpickr-time" />
										</div>
										<!-- /Time Picker -->

										<!-- Datetime Picker-->
										<div class="col-md-6 col-12 mb-3">
											<label for="flatpickr-datetime" class="form-label">Datetime Picker</label>
											<input type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM"
												id="flatpickr-datetime" />
										</div>
										<!-- /Datetime Picker-->

										<!-- Multiple Dates Picker-->
										<div class="col-md-6 col-12 mb-3">
											<label for="flatpickr-multi" class="form-label">Multiple Dates Picker</label>
											<input type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM"
												id="flatpickr-multi" />
										</div>
										<!-- /Multiple Dates Picker-->

										<!-- Range Picker-->
										<div class="col-md-6 col-12 mb-3">
											<label for="flatpickr-range" class="form-label">Range Picker</label>
											<input type="text" class="form-control" placeholder="YYYY-MM-DD to YYYY-MM-DD"
												id="flatpickr-range" />
										</div>
										<!-- /Range Picker-->

										<!-- Human Friendly Date Picker-->
										<div class="col-md-6 col-12 mb-3">
											<label for="flatpickr-human-friendly" class="form-label">Human Friendly Date
												Picker</label>
											<input type="text" class="form-control" placeholder="Month DD, YYYY"
												id="flatpickr-human-friendly" />
										</div>
										<!-- /Human Friendly Date Picker-->

										<!-- Disabled Range-->
										<div class="col-md-6 col-12 mb-md-0 mb-3">
											<label for="flatpickr-disabled-range" class="form-label">Disabled Range</label>
											<input type="text" class="form-control" placeholder="YYYY-MM-DD"
												id="flatpickr-disabled-range" />
										</div>
										<!-- /Disabled Range-->

										<!-- Inline Picker-->
										<div class="col-md-6 col-12">
											<label for="flatpickr-inline" class="form-label">Inline Picker</label>
											<input type="text" class="form-control mb-1" placeholder="YYYY-MM-DD"
												id="flatpickr-inline" />
										</div>
										<!-- /Inline Picker-->
									</div>
								</div>
							</div>
						</div>
						<!-- /Flatpickr -->
					</div>

				</div>
			</div>

		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection