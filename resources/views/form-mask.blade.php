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
							<h2 class="mb-1">Form Mask</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Forms
									</li>
									<li class="breadcrumb-item active" aria-current="page">Form Mask</li>
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
						<div class="col-sm-12">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Form Mask</h5>
									<p class="sub-header">Input masks can be used to force the user to enter data conform a
										specific format. Unlike validation, the user can't enter any other key than the ones
										specified by the mask.</p>
								</div>
								<div class="card-body">
									<form action="#">
										<div class="row g-3">
											<div class="col-md-6">
												<label class="form-label">Phone</label>
												<input type="text" id="phone" class="form-control">
												<span class="form-text text-muted">(999) 999-9999</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Date</label>
												<input type="text" id="date" class="form-control">
												<span class="form-text text-muted">dd/mm/yyyy</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">SSN field 1</label>
												<input type="text" id="ssn" class="form-control">
												<span class="form-text text-muted">e.g "999-99-9999"</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Phone field + ext.</label>
												<input type="text" id="phoneExt" class="form-control">
												<span class="form-text text-muted">+40 999 999 999</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Product Key</label>
												<input type="text" id="products" class="form-control">
												<span class="form-text text-muted">e.g a*-999-a999</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Currency</label>
												<input type="text" id="currency" class="form-control">
												<span class="form-text text-muted">$ 999,999,999.99</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Eye Script</label>
												<input type="text" id="eyescript" class="form-control">
												<span class="form-text text-muted">~9.99 ~9.99 999</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Percent</label>
												<input type="text" id="pct" class="form-control">
												<span class="form-text text-muted">e.g "99%"</span>
											</div>
											<div class="col-md-6">
												<label class="form-label">Credit Card Number</label>
												<input type="text" class="form-control" id="ccn">
												<span class="form-text text-muted">e.g "999.999.999.9999"</span>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
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