@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Time Card</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Time Card</li>
                </ol></nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Attendance</h5>
            </div>
            <div class="card-body">
                <form id="time_card_filter_form" method="POST" action="{{ url('time-management/time-card') }}"
                    data-default-year="{{ now()->format('Y') }}"
                    data-default-employee="{{ $isSuperAdmin ? '' : auth('tenant')->id() }}"
                    data-default-start-date="{{ now()->startOfYear()->toDateString() }}"
                    data-default-end-date="{{ now()->endOfYear()->toDateString() }}">
                    @csrf
                    <input type="hidden" name="page" value="1">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label for="time_card_year" class="form-label">Year</label>
                            <select id="time_card_year" name="year" class="form-select">
                                @foreach ($years as $availableYear)
                                    <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="time_card_employee" class="form-label">Employee</label>
                            <select id="time_card_employee" name="employee_id" class="form-select">
                                @if ($isSuperAdmin)
                                    <option value="" @selected($employeeId === '' || $employeeId === null)>All Employees</option>
                                @endif
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected((string) $employeeId === (string) $employee->id)>
                                        {{ $employee->name }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="time_card_start_date" class="form-label">From Date</label>
                            <input id="time_card_start_date" name="start_date" type="date" class="form-control"
                                min="{{ $year }}-01-01" max="{{ $year }}-12-31" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="time_card_end_date" class="form-label">To Date</label>
                            <input id="time_card_end_date" name="end_date" type="date" class="form-control"
                                min="{{ $year }}-01-01" max="{{ $year }}-12-31" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2 mb-3 d-flex gap-2">
                            <button id="time_card_reset" type="button" class="btn btn-light d-flex align-items-center">
                                <i class="ti ti-status-change me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="time_card_results" aria-live="polite">
            @include('time-management.time-card.partials.records')
        </div>
        <div id="time_card_filter_status" class="visually-hidden" role="status" aria-live="polite"></div>
    </div>
    @include('partials.footer')
</div>
@push('scripts')
<script>
    (function () {
        const form = document.getElementById('time_card_filter_form');
        const results = document.getElementById('time_card_results');
        const status = document.getElementById('time_card_filter_status');
        const year = document.getElementById('time_card_year');
        const startDate = document.getElementById('time_card_start_date');
        const endDate = document.getElementById('time_card_end_date');
        let activeRequest;

        async function filterTimeCard() {
            if (activeRequest) {
                activeRequest.abort();
            }
            const request = new AbortController();
            activeRequest = request;
            status.className = 'visually-hidden';
            status.textContent = 'Loading attendance records.';
            results.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: new URLSearchParams(new FormData(form)),
                    signal: request.signal
                });
                const data = await response.json();

                if (!response.ok) {
                    const validationMessage = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : data.message;
                    throw new Error(validationMessage || 'Unable to load attendance records.');
                }

                results.innerHTML = data.html;
                if (typeof window.initializeDataTables === 'function') {
                    window.initializeDataTables(results);
                }
                status.className = 'visually-hidden';
                status.textContent = 'Attendance records updated.';
            } catch (error) {
                if (error.name !== 'AbortError') {
                    status.className = 'alert alert-danger mt-2';
                    status.textContent = error.message || 'Unable to load attendance records.';
                }
            } finally {
                if (activeRequest === request) {
                    activeRequest = null;
                    results.removeAttribute('aria-busy');
                }
            }
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            form.elements.page.value = '1';
            filterTimeCard();
        });

        form.querySelectorAll('select, input[type="date"]').forEach(function (field) {
            field.addEventListener('change', function () {
                if (field === year) {
                    startDate.min = year.value + '-01-01';
                    startDate.max = year.value + '-12-31';
                    endDate.min = year.value + '-01-01';
                    endDate.max = year.value + '-12-31';
                    startDate.value = startDate.min;
                    endDate.value = endDate.max;
                }
                form.requestSubmit();
            });
        });

        document.getElementById('time_card_reset').addEventListener('click', function () {
            year.value = form.dataset.defaultYear;
            startDate.value = form.dataset.defaultStartDate;
            endDate.value = form.dataset.defaultEndDate;
            form.querySelector('[name="employee_id"]').value = form.dataset.defaultEmployee;
            form.elements.page.value = '1';
            form.requestSubmit();
        });

        results.addEventListener('click', function (event) {
            const link = event.target.closest('.pagination a');
            if (!link) {
                return;
            }
            event.preventDefault();
            form.elements.page.value = new URL(link.href, window.location.href).searchParams.get('page') || '1';
            filterTimeCard();
        });
    })();
</script>
@endpush
@endsection
