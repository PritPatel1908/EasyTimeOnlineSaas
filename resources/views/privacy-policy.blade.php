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
					<h2 class="mb-1">Privacy Policy</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Pages
							</li>
							<li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
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

			<div class="card">
				<div class="card-body">
					<div class="mb-3">
						<p>At SmratHR, we value your privacy and are committed to protecting your personal data.
							This Privacy Policy explains how we collect, use, disclose, and safeguard your
							information
							when you access our HR admin platform. Please read this policy carefully.
						</p>
					</div>
					<div>
						<h5 class="mb-2">Information We Collect</h5>
						<p class="mb-2">We may collect the following types of personal information:</p>
						<ul class="list-styled mb-3">
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								<span class="fw-medium text-gray-9 me-1">Employee Data :</span>Name, contact
								information,
								job title, department, payroll details, and other employment-related data.
							</li>
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								<span class="fw-medium text-gray-9 me-1">Login Information :</span>Username,
								password, IP address, and other access credentials.
							</li>
							<li class="d-flex align-items-baseline">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								<span class="fw-medium text-gray-9 me-1">Usage Data : </span>Details about how the
								platform is used, including logs of actions taken within the HR admin module
							</li>
						</ul>
					</div>
					<div>
						<h5 class="mb-2">How We Use Your Information</h5>
						<p class="mb-2">We may use the information we collect to:</p>
						<ul class="list-styled mb-3">
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Manage employee records and HR functions.
							</li>
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Process payroll and benefits administration.
							</li>
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Ensure the security and integrity of the platform.
							</li>
							<li class="d-flex align-items-baseline">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Generate reports and analytics to improve our services.
							</li>
						</ul>
					</div>
					<div>
						<h5 class="mb-2">Sharing Your Information</h5>
						<p class="mb-2">We will not sell or share your personal information with third parties
							except:</p>
						<ul class="list-styled mb-3">
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								To authorized service providers who assist in our HR processes.
							</li>
							<li class="d-flex align-items-baseline">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								If required by law, such as in response to a court order or legal obligation.
							</li>
						</ul>
					</div>
					<div class="mb-3">
						<h5 class="mb-2">Data Security</h5>
						<p class="mb-2">We implement appropriate technical and organizational measures to protect
							your personal data from unauthorized access, alteration, or disclosure. However,
							no method of transmission over
							the internet is 100% secure, and we cannot guarantee absolute security.
						</p>
					</div>
					<div class="mb-3">
						<h5 class="mb-2">Data Retention</h5>
						<p class="mb-2">
							We will retain your personal information for as long as necessary
							to fulfill the purposes outlined in this policy, or as required by law.
						</p>
					</div>
					<div>
						<h5 class="mb-2">Your Rights</h5>
						<p class="mb-2">You may have the following rights concerning your personal data:</p>
						<ul class="list-styled mb-3">
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Access to your data.
							</li>
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Correction of inaccurate information.
							</li>
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Deletion of data where appropriate.
							</li>
							<li class="d-flex align-items-baseline mb-2">
								<i class="ti ti-point-filled fs-10 me-1"></i>
								Restriction of processing under certain circumstances.
							</li>
						</ul>
					</div>
					<div>
						<h5 class="mb-2">Changes to This Policy</h5>
						<p>
							We may update this policy from time to time. The latest version will always be available
							on the platform.
							Continued use of the HR admin platform signifies your acceptance of any updates.
						</p>
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