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

					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col-sm-12">
								<h3 class="page-title">Dragula</h3>
							</div>
						</div>
					</div>
					<!-- /Page Header -->

					<!-- start row -->
					<div class="row">

						<div class="col-12">
							<div class="card">
								<div class="card-header border-bottom d-flex align-items-center">
									<h4 class="header-title">Simple Drag and Drop Example</h4>
								</div>
								<div class="card-body">
									<p class="text-muted mb-0">
										Just specify the data attribute <code>data-plugin='dragula'</code> to have drag and
										drop support in your container.
									</p>

									<!-- start row -->
									<div class="row" id="simple-dragula" data-plugin="dragula">

										<div class="col-md-4">
											<div class="card mb-0 mt-4 text-white bg-primary">
												<div class="card-body">
													<blockquote class="card-bodyquote mb-0">
														<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer
															posuere erat a ante.</p>
				<!-- End Content -->

				@include('partials.footer')

			</div>

		</div>
	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection