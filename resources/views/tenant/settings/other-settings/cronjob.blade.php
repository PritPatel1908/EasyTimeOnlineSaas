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
							<li class="breadcrumb-item active" aria-current="page">Cronjob</li>
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
					<a class="nav-link" href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('default-settings')}}"><i class="ti ti-server-cog me-2"></i>System
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
									class="d-inline-flex align-items-center rounded py-2 px-3">Custom CSS</a>
								<a href="{{url('custom-js')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Custom JS</a>
								<a href="{{url('cronjob')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Cronjob</a>
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
									<h4>Cronjob</h4>
								</div>
								<div class="col-md-6 col-sm-8">
									<div
										class="d-flex justify-content-sm-end align-items-center flex-wrap row-gap-2">
										@if (count($availableCronTasks) > 0)
											<button type="button" class="btn btn-primary" data-bs-toggle="modal"
												data-bs-target="#add_cronjob">
												<i class="ti ti-circle-plus me-2"></i>Add Cronjob
											</button>
										@endif
									</div>
								</div>
							</div>
						</div>
						<div class="card-body pb-0">
							@if (session('success'))
								<div class="alert alert-success mx-3" role="alert">{{ session('success') }}</div>
							@endif
							@if ($errors->any())
								<div class="alert alert-danger mx-3" role="alert">
									{{ $errors->first() }}
								</div>
							@endif
							<div class="card mb-3">
								<div
									class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
									<h5>Cronjob List</h5>
								</div>
								<div class="card-body p-0">
									<div class="custom-datatable-filter table-responsive">
										<table class="table">
											<thead class="thead-light">
												<tr>
													<th>Name</th>
													<th>Task</th>
													<th>Schedule</th>
													<th>Next Run</th>
													<th>Last Run</th>
													<th>Status</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
												@forelse ($cronJobs as $cronJob)
													<tr>
														<td>
															<h6 class="fw-medium">{{ $cronJob->name }}</h6>
															<small class="text-muted">
																{{ $cronJob->task_key === \App\Models\Central\TenantCronJob::TASK_LEAVE_MUSTER
																	? 'Queues leave and balance batches'
																	: 'Fetches or processes attendance punch logs' }}
															</small>
															@if ($cronJob->last_status === 'failed' && $cronJob->last_error)
																<small class="text-danger">Last error: {{ $cronJob->last_error }}</small>
															@endif
														</td>
														<td>{{ \App\Models\Central\TenantCronJob::TASKS[$cronJob->task_key] ?? $cronJob->task_key }}</td>
														<td>
															{{ $cronJob->schedule_type === 'daily'
																? 'Daily at '.substr($cronJob->schedule_time, 0, 5)
																: 'Every '.$cronJob->interval_minutes.' minutes' }}
														</td>
														<td>{{ $cronJob->enabled ? $cronJob->next_run_at?->format('d M Y H:i') : 'Paused' }}</td>
														<td>{{ $cronJob->last_run_at?->format('d M Y H:i') ?? 'Never' }}</td>
														<td>
															<span class="badge {{ $cronJob->enabled ? 'badge-success' : 'badge-secondary' }} d-flex align-items-center badge-xs">
																<i class="ti ti-point-filled me-1"></i>
																{{ $cronJob->enabled ? ucfirst($cronJob->last_status) : 'Paused' }}
															</span>
														</td>
														<td>
															<div class="action-icon d-inline-flex align-items-center">
																<form method="POST" action="{{ url('cronjob/'.$cronJob->id.'/toggle') }}">
																	@csrf
																	<button type="submit" class="btn btn-link p-0 me-2" aria-label="{{ $cronJob->enabled ? 'Pause' : 'Resume' }} cron job">
																		<i class="ti {{ $cronJob->enabled ? 'ti-player-pause' : 'ti-player-play' }}"></i>
																	</button>
																</form>
																<button type="button" class="btn btn-link p-0 me-2" data-bs-toggle="modal"
																	data-bs-target="#edit_cronjob_{{ $cronJob->id }}" aria-label="Edit cron job">
																	<i class="ti ti-edit"></i>
																</button>
																<form method="POST" action="{{ url('cronjob/'.$cronJob->id) }}"
																	onsubmit="return confirm('Delete this cron job?')">
																	@csrf
																	@method('DELETE')
																	<button type="submit" class="btn btn-link p-0" aria-label="Delete cron job">
																		<i class="ti ti-trash"></i>
																	</button>
																</form>
															</div>
														</td>
													</tr>
												@empty
													<tr>
														<td colspan="7" class="text-center py-4">No cron jobs configured yet.</td>
													</tr>
												@endforelse
											</tbody>
										</table>
									</div>
								</div>
							</div>
							@if (count($availableCronTasks) === 0)
								<p class="text-muted px-3">All supported tasks are configured. Edit or delete an existing task to change the selection.</p>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

	@if (count($availableCronTasks) > 0)
		<div class="modal fade" id="add_cronjob" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<form method="POST" action="{{ url('cronjob') }}" class="cron-job-form" data-mode="create">
						@csrf
						<div class="modal-header">
							<h5 class="modal-title">Add Cronjob</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label class="form-label" for="cron-job-name">Name</label>
								<input id="cron-job-name" class="form-control" name="name" maxlength="100" required>
							</div>
							<div class="mb-3">
								<label class="form-label" for="cron-job-task">Task</label>
								<select id="cron-job-task" class="form-control select2 cron-task-select" name="task_key" required>
									@foreach (\App\Models\Central\TenantCronJob::TASKS as $taskKey => $taskLabel)
										@php($taskAlreadyAdded = $cronJobs->contains('task_key', $taskKey))
										<option value="{{ $taskKey }}" data-label="{{ $taskLabel }}" @disabled($taskAlreadyAdded)>
											{{ $taskLabel }}{{ $taskAlreadyAdded ? ' (already added)' : '' }}
										</option>
									@endforeach
								</select>
							</div>
							@include('tenant.settings.other-settings.partials.cron-schedule-fields', ['cronJob' => null])
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Add Cronjob</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	@endif

	@foreach ($cronJobs as $cronJob)
		<div class="modal fade" id="edit_cronjob_{{ $cronJob->id }}" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<form method="POST" action="{{ url('cronjob/'.$cronJob->id) }}" class="cron-job-form" data-mode="edit" data-task="{{ $cronJob->task_key }}">
						@csrf
						@method('PUT')
						<div class="modal-header">
							<h5 class="modal-title">Edit Cronjob</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label class="form-label">Task</label>
								<select name="task_key" class="form-control select2 cron-task-select" required>
									@foreach (\App\Models\Central\TenantCronJob::TASKS as $taskKey => $taskLabel)
										@php($taskAlreadyAdded = $cronJobs->contains(fn ($otherJob) => $otherJob->id !== $cronJob->id && $otherJob->task_key === $taskKey))
										<option value="{{ $taskKey }}" data-label="{{ $taskLabel }}"
											@selected($cronJob->task_key === $taskKey) @disabled($taskAlreadyAdded)>
											{{ $taskLabel }}{{ $taskAlreadyAdded ? ' (already added)' : '' }}
										</option>
									@endforeach
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label" for="cron-job-name-{{ $cronJob->id }}">Name</label>
								<input id="cron-job-name-{{ $cronJob->id }}" class="form-control" name="name" maxlength="100" value="{{ $cronJob->name }}" required>
							</div>
							@include('tenant.settings.other-settings.partials.cron-schedule-fields', ['cronJob' => $cronJob])
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Save Changes</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	@endforeach

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.cron-task-select, .cron-schedule-type').forEach(function (select) {
				if (!select.tomselect) {
					new TomSelect(select, { width: '100%', controlInput: null, dropdownParent: 'body' });
				}
			});
		});

		document.querySelectorAll('.cron-job-form').forEach(function (form) {
			const taskSelect = form.querySelector('.cron-task-select');
			const scheduleType = form.querySelector('.cron-schedule-type');
			const dailyFields = form.querySelector('.cron-daily-fields');
			const intervalFields = form.querySelector('.cron-interval-fields');
			const intervalOption = scheduleType.querySelector('option[value="interval"]');
			const punchTaskKey = '{{ \App\Models\Central\TenantCronJob::TASK_PUNCH_PROCESS }}';
			const nameInput = form.querySelector('[name="name"]');

			function setScheduleType(value) {
				if (scheduleType.tomselect) {
					scheduleType.tomselect.setValue(value, true);
				} else {
					scheduleType.value = value;
				}
			}

			function refreshScheduleFields() {
				const taskKey = taskSelect ? taskSelect.value : form.dataset.task;
				const leaveMuster = taskKey === '{{ \App\Models\Central\TenantCronJob::TASK_LEAVE_MUSTER }}';

				intervalOption.disabled = leaveMuster;
				if (scheduleType.tomselect) {
					const intervalData = Object.assign({}, scheduleType.tomselect.options.interval, {
						disabled: leaveMuster
					});
					scheduleType.tomselect.updateOption('interval', intervalData);
				}
				if (leaveMuster && scheduleType.value === 'interval') {
					setScheduleType('daily');
				}

				const daily = scheduleType.value === 'daily';
				dailyFields.hidden = !daily;
				intervalFields.hidden = daily;
				dailyFields.querySelector('input').required = daily;
				intervalFields.querySelector('input').required = !daily;
			}

			if (taskSelect) {
				taskSelect.addEventListener('change', function () {
					if (form.dataset.mode === 'create') {
						nameInput.value = taskSelect.selectedOptions[0].dataset.label;
					}
					refreshScheduleFields();
					setScheduleType(taskSelect.value === punchTaskKey ? 'interval' : 'daily');
					refreshScheduleFields();
				});
				if (form.dataset.mode === 'create') {
					nameInput.value = taskSelect.selectedOptions[0].dataset.label;
				}
				if (form.dataset.mode === 'create' && taskSelect.value === punchTaskKey) {
					scheduleType.value = 'interval';
				}
			}
			scheduleType.addEventListener('change', refreshScheduleFields);
			refreshScheduleFields();
		});
	</script>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection