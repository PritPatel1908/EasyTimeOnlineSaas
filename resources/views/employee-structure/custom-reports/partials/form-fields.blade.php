@php
    $fieldsInput = old('custom_report_fields', $customReportDetail?->custom_report_fields ?? []);
    $filterInput = old('custom_report_filter', $customReportDetail?->custom_report_filter ?? []);
    $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    if (is_string($fieldsInput)) {
        $decodedFields = json_decode($fieldsInput, true);
        $fieldsInput = json_last_error() === JSON_ERROR_NONE && is_array($decodedFields) ? $decodedFields : [];
    }
    $reportFieldRows = is_array($fieldsInput) ? array_values(array_filter($fieldsInput, 'is_array')) : [];
    $reportFieldRows = array_map(static function (array $field): array {
        return [
            'field_name' => $field['field_name'] ?? '',
            'date_time_format' => $field['date_time_format'] ?? 'default',
            'use_in_summary' => filter_var($field['use_in_summary'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }, $reportFieldRows);
    if ($reportFieldRows === []) {
        $reportFieldRows = [['field_name' => '', 'date_time_format' => 'default', 'use_in_summary' => false]];
    }
    $filterValue = is_string($filterInput) ? $filterInput : json_encode($filterInput, $jsonFlags);
    $dateRangePreset = old('date_range_preset', $customReport->exists ? 'custom' : 'today');
    $reportOrientation = old('report_orientation', $customReport->report_orientation ?? 'vertical');
    $defaultReportDate = now()->toDateString();
    $fromDate = old('from_date', $customReport->from_date?->format('Y-m-d') ?? $defaultReportDate);
    $toDate = old('to_date', $customReport->to_date?->format('Y-m-d') ?? $defaultReportDate);
@endphp
@if($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Basic Information</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="custom_report_name">Report Name <span class="text-danger" aria-hidden="true">*</span></label><input id="custom_report_name" name="report_name" class="form-control @error('report_name') is-invalid @enderror" value="{{ old('report_name', $customReport->report_name) }}" required><x-validation-error field="report_name" /></div>
    <div class="col-md-12 mb-3">
        <label class="form-label" for="custom_report_orientation">Report Orientation <span class="text-danger" aria-hidden="true">*</span></label>
        <select id="custom_report_orientation" name="report_orientation" class="form-control select @error('report_orientation') is-invalid @enderror" required>
            <option value="vertical" @selected($reportOrientation === 'vertical')>Vertical</option>
            <option value="horizontal" @selected($reportOrientation === 'horizontal')>Horizontal</option>
        </select>
        <x-validation-error field="report_orientation" />
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label" for="custom_report_date_range">Date Range <span class="text-danger" aria-hidden="true">*</span></label>
        <select id="custom_report_date_range" name="date_range_preset" class="form-control select" required>
            <option value="today" @selected($dateRangePreset === 'today')>Today</option>
            <option value="yesterday" @selected($dateRangePreset === 'yesterday')>Yesterday</option>
            <option value="this_week" @selected($dateRangePreset === 'this_week')>This Week</option>
            <option value="this_month" @selected($dateRangePreset === 'this_month')>This Month</option>
            <option value="this_quarter" @selected($dateRangePreset === 'this_quarter')>This Quarter</option>
            <option value="this_year" @selected($dateRangePreset === 'this_year')>This Year</option>
            <option value="custom" @selected($dateRangePreset === 'custom')>Custom</option>
        </select>
    </div>
    <div id="custom_report_custom_dates" class="col-12 mb-3 {{ $dateRangePreset === 'custom' ? '' : 'd-none' }}">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0"><label class="form-label" for="custom_report_from_date">From Date <span class="text-danger" aria-hidden="true">*</span></label><input id="custom_report_from_date" type="date" name="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ $fromDate }}" required><x-validation-error field="from_date" /></div>
            <div class="col-md-6"><label class="form-label" for="custom_report_to_date">To Date <span class="text-danger" aria-hidden="true">*</span></label><input id="custom_report_to_date" type="date" name="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ $toDate }}" required><x-validation-error field="to_date" /></div>
        </div>
    </div>
</div></div></div>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Custom Report Fields</h5></div>
    <div class="card-body">
        <label class="form-label">Custom Report Field <span class="text-danger" aria-hidden="true">*</span></label>
        <input id="custom_report_fields" type="hidden" name="custom_report_fields" value="[]">
        <div id="custom_report_field_rows" class="mt-2"></div>
        <div id="custom_report_fields_client_error" class="text-danger d-none mb-2" role="alert">Add at least one report field.</div>
        <x-validation-error field="custom_report_fields" />
        <button id="add_custom_report_field" type="button" class="btn btn-outline-primary btn-sm"><i class="ti ti-plus me-1"></i>Add New Field</button>
    </div>
</div>
<template id="custom_report_field_template">
    <div class="border rounded p-3 mb-3" data-custom-report-field-row>
        <div class="row align-items-end">
            <div class="col-md-4 mb-3 mb-md-0">
                <label class="form-label">Field Name <span class="text-danger" aria-hidden="true">*</span></label>
                <select class="form-control" data-field-name data-report-search-select required>
                    <option value="">Select Field</option>
                    @foreach(\App\Support\CustomReportFieldOptions::all() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <label class="form-label">Date/Time Format <span class="text-danger" aria-hidden="true">*</span></label>
                <select class="form-control" data-date-time-format data-report-search-select required>
                    <option value="default">Default</option>
                    <option value="d-m-Y">DD-MM-YYYY</option>
                    <option value="m-d-Y">MM-DD-YYYY</option>
                    <option value="Y-m-d">YYYY-MM-DD</option>
                    <option value="d/m/Y">DD/MM/YYYY</option>
                    <option value="m/d/Y">MM/DD/YYYY</option>
                    <option value="H:i">24-hour time (HH:mm)</option>
                    <option value="h:i A">12-hour time (hh:mm AM/PM)</option>
                    <option value="d-m-Y H:i">DD-MM-YYYY HH:mm</option>
                    <option value="Y-m-d H:i">YYYY-MM-DD HH:mm</option>
                </select>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="form-label d-block" data-summary-label>Use in Summary</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" data-use-in-summary aria-label="Use in Summary">
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-center justify-content-end gap-1 mt-3 mt-md-0">
                <button type="button" class="btn btn-light btn-sm" data-drag-handle title="Drag to reorder" aria-label="Drag to reorder field">
                    <i class="ti ti-grip-vertical"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-remove-report-field aria-label="Remove field">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Custom Report Filter</h5></div>
    <div class="card-body">
        <div class="mb-0"><label class="form-label" for="custom_report_filter">Custom Report Filter (JSON) <span class="text-danger" aria-hidden="true">*</span></label><textarea id="custom_report_filter" name="custom_report_filter" rows="8" class="form-control font-monospace @error('custom_report_filter') is-invalid @enderror" required>{{ $filterValue }}</textarea><x-validation-error field="custom_report_filter" /></div>
    </div>
</div>
@push('scripts')
<script src="{{ URL::asset('build/plugins/sortablejs/Sortable.min.js') }}"></script>
<script>
(function () {
    var rangeSelect = document.getElementById('custom_report_date_range');
    var customDates = document.getElementById('custom_report_custom_dates');
    var fromDateInput = document.getElementById('custom_report_from_date');
    var toDateInput = document.getElementById('custom_report_to_date');
    var todayValue = @json(now()->toDateString());

    function parseDate(value) {
        var parts = value.split('-').map(Number);
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatDate(date) {
        return [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');
    }

    function updateDateRange() {
        var preset = rangeSelect.value;
        customDates.classList.toggle('d-none', preset !== 'custom');

        if (preset === 'custom') {
            return;
        }

        var today = parseDate(todayValue);
        var from = new Date(today);
        var to = new Date(today);

        if (preset === 'yesterday') {
            from.setDate(from.getDate() - 1);
            to = new Date(from);
        } else if (preset === 'this_week') {
            from.setDate(from.getDate() - ((from.getDay() + 6) % 7));
        } else if (preset === 'this_month') {
            from.setDate(1);
        } else if (preset === 'this_quarter') {
            from.setMonth(Math.floor(from.getMonth() / 3) * 3, 1);
        } else if (preset === 'this_year') {
            from.setMonth(0, 1);
        }

        fromDateInput.value = formatDate(from);
        toDateInput.value = formatDate(to);
    }

    rangeSelect.addEventListener('change', updateDateRange);
    updateDateRange();
})();

(function () {
    var form = document.getElementById('custom_report_fields').closest('form');
    var rowsContainer = document.getElementById('custom_report_field_rows');
    var fieldsInput = document.getElementById('custom_report_fields');
    var addButton = document.getElementById('add_custom_report_field');
    var template = document.getElementById('custom_report_field_template');
    var clientError = document.getElementById('custom_report_fields_client_error');
    var submissionAttempted = false;
    var rowNumber = 0;
    var initialFields = @json($reportFieldRows);

    function initializeSelect(select) {
        if (typeof TomSelect !== 'undefined' && !select.tomselect) {
            new TomSelect(select, {
                width: '100%',
                searchField: ['text'],
                placeholder: 'Search or select'
            });
        }
    }

    function syncFields() {
        var fields = Array.prototype.map.call(rowsContainer.querySelectorAll('[data-custom-report-field-row]'), function (row) {
            return {
                field_name: row.querySelector('[data-field-name]').value,
                date_time_format: row.querySelector('[data-date-time-format]').value,
                use_in_summary: row.querySelector('[data-use-in-summary]').checked
            };
        });

        fieldsInput.value = JSON.stringify(fields);
        if (submissionAttempted) {
            clientError.classList.toggle('d-none', fields.length > 0);
        }
    }

    function addFieldRow(field) {
        var fragment = template.content.cloneNode(true);
        var row = fragment.querySelector('[data-custom-report-field-row]');
        var fieldName = row.querySelector('[data-field-name]');
        var dateTimeFormat = row.querySelector('[data-date-time-format]');
        var useInSummary = row.querySelector('[data-use-in-summary]');

        rowNumber += 1;
        useInSummary.id = 'custom_report_use_in_summary_' + rowNumber;
        row.querySelector('[data-summary-label]').setAttribute('for', useInSummary.id);
        fieldName.value = field.field_name || '';
        var storedFormat = field.date_time_format || 'default';
        var hasStoredFormat = Array.prototype.some.call(dateTimeFormat.options, function (option) {
            return option.value === storedFormat;
        });
        dateTimeFormat.value = hasStoredFormat ? storedFormat : 'default';
        useInSummary.checked = field.use_in_summary === true;

        rowsContainer.appendChild(fragment);
        initializeSelect(rowsContainer.lastElementChild.querySelector('[data-field-name]'));
        initializeSelect(rowsContainer.lastElementChild.querySelector('[data-date-time-format]'));
    }

    addButton.addEventListener('click', function () {
        addFieldRow({});
        syncFields();
    });

    rowsContainer.addEventListener('change', syncFields);
    rowsContainer.addEventListener('click', function (event) {
        var removeButton = event.target.closest('[data-remove-report-field]');
        if (removeButton) {
            var row = removeButton.closest('[data-custom-report-field-row]');
            var fieldName = row.querySelector('[data-field-name]');
            var dateTimeFormat = row.querySelector('[data-date-time-format]');
            if (fieldName.tomselect) fieldName.tomselect.destroy();
            if (dateTimeFormat.tomselect) dateTimeFormat.tomselect.destroy();
            row.remove();
            syncFields();
        }
    });

    form.addEventListener('submit', function (event) {
        submissionAttempted = true;
        syncFields();
        if (!rowsContainer.querySelector('[data-custom-report-field-row]')) {
            event.preventDefault();
            clientError.classList.remove('d-none');
        }
    });

    initialFields.forEach(addFieldRow);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            rowsContainer.querySelectorAll('[data-report-search-select]').forEach(initializeSelect);
        }, {once: true});
    } else {
        rowsContainer.querySelectorAll('[data-report-search-select]').forEach(initializeSelect);
    }
    syncFields();
    new Sortable(rowsContainer, {
        handle: '[data-drag-handle]',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: syncFields
    });
})();
</script>
@endpush
<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Configuration</h5></div><div class="card-body">
    <label class="form-label" for="custom_report_status">Status <span class="text-danger" aria-hidden="true">*</span></label>
    <select id="custom_report_status" name="status" class="form-control select @error('status') is-invalid @enderror" required>
        <option value="1" @selected(old('status', $customReport->status ?? 1) == 1)>Active</option>
        <option value="0" @selected(old('status', $customReport->status ?? 1) == 0)>Inactive</option>
    </select>
    <x-validation-error field="status" />
</div></div>
