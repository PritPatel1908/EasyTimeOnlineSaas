@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

			<div class="page-wrapper">

				<!-- Start Content -->
				<div class="content">

					<!-- Breadcrumb -->
					<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
						<div class="my-auto mb-2">
							<h2 class="mb-1">Settings</h2>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
									</li>
									<li class="breadcrumb-item">
										Other Settings
									</li>
									<li class="breadcrumb-item active" aria-current="page">Custom CSS</li>
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

					<ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
						<li class="nav-item">
							<a class="nav-link" href="{{url('profile-settings')}}"><i class="ti ti-settings me-2"></i>General
								Settings</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="{{url('business-settings')}}"><i class="ti ti-world-cog me-2"></i>Website
								Settings</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="{{url('salary-settings')}}"><i
									class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="{{url('email-settings')}}"><i class="ti ti-server-cog me-2"></i>System
								Settings</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="{{url('payment-gateways')}}"><i
									class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
						</li>
						<li class="nav-item">
							<a class="nav-link active" href="{{url('custom-css')}}"><i class="ti ti-settings-2 me-2"></i>Other
								Settings</a>
						</li>
					</ul>
					<div class="row">
						<div class="col-xl-3 theiaStickySidebar">
							<div class="card">
								<div class="card-body">
									<div class="d-flex flex-column list-group settings-list">
										<a href="{{url('custom-css')}}"
											class="d-inline-flex align-items-center rounded active py-2 px-3"><i
												class="ti ti-arrow-badge-right me-2"></i>Custom CSS</a>
										<a href="{{url('custom-js')}}"
											class="d-inline-flex align-items-center rounded py-2 px-3">Custom JS</a>
										<a href="{{url('cronjob')}}"
											class="d-inline-flex align-items-center rounded py-2 px-3">Cronjob</a>
										<a href="{{url('storage-settings')}}"
											class="d-inline-flex align-items-center rounded py-2 px-3">Storage</a>
										<a href="{{url('ban-ip-address')}}"
											class="d-inline-flex align-items-center rounded py-2 px-3">Ban IP Address</a>
										<a href="{{url('backup')}}"
											class="d-inline-flex align-items-center rounded py-2 px-3">Backup</a>
										<a href="{{url('clear-cache')}}"
											class="d-inline-flex align-items-center rounded py-2 px-3">Clear Cache</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-9">
							<div class="card">
								<div class="card-header px-0 mx-3">
									<div class="row g-3 align-items-center">
										<div class="col-md-6 col-sm-4">
											<h4>Custom CSS</h4>
										</div>
									</div>
								</div>
								<div class="card-body">
									<h5 class="mb-3">Write Custom CSS</h5>
									<div class="bg-dark text-gray-5">
										<pre class="language-markup mb-0">
	<code class="language-markup mb-0">
		.section-header {
			margin-bottom: 50px;
		}
		.section-header h2{
			font-size: 36px;
			font-weight: 700;
			color: #0A0A0A;
			margin-bottom: 14px;
		}
		.section-header h5 {
			font-size: 18px;
			color: #680A83;
			font-weight: 600;
			text-align: center;
			margin-bottom: 8px;
		}
	</code>
	</pre>
									</div>
									<div class="d-flex align-items-center justify-content-end border-top mt-3 pt-3">
										<button type="button" class="btn btn-outline-light border me-3">Cancel</button>
										<button type="submit" class="btn btn-primary">Save</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection