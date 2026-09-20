@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
	<div class="content">
		@include('partials.flash-alerts')

		<div class="d-flex justify-content-between page-breadcrumb mb-3">
			<div>
				<h2 class="mb-1">View Designation</h2>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">Employee Structure</li>
						<li class="breadcrumb-item"><a href="{{ url('employee-structure/designations') }}">Designations</a></li>
						<li class="breadcrumb-item active">{{ $designation->name }}</li>
					</ol>
				</nav>
			</div>
			<div class="d-flex align-items-center gap-2">
				@if(\App\Support\TenantPermissions::userCan('Designation', 'write'))
					<a href="{{ url('employee-structure/designations/'.$designation->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
				@endif
				<a href="{{ url('employee-structure/designations') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4 mb-3"><small class="text-muted d-block">Designation Name</small>{{ $designation->name }}</div>
					<div class="col-md-4 mb-3"><small class="text-muted d-block">Designation Code</small>{{ $designation->code }}</div>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header"><h5 class="mb-0">Organization</h5></div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4 mb-3"><small class="text-muted d-block">Companies</small>{{ $designation->companies()->pluck('name')->join(', ') ?: '-' }}</div>
					<div class="col-md-4 mb-3"><small class="text-muted d-block">Locations</small>{{ $designation->locations()->pluck('name')->join(', ') ?: '-' }}</div>
					<div class="col-md-4 mb-3"><small class="text-muted d-block">Categories</small>{{ $designation->categories()->pluck('name')->join(', ') ?: '-' }}</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-header"><h5 class="mb-0">Configuration</h5></div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-4 mb-3"><small class="text-muted d-block">Status</small><span class="badge {{ $designation->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $designation->status === 1 ? 'Active' : 'Inactive' }}</span></div>
				</div>
			</div>
		</div>
		@include('partials.audit-users', ['record' => $designation])
	</div>
</div>
@include('partials.footer')
@endsection
