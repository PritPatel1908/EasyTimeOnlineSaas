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
                    data-default-start-date="{{ now()->startOfMonth()->toDateString() }}"
                    data-default-end-date="{{ now()->endOfMonth()->toDateString() }}">
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
    <div class="modal fade" id="time_card_punches_modal" tabindex="-1" aria-labelledby="time_card_punches_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="time_card_punches_title">Attendance Punches</h5>
                        <small class="text-muted" id="time_card_punches_subtitle"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="time_card_punches_body" aria-live="polite">
                    <p class="text-muted mb-0">Select a date to view its punches.</p>
                </div>
            </div>
        </div>
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
        const punchesModalElement = document.getElementById('time_card_punches_modal');
        const punchesBody = document.getElementById('time_card_punches_body');
        const punchesSubtitle = document.getElementById('time_card_punches_subtitle');
        let punchesRequest;
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
                    const selectedYear = Number(year.value);
                    const currentMonth = new Date().getMonth() + 1;
                    const monthStart = `${selectedYear}-${String(currentMonth).padStart(2, '0')}-01`;
                    const monthEndDay = new Date(selectedYear, currentMonth, 0).getDate();
                    const monthEnd = `${selectedYear}-${String(currentMonth).padStart(2, '0')}-${String(monthEndDay).padStart(2, '0')}`;

                    startDate.min = `${selectedYear}-01-01`;
                    startDate.max = `${selectedYear}-12-31`;
                    endDate.min = `${selectedYear}-01-01`;
                    endDate.max = `${selectedYear}-12-31`;
                    startDate.value = monthStart;
                    endDate.value = monthEnd;
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
            const punchButton = event.target.closest('.time-card-punches');
            if (punchButton) {
                event.preventDefault();
                if (punchesRequest) {
                    punchesRequest.abort();
                }
                const request = new AbortController();
                punchesRequest = request;
                punchesSubtitle.textContent = `${punchButton.dataset.employee} — ${punchButton.dataset.date}`;
                punchesBody.innerHTML = '<p class="text-muted mb-0">Loading punches…</p>';
                window.bootstrap.Modal.getOrCreateInstance(punchesModalElement).show();

                const body = new URLSearchParams(new FormData(form));
                body.set('punch_attendance_id', punchButton.dataset.attendanceId);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: body,
                    signal: request.signal
                })
                    .then(async function (response) {
                        const responseText = await response.text();
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch {
                            throw new Error(`Unable to load punches (HTTP ${response.status}).`);
                        }
                        if (!response.ok) {
                            throw new Error(data.message || `Unable to load punches (HTTP ${response.status}).`);
                        }
                        if (typeof data.html !== 'string') {
                            throw new Error('The server returned an invalid punch list.');
                        }
                        punchesBody.innerHTML = data.html;
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-danger mb-0';
                            alert.textContent = error.message || 'Unable to load punches.';
                            punchesBody.replaceChildren(alert);
                        }
                    })
                    .finally(function () {
                        if (punchesRequest === request) {
                            punchesRequest = null;
                        }
                    });
                return;
            }

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
