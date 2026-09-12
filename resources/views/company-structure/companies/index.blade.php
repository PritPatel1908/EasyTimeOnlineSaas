@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">

        {{-- Breadcrumb --}}
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Companies</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Company Structure</li>
                        <li class="breadcrumb-item active" aria-current="page">Companies</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_company_modal"
                        class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Add Company
                    </a>
                </div>
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
        </div>
        {{-- /Breadcrumb --}}

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div id="company-status-alert" class="alert d-none" role="alert"></div>

        {{-- Company List --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Company List</h5>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown me-3">
                        <a href="javascript:void(0);"
                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                            data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li>
                                <button type="button" class="dropdown-item rounded-1 company-status-filter"
                                    data-status="all">All</button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-1 company-status-filter"
                                    data-status="1">Active</button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-1 company-status-filter"
                                    data-status="0">Inactive</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>
                                <th class="no-sort">
                                    <div class="form-check form-check-md">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th>Company Name</th>
                                <th>Code</th>
                                <th>Email</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('company-structure.companies.partials.rows', ['companies' => $companies])
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($companies->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">
                        Showing {{ $companies->firstItem() }}–{{ $companies->lastItem() }} of {{ $companies->total() }} companies
                    </p>
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
        {{-- /Company List --}}

    </div>

    @include('partials.footer')
</div>

{{-- Add Company Modal --}}
<div class="modal fade" id="add_company_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Company</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form action="{{ url('company-structure/companies') }}" method="POST">
                @csrf
                <input type="hidden" name="_form" value="add">
                <div class="modal-body">
                    @include('company-structure.companies.partials.form-fields', ['company' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-circle-plus me-1"></i>Add Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- /Add Company Modal --}}

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="delete_company_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                    <i class="ti ti-trash-x fs-36"></i>
                </span>
                <h4 class="mb-1">Confirm Delete</h4>
                <p class="mb-3 text-muted" id="delete_company_message">
                    Are you sure you want to delete this company? This action cannot be undone.
                </p>
                <form id="delete_company_form" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-trash me-1"></i>Yes, Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- /Delete Confirmation Modal --}}

@push('scripts')
<script>
    // Reopen add modal with errors if validation failed on store
    @if ($errors->any() && old('_form') === 'add')
        var addModal = new bootstrap.Modal(document.getElementById('add_company_modal'));
        addModal.show();
    @endif

    // Wire up the delete modal with the correct company id and name
    document.querySelectorAll('.delete-company-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id   = this.dataset.id;
            var name = this.dataset.name;
            document.getElementById('delete_company_form').action =
                '{{ url('company-structure/companies') }}/' + id;
            document.getElementById('delete_company_message').textContent =
                'Are you sure you want to delete "' + name + '"? This action cannot be undone.';
        });
    });

    document.addEventListener('click', function (event) {
        var filterButton = event.target.closest('.company-status-filter');

        if (!filterButton || filterButton.disabled) {
            return;
        }

        event.preventDefault();

        var alert = document.getElementById('company-status-alert');
        filterButton.disabled = true;

        fetch('{{ url('company-structure/companies/filter') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: filterButton.dataset.status })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to filter companies.');
                    }

                    return data;
                });
            })
            .then(function (data) {
                var table = document.querySelector('table.datatable');
                var sourceBody = table.querySelector('tbody');
                var rowContainer = document.createElement('tbody');
                var visibleBody = document.querySelector('.gridjs-tbody');

                rowContainer.innerHTML = data.html;
                sourceBody.innerHTML = data.html;

                if (visibleBody) {
                    visibleBody.innerHTML = '';
                    rowContainer.querySelectorAll('tr').forEach(function (row) {
                        row.classList.add('gridjs-tr');
                        row.querySelectorAll('td').forEach(function (cell) {
                            cell.classList.add('gridjs-td');
                        });
                        visibleBody.appendChild(row);
                    });
                }

                var summary = document.querySelector('.gridjs-summary');
                if (summary) {
                    summary.textContent = data.count === 0
                        ? 'No records to show'
                        : 'Showing 1-' + data.count + ' of ' + data.count + ' entries';
                }

                var pages = document.querySelector('.gridjs-pages');
                if (pages) {
                    pages.innerHTML = '';
                }

                alert.className = 'alert alert-success';
                alert.textContent = 'Company status filter applied successfully.';
            })
            .catch(function (error) {
                alert.className = 'alert alert-danger';
                alert.textContent = error.message;
            })
            .finally(function () {
                filterButton.disabled = false;
            });
    }, true);

    document.addEventListener('click', function (event) {
        var statusButton = event.target.closest('.company-status-toggle');

        if (!statusButton || statusButton.disabled) {
            return;
        }

        event.preventDefault();

            var alert = document.getElementById('company-status-alert');
            var nextStatus = statusButton.dataset.status === '1' ? 0 : 1;

            statusButton.disabled = true;

            fetch('{{ url('company-structure/companies') }}/' + statusButton.dataset.id + '/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: nextStatus })
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok) {
                            throw new Error(data.message || 'Unable to update company status.');
                        }

                        return data;
                    });
                })
                .then(function (data) {
                    statusButton.dataset.status = String(data.status);
                    statusButton.classList.toggle('badge-success', data.status === 1);
                    statusButton.classList.toggle('badge-danger', data.status !== 1);
                    statusButton.querySelector('.company-status-label').textContent = data.statusLabel;
                    alert.className = 'alert alert-success';
                    alert.textContent = data.message;
                })
                .catch(function (error) {
                    alert.className = 'alert alert-danger';
                    alert.textContent = error.message;
                })
                .finally(function () {
                    statusButton.disabled = false;
                });
    }, true);
</script>
@endpush

@endsection
