@extends('layout.mainlayout')
@section('content')
@php $canCreate=\App\Support\TenantPermissions::userCan('Department','create'); $canImport=\App\Support\TenantPermissions::userCan('Department','import'); $canExport=\App\Support\TenantPermissions::userCan('Department','export'); @endphp
<div class="page-wrapper"><div class="content">@include('partials.flash-alerts')
    <div id="department-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
        <i class="ti ti-circle-check me-2 alert-icon"></i>
        <span class="alert-message"></span>
        <button type="button" class="btn-close" aria-label="Close"></button>
    </div><div class="d-flex justify-content-between page-breadcrumb mb-3"><div><h2 class="mb-1">Departments</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Company Structure</li><li class="breadcrumb-item active">Departments</li></ol></nav></div><div class="d-flex align-items-center gap-2">@if($canExport)<a class="btn btn-light" href="{{ url('company-structure/departments/export') }}"><i class="ti ti-file-export me-2"></i>Export</a>@endif @if($canImport)<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_department_modal"><i class="ti ti-file-import me-2"></i>Import</button>@endif @if($canCreate)<a class="btn btn-primary" href="{{ url('company-structure/departments/create') }}"><i class="ti ti-circle-plus me-2"></i>Add Department</a>@endif</div></div><div class="card"><div class="card-header d-flex justify-content-between"><h5>Department List</h5><div class="dropdown"><button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button><ul class="dropdown-menu"><li><button class="dropdown-item department-status-filter" data-status="all">All</button></li><li><button class="dropdown-item department-status-filter" data-status="1">Active</button></li><li><button class="dropdown-item department-status-filter" data-status="0">Inactive</button></li></ul></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table datatable"><thead class="thead-light"><tr><th></th><th>Name</th><th>Code</th><th>Email</th><th>Companies</th><th>Locations</th><th>Status</th><th></th></tr></thead><tbody>@include('company-structure.departments.partials.rows')</tbody></table></div></div>@if($departments->hasPages())<div class="card-footer d-flex justify-content-between"><span class="text-muted">Showing {{ $departments->firstItem() }}–{{ $departments->lastItem() }} of {{ $departments->total() }} departments</span>{{ $departments->links() }}</div>@endif</div></div>@include('partials.footer')</div>
<div class="modal fade" id="delete_department_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><h4>Confirm Delete</h4><p id="delete_department_message">Are you sure you want to delete this department?</p><form id="delete_department_form" method="POST">@csrf @method('DELETE')<button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Yes, Delete</button></form></div></div></div></div>
@if ($canImport)
<div class="modal fade" id="import_department_modal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Import Departments</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="import_department_form" method="POST" action="{{ url('company-structure/departments/import') }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-body">
					<div class="mb-3">
						<label for="department_import_file" class="form-label">CSV File</label>
						<input id="department_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
						<a href="{{ url('company-structure/departments/import/sample') }}" class="d-inline-flex align-items-center mt-2">
							<i class="ti ti-download me-1"></i>Download Sample CSV
						</a>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_department_duplicates" value="1">
						<label class="form-check-label" for="update_department_duplicates">
							Update Duplicate Records
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">
						<i class="ti ti-file-import me-1"></i>Import
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
@push('scripts')
<script>
    function showDepartmentStatusAlert(alert, type, message) {
        alert.className = 'alert alert-' + type + ' alert-dismissible d-flex align-items-center';
        alert.querySelector('.alert-icon').className = type === 'success'
            ? 'ti ti-circle-check me-2 alert-icon'
            : 'ti ti-alert-circle me-2 alert-icon';
        alert.querySelector('.alert-message').textContent = message;
        alert.classList.remove('d-none');
    }

    function startDepartmentAutoDismissAlert(alert) {
        if (alert.autoDismissTimer) {
            window.clearTimeout(alert.autoDismissTimer);
        }

        var remaining = 5000;
        var timer;
        var startedAt;

        function dismiss() {
            if (alert.id === 'department-status-alert') {
                alert.classList.add('d-none');
            } else {
                alert.remove();
            }
        }

        function startTimer() {
            startedAt = Date.now();
            timer = window.setTimeout(dismiss, remaining);
            alert.autoDismissTimer = timer;
        }

        alert.addEventListener('mouseenter', function () {
            if (timer) {
                window.clearTimeout(timer);
                remaining = Math.max(0, remaining - (Date.now() - startedAt));
                timer = null;
            }
        });

        alert.addEventListener('mouseleave', function () {
            if (!timer && remaining > 0) {
                startTimer();
            }
        });

        startTimer();
    }

    function refreshDepartmentTable() {
        var status = 'all';
        fetch('{{ url('company-structure/departments/filter') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to refresh departments.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                var table = document.querySelector('table.datatable');
                var sourceBody = table.querySelector('tbody');
                sourceBody.innerHTML = data.html;
            })
            .catch(function (error) {
                console.error('Error refreshing department table:', error);
            });
    }

    // Close alert button
    document.querySelector('#department-status-alert .btn-close').addEventListener('click', function () {
        var alert = this.closest('.alert');
        if (alert.autoDismissTimer) {
            window.clearTimeout(alert.autoDismissTimer);
            alert.autoDismissTimer = null;
        }
        alert.classList.add('d-none');
    });

    // Handle import form submission via AJAX
    document.getElementById('import_department_form').addEventListener('submit', function (e) {
        e.preventDefault();

        var form = this;
        var formData = new FormData(form);
        var alert = document.getElementById('department-status-alert');
        var submitBtn = form.querySelector('button[type="submit"]');
        var originalBtnText = submitBtn.innerHTML;
        var modalElement = document.querySelector('#import_department_modal');
        var bootstrapModal = null;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader-2 me-1 animate-spin"></i>Importing...';

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.ok || response.status === 302) {
                    showDepartmentStatusAlert(alert, 'success', 'Department import has started. Table will refresh automatically...');
                    startDepartmentAutoDismissAlert(alert);

                    // Reset form
                    form.reset();

                    // Close modal using Bootstrap API
                    try {
                        bootstrapModal = bootstrap.Modal.getInstance(modalElement);
                        if (bootstrapModal) {
                            bootstrapModal.hide();
                        }
                    } catch (err) {
                        console.log('Modal close error:', err);
                        // Fallback manual close
                        modalElement.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        var backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                    }

                    // Start polling for table refresh
                    var retryCount = 0;
                    var maxRetries = 120;
                    var initialCount = document.querySelectorAll('table.datatable tbody tr').length;

                    var refreshInterval = setInterval(function () {
                        retryCount++;

                        fetch(window.location.href, {
                            method: 'GET',
                            headers: { 'Accept': 'text/html' }
                        })
                            .then(r => r.text())
                            .then(html => {
                                var parser = new DOMParser();
                                var doc = parser.parseFromString(html, 'text/html');
                                var newTable = doc.querySelector('table.datatable tbody');

                                if (newTable) {
                                    var currentTable = document.querySelector('table.datatable tbody');
                                    var newHtml = newTable.innerHTML;
                                    var oldHtml = currentTable.innerHTML;

                                    if (newHtml !== oldHtml) {
                                        currentTable.innerHTML = newHtml;
                                        console.log('Table refreshed successfully');
                                        clearInterval(refreshInterval);
                                    }
                                }
                            })
                            .catch(err => console.error('Refresh error:', err));

                        if (retryCount >= maxRetries) {
                            clearInterval(refreshInterval);
                        }
                    }, 1500);

                } else {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Import failed. Please try again.');
                    });
                }
            })
            .catch(function (error) {
                showDepartmentStatusAlert(alert, 'danger', error.message);
                startDepartmentAutoDismissAlert(alert);
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
    });

    // Delete modal handling
    document.addEventListener('click', function(e) {
        var b = e.target.closest('.delete-department-btn');
        if (b) {
            document.getElementById('delete_department_form').action = b.dataset.url;
            document.getElementById('delete_department_message').textContent = 'Are you sure you want to delete "' + b.dataset.name + '"?';
        }
    });

    // Status filter handling
    document.querySelectorAll('.department-status-filter').forEach(function(b) {
        b.addEventListener('click', function() {
            fetch('{{ url('company-structure/departments/filter') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({status: b.dataset.status})
            })
                .then(r => r.json())
                .then(d => {
                    document.querySelector('table.datatable tbody').innerHTML = d.html;
                });
        });
    });
</script>
@endpush
@endsection
