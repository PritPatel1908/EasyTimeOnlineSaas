@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">
			@include('partials.flash-alerts')

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
								System Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Email Templates</li>
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
					<a class="nav-link " href="{{url('profile-settings')}}"><i class="ti ti-settings me-2"></i>General
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link " href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="{{url('default-settings')}}"><i
							class="ti ti-server-cog me-2"></i>System Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('payment-gateways')}}"><i
							class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('custom-css')}}"><i class="ti ti-settings-2 me-2"></i>Other
						Settings</a>
				</li>
			</ul>
			<div class="row">
				<div class="col-xl-3 theiaStickySidebar">
					<div class="card">
						<div class="card-body">
							<div class="d-flex flex-column list-group settings-list">
								<a href="{{url('default-settings')}}"
										class="d-inline-flex align-items-center rounded py-2 px-3">Default Settings</a>
									<a href="{{url('email-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Email Settings</a>
								<a href="{{url('email-template')}}"
									class="d-inline-flex align-items-center rounded  active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Email Templates</a>
								<a href="{{url('sms-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Settings</a>
								<a href="{{url('sms-template')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Templates</a>
								<a href="{{url('otp-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">OTP</a>
								<a href="{{url('gdpr')}}" class="d-inline-flex align-items-center rounded py-2 px-3">GDPR
									Cookies</a>
								<a href="{{url('maintenance-mode')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Maintenance Mode</a>

							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom d-flex align-items-center justify-content-between pb-3 mb-3">
								<h4>Email Templates</h4>
								<a href="#" class="btn btn-primary" data-bs-toggle="modal"
									data-bs-target="#addtemplate"><i class="ti ti-circle-plus me-2"></i>Add
									Template</a>
							</div>
							<form action="{{ url('email-template') }}">
								<div class="border-bottom mb-3">
									<div class="row">
										@forelse ($emailTemplates as $emailTemplate)
											<div class="col-md-4">
												<div class="mb-3">
													<div class="card">
														<div class="card-body p-3">
															<div class="d-flex align-items-center justify-content-between">
																<h6>{{ $emailTemplate->title ?: \Illuminate\Support\Str::headline($emailTemplate->template_type) }}</h6>
																<div class="d-flex align-items-center">
																	<a href="#" class="btn btn-sm btn-icon"
																		data-bs-toggle="modal"
																		data-bs-target="#edittemplate"
																		data-template-edit="{{ $emailTemplate->id }}"
																		data-template-title="{{ $emailTemplate->title }}"
																		data-template-type="{{ $emailTemplate->template_type }}"
																		data-template-content="{{ $emailTemplate->email_content }}"
																		data-template-status="{{ $emailTemplate->status }}"
																		aria-label="Edit {{ $emailTemplate->title ?: $emailTemplate->template_type }}"><i class="ti ti-edit"></i></a>
																	<a href="#" class="btn btn-sm btn-icon"
																		data-bs-toggle="modal"
																		data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										@empty
											<div class="col-12">
												<p class="text-muted mb-3">No email templates found.</p>
											</div>
										@endforelse
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-end">
									<button type="button" class="btn btn-outline-light border me-3">Cancel</button>
									<button type="submit" class="btn btn-primary">Save</button>
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

    <!-- ========================
        End Page Content
    ========================= -->

@endsection

@push('scripts')
	<script>
		(function () {
			const variablesByType = @json(\App\Models\Tenant\EmailTemplate::variablesByTemplateType());
			const tokenOpen = '{' + '{ ';
			const tokenClose = ' }' + '}';

			@if ($errors->any() && old('email_template_form') === 'add')
				document.addEventListener('DOMContentLoaded', function () {
					const modal = document.getElementById('addtemplate');
					if (modal) {
						bootstrap.Modal.getOrCreateInstance(modal).show();
					}
				});
			@endif

			@if ($errors->any() && old('email_template_form') === 'edit')
				document.addEventListener('DOMContentLoaded', function () {
					const modal = document.getElementById('edittemplate');
					const templateId = @json(old('email_template_id'));
					if (modal && templateId) {
						modal.querySelector('[data-template-edit-form]').action =
							'{{ url('email-template') }}/' + encodeURIComponent(templateId);
						bootstrap.Modal.getOrCreateInstance(modal).show();
					}
				});
			@endif

			document.querySelectorAll('[data-template-edit]').forEach(function (button) {
				button.addEventListener('click', function () {
					const modal = document.getElementById('edittemplate');
					const form = modal.querySelector('[data-template-edit-form]');
					form.action = '{{ url('email-template') }}/' + encodeURIComponent(button.dataset.templateEdit);
					form.querySelector('[name="email_template_id"]').value = button.dataset.templateEdit;
					form.querySelector('[name="title"]').value = button.dataset.templateTitle || '';
					form.querySelector('[name="email_content"]').value = button.dataset.templateContent || '';

					const typeSelect = form.querySelector('[name="template_type"]');
					if (typeSelect.tomselect) {
						typeSelect.tomselect.setValue(button.dataset.templateType);
					} else {
						typeSelect.value = button.dataset.templateType;
						typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
					}

					const statusSelect = form.querySelector('[name="status"]');
					if (statusSelect.tomselect) {
						statusSelect.tomselect.setValue(button.dataset.templateStatus);
					} else {
						statusSelect.value = button.dataset.templateStatus;
					}
				});
			});

			['addtemplate', 'edittemplate'].forEach(function (modalId) {
				const modal = document.getElementById(modalId);
				if (!modal) {
					return;
				}

				const typeSelect = modal.querySelector('[data-template-type]');
				const variablePanel = modal.querySelector('[data-template-variable-panel]');
				const variableList = modal.querySelector('[data-template-variable-list]');
				const variableCount = modal.querySelector('[data-template-variable-count]');
				const contentColumn = modal.querySelector('[data-template-content-column]');
				const contentInput = modal.querySelector('textarea');

				function renderVariables() {
					const variables = variablesByType[typeSelect.value] || [];
					const hasVariables = variables.length > 0;

					variablePanel.classList.toggle('d-none', !hasVariables);
					contentColumn.classList.remove('col-lg-12', 'col-lg-9');
					contentColumn.classList.add(hasVariables ? 'col-lg-9' : 'col-lg-12');
					variableCount.textContent = hasVariables ? variables.length : '';
					variableList.replaceChildren();

					variables.forEach(function (variable) {
						const button = document.createElement('button');
						button.type = 'button';
						button.className = 'btn btn-sm btn-light border text-start w-100 mb-2 py-2';
						button.dataset.templateVariable = variable.key;
						button.setAttribute('aria-label', 'Insert ' + variable.label + ' variable');

						const label = document.createElement('span');
						label.className = 'd-block';
						label.textContent = variable.label;

						const token = document.createElement('code');
						token.className = 'd-block small text-primary mt-1';
						token.textContent = tokenOpen + variable.key + tokenClose;

						button.append(label, token);
						variableList.appendChild(button);
					});
				}

				variableList.addEventListener('click', function (event) {
					const button = event.target.closest('[data-template-variable]');
					if (!button) {
						return;
					}

					const token = tokenOpen + button.dataset.templateVariable + tokenClose;
					const start = contentInput.selectionStart;
					const end = contentInput.selectionEnd;
					contentInput.setRangeText(token, start, end, 'end');
					contentInput.focus();
				});

				contentInput.addEventListener('keydown', function (event) {
					if (event.key !== 'Backspace' || contentInput.selectionStart !== contentInput.selectionEnd) {
						return;
					}

					const caret = contentInput.selectionStart;
					const tokenPattern = /\{\{\s*[a-zA-Z_][\w.]*\s*\}\}/g;
					let match;

					while ((match = tokenPattern.exec(contentInput.value)) !== null) {
						const tokenEnd = match.index + match[0].length;
						if (caret > match.index && caret <= tokenEnd) {
							event.preventDefault();
							contentInput.setRangeText('', match.index, tokenEnd, 'start');
							break;
						}
					}
				});

				typeSelect.addEventListener('change', renderVariables);
				renderVariables();
			});
		})();
	</script>
@endpush