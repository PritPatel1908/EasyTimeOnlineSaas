@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">
			<div class="page-header">
				<div class="page-title">
					<h3>Breadcrumb</h3>
				</div>
			</div>


			<!-- start row -->
			<div class="row">

				<div class="col-xl-6">
					<div class="card">
						<div class="card-header">
							<h5 class="card-title">Default Breadcrumb</h5>
						</div>
						<div class="card-body  py-2">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 py-2">
									<li class="breadcrumb-item active" aria-current="page">Home</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 py-2">
									<li class="breadcrumb-item"><a href="#">Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Library</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 py-2">
									<li class="breadcrumb-item"><a href="#">Home</a></li>
									<li class="breadcrumb-item"><a href="#">Library</a></li>
									<li class="breadcrumb-item active" aria-current="page">Data</li>
								</ol>
							</nav>
						</div> <!-- end card-body -->
					</div> <!-- end card-->
				</div> <!-- end col -->

				<div class="col-xl-6">
					<div class="card">
						<div class="card-header">
							<h5 class="card-title">Breadcrumb with Icons</h5>
						</div>
						<div class="card-body  py-2">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb py-2 mb-0">
									<li class="breadcrumb-item active" aria-current="page"><i
											class="ti ti-smart-home fs-16 me-1"></i>Home</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb py-2 mb-0">
									<li class="breadcrumb-item"><a href="#"><i
												class="ti ti-smart-home fs-16 me-1"></i>Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Library</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb py-2 mb-0">
									<li class="breadcrumb-item"><a href="#"><i
												class="ti ti-smart-home fs-16 me-1"></i>Home</a></li>
									<li class="breadcrumb-item"><a href="#">Library</a></li>
									<li class="breadcrumb-item active" aria-current="page">Data</li>
								</ol>
							</nav>
						</div> <!-- end card-body -->
					</div> <!-- end card-->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">

				<div class="col-xl-6">
					<div class="card">
						<div class="card-header">
							<h5 class="card-title">Arrow Style</h5>
						</div>
						<div class="card-body py-2">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-arrow mb-0 py-2">
									<li class="breadcrumb-item active" aria-current="page">Home</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-arrow mb-0 py-2">
									<li class="breadcrumb-item"><a href="#">Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Library</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-arrow mb-0 py-2">
									<li class="breadcrumb-item"><a href="#">Home</a></li>
									<li class="breadcrumb-item"><a href="#">Library</a></li>
									<li class="breadcrumb-item active" aria-current="page">Data</li>
								</ol>
							</nav>
						</div> <!-- end card-body -->
					</div> <!-- end card-->
				</div> <!-- end col -->

				<div class="col-xl-6">
					<div class="card">
						<div class="card-header">
							<h5 class="card-title">Pipe Style</h5>
						</div>
						<div class="card-body py-2">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-pipe py-2 mb-0">
									<li class="breadcrumb-item active" aria-current="page">Home</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-pipe py-2 mb-0">
									<li class="breadcrumb-item"><a href="#">Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Library</li>
								</ol>
							</nav>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-pipe py-2 mb-0">
									<li class="breadcrumb-item"><a href="#">Home</a></li>
									<li class="breadcrumb-item"><a href="#">Library</a></li>
									<li class="breadcrumb-item active" aria-current="page">Data</li>
								</ol>
							</nav>
						</div> <!-- end card-body -->
					</div> <!-- end card-->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection