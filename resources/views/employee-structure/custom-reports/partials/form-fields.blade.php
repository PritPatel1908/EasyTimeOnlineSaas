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
            'minute_format' => $field['minute_format'] ?? (in_array($field['date_time_format'] ?? 'default', ['H:i', 'h:i A'], true) ? 'hh_mm' : 'default'),
            'status_master_ids' => array_values(array_filter(
                array_map(
                    static fn (mixed $id): string => is_scalar($id) ? (string) $id : '',
                    is_array($field['status_master_ids'] ?? null) ? $field['status_master_ids'] : [],
                ),
                static fn (string $id): bool => $id !== '',
            )),
            'use_in_summary' => filter_var($field['use_in_summary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'half_status_count_as_half' => filter_var($field['half_status_count_as_half'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }, $reportFieldRows);
    if ($reportFieldRows === []) {
        $reportFieldRows = [['field_name' => '', 'date_time_format' => 'default', 'minute_format' => 'default', 'use_in_summary' => false, 'half_status_count_as_half' => false]];
    }
    if (is_string($filterInput)) {
        $decodedFilter = json_decode($filterInput, true);
        $filterInput = json_last_error() === JSON_ERROR_NONE && is_array($decodedFilter) ? $decodedFilter : [];
    }
    $filterSelections = is_array($filterInput) ? $filterInput : [];
    $filterValue = json_encode($filterSelections, $jsonFlags);
    $dateRangePreset = old('date_range_preset', $customReport->date_range_preset ?? 'today');
    $reportOrientation = old('report_orientation', $customReport->report_orientation ?? 'vertical');
    $summaryOrientation = old('summary_orientation', $customReport->summary_orientation ?? $reportOrientation);
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
        <label class="form-label" for="custom_report_summary_orientation">Summary Orientation <span class="text-danger" aria-hidden="true">*</span></label>
        <select id="custom_report_summary_orientation" name="summary_orientation" class="form-control select @error('summary_orientation') is-invalid @enderror" required>
            <option value="vertical" @selected($summaryOrientation === 'vertical')>Vertical</option>
            <option value="horizontal" @selected($summaryOrientation === 'horizontal')>Horizontal</option>
        </select>
        <x-validation-error field="summary_orientation" />
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label" for="custom_report_date_range">Date Range <span class="text-danger" aria-hidden="true">*</span></label>
        <select id="custom_report_date_range" name="date_range_preset" class="form-control select" required>
            <option value="today" @selected($dateRangePreset === 'today')>Today</option>
            <option value="yesterday" @selected($dateRangePreset === 'yesterday')>Yesterday</option>
            <option value="this_week" @selected($dateRangePreset === 'this_week')>This Week</option>
            <option value="last_week" @selected($dateRangePreset === 'last_week')>Last Week</option>
            <option value="this_month" @selected($dateRangePreset === 'this_month')>This Month</option>
            <option value="last_month" @selected($dateRangePreset === 'last_month')>Last Month</option>
            <option value="this_quarter" @selected($dateRangePreset === 'this_quarter')>This Quarter</option>
            <option value="last_quarter" @selected($dateRangePreset === 'last_quarter')>Last Quarter</option>
            <option value="this_year" @selected($dateRangePreset === 'this_year')>This Year</option>
            <option value="last_year" @selected($dateRangePreset === 'last_year')>Last Year</option>
            <option value="custom" @selected($dateRangePreset === 'custom')>Custom</option>
        </select>
    </div>
    <div id="custom_report_custom_dates" class="col-12 mb-3 {{ $dateRangePreset === 'custom' ? '' : 'd-none' }}">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0"><label class="form-label" for="custom_report_from_date">From Date <span class="text-danger" aria-hidden="true">*</span></label><input id="custom_report_from_date" type="date" name="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ $fromDate }}" @required($dateRangePreset === 'custom') @disabled($dateRangePreset !== 'custom')><x-validation-error field="from_date" /></div>
            <div class="col-md-6"><label class="form-label" for="custom_report_to_date">To Date <span class="text-danger" aria-hidden="true">*</span></label><input id="custom_report_to_date" type="date" name="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ $toDate }}" @required($dateRangePreset === 'custom') @disabled($dateRangePreset !== 'custom')><x-validation-error field="to_date" /></div>
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
            <div class="col-md-11">
                <div class="row">
                    <div class="col-md-12 mb-3 mb-md-0" data-field-column>
                        <label class="form-label">Field Name <span class="text-danger" aria-hidden="true">*</span></label>
                        <select class="form-control" data-field-name data-report-search-select required>
                            <option value="">Select Field</option>
                            @foreach(\App\Support\CustomReportFieldOptions::all() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 mb-md-0" data-format-column>
                        <label class="form-label" data-format-label>Date/Time Format <span class="text-danger" aria-hidden="true">*</span></label>
                        <select class="form-control" data-date-time-format data-report-search-select required>
                            <option value="default">Default</option>
                            <option value="d-m-Y" data-date-only>DD-MM-YYYY</option>
                            <option value="m-d-Y" data-date-only>MM-DD-YYYY</option>
                            <option value="Y-m-d" data-date-only>YYYY-MM-DD</option>
                            <option value="d/m/Y" data-date-only>DD/MM/YYYY</option>
                            <option value="m/d/Y" data-date-only>MM/DD/YYYY</option>
                            <option value="H:i">24-hour time (HH:mm)</option>
                            <option value="h:i A">12-hour time (hh:mm AM/PM)</option>
                            <option value="d-m-Y H:i" data-date-time>DD-MM-YYYY HH:mm</option>
                            <option value="Y-m-d H:i" data-date-time>YYYY-MM-DD HH:mm</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 mb-md-0 d-none" data-status-column>
                        <label class="form-label">Attendance Status <span class="text-danger" aria-hidden="true">*</span></label>
                        <select class="form-control" data-status-master multiple>
                            @foreach($statusMasterOptions as $statusMaster)
                                <option value="{{ $statusMaster->id }}">{{ $statusMaster->code }} - {{ $statusMaster->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 mb-md-0 d-none" data-minute-format-column>
                        <label class="form-label">Minutes Format <span class="text-danger" aria-hidden="true">*</span></label>
                        <select class="form-control" data-minute-format data-report-search-select required>
                            <option value="default">Default (Minutes)</option>
                            <option value="hh_mm">HH:mm</option>
                            <option value="decimal">Decimal</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0" data-summary-container>
                        <label class="form-label d-block" data-summary-label>Use in Summary</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" data-use-in-summary aria-label="Use in Summary">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0 d-none" data-half-status-count-container>
                        <label class="form-label d-block" data-half-status-count-label>Count half-day status as 0.5</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" data-half-status-count aria-label="Count half-day status as 0.5">
                        </div>
                    </div>
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
        <p class="text-muted">Leave the filters empty to include all employees. Multiple selections within one filter match any selected value; different filters narrow the results together. Employee selections further narrow the matching employees.</p>
        <input id="custom_report_filter" type="hidden" name="custom_report_filter" value="{{ $filterValue }}">
        <div class="row">
            @foreach($reportFilterOptions as $filterKey => $filterGroup)
                @php
                    $selectedFilterIds = array_map('strval', is_array($filterSelections[$filterKey] ?? null) ? $filterSelections[$filterKey] : []);
                @endphp
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="custom_report_filter_{{ $filterKey }}">{{ $filterGroup['label'] }}</label>
                    <select id="custom_report_filter_{{ $filterKey }}" class="form-control" data-report-filter="{{ $filterKey }}" multiple>
                        @foreach($filterGroup['options'] as $option)
                            <option value="{{ $option['id'] }}" @selected(in_array((string) $option['id'], $selectedFilterIds, true))>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
        <x-validation-error field="custom_report_filter" />
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
        var customPreset = preset === 'custom';
        customDates.classList.toggle('d-none', !customPreset);
        fromDateInput.required = customPreset;
        toDateInput.required = customPreset;
        fromDateInput.disabled = !customPreset;
        toDateInput.disabled = !customPreset;

        if (customPreset) {
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
        } else if (preset === 'last_week') {
            from.setDate(from.getDate() - ((from.getDay() + 6) % 7) - 7);
            to = new Date(from);
            to.setDate(to.getDate() + 6);
        } else if (preset === 'this_month') {
            from.setDate(1);
            to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        } else if (preset === 'last_month') {
            from.setDate(1);
            from.setMonth(from.getMonth() - 1);
            to = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (preset === 'this_quarter') {
            from.setMonth(Math.floor(from.getMonth() / 3) * 3, 1);
        } else if (preset === 'last_quarter') {
            from.setMonth(Math.floor(from.getMonth() / 3) * 3 - 3, 1);
            to = new Date(from.getFullYear(), from.getMonth() + 3, 0);
        } else if (preset === 'this_year') {
            from.setMonth(0, 1);
        } else if (preset === 'last_year') {
            from.setFullYear(from.getFullYear() - 1, 0, 1);
            to = new Date(from.getFullYear(), 11, 31);
        }

        fromDateInput.value = formatDate(from);
        toDateInput.value = formatDate(to);
    }

    rangeSelect.addEventListener('change', updateDateRange);
    updateDateRange();
})();

(function () {
    var filterInput = document.getElementById('custom_report_filter');
    var filterSelections = @json($filterSelections);
    var selects = Array.prototype.slice.call(document.querySelectorAll('[data-report-filter]'));

    function selectedValues(select) {
        if (select.tomselect) {
            var value = select.tomselect.getValue();
            return Array.isArray(value) ? value : (value ? [value] : []);
        }

        return Array.prototype.filter.call(select.options, function (option) {
            return option.selected;
        }).map(function (option) {
            return option.value;
        });
    }

    function syncFilters() {
        var filters = {};
        selects.forEach(function (select) {
            var values = selectedValues(select);
            if (values.length > 0) {
                filters[select.dataset.reportFilter] = values;
            }
        });
        filterInput.value = JSON.stringify(filters);
    }

    function initializeFilters() {
        selects.forEach(function (select) {
            var selected = Array.isArray(filterSelections[select.dataset.reportFilter])
                ? filterSelections[select.dataset.reportFilter].map(String)
                : [];
            Array.prototype.forEach.call(select.options, function (option) {
                option.selected = selected.indexOf(option.value) !== -1;
            });

            if (typeof TomSelect !== 'undefined' && !select.tomselect) {
                new TomSelect(select, {
                    plugins: ['remove_button'],
                    width: '100%',
                    searchField: ['text'],
                    placeholder: 'Search or select multiple'
                });
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target.matches('[data-report-filter]')) {
                syncFilters();
            }
        });

        syncFilters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeFilters, {once: true});
    } else {
        initializeFilters();
    }
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
    var dateOnlyFields = @json(\App\Support\CustomReportFieldOptions::dateOnlyFields());
    var dateTimeOnlyFields = @json(\App\Support\CustomReportFieldOptions::dateTimeOnlyFields());
    var timeOnlyFields = @json(\App\Support\CustomReportFieldOptions::timeOnlyFields());
    var dateTimeFields = @json(\App\Support\CustomReportFieldOptions::dateTimeFields());
    var minuteFormatFields = @json(\App\Support\CustomReportFieldOptions::minuteFormatFields());
    var summaryFields = @json(\App\Support\CustomReportFieldOptions::summaryFields());
    var statusSelectionFields = @json(\App\Support\CustomReportFieldOptions::statusSelectionFields());
    var formatOptions = Array.prototype.map.call(
        template.content.querySelector('[data-date-time-format]').options,
        function (option) {
            return {
                value: option.value,
                label: option.textContent,
                dateOnly: option.value === 'default' || option.hasAttribute('data-date-only'),
                dateTime: option.value === 'default' || option.hasAttribute('data-date-time'),
                timeOnly: ['default', 'H:i', 'h:i A'].indexOf(option.value) !== -1
            };
        }
    );

    function initializeSelect(select) {
        if (typeof TomSelect !== 'undefined' && !select.tomselect) {
            var options = {
                width: '100%',
                searchField: ['text'],
                placeholder: select.multiple ? 'Search or select multiple' : 'Search or select',
                maxOptions: null
            };
            if (select.multiple) {
                options.plugins = ['remove_button'];
            }
            new TomSelect(select, options);
        }
    }

    function selectedStatusMasterIds(select) {
        var selected = select.tomselect
            ? select.tomselect.getValue()
            : Array.prototype.filter.call(select.options, function (option) {
                return option.selected;
            }).map(function (option) {
                return option.value;
            });

        if (!Array.isArray(selected)) {
            selected = selected ? [selected] : [];
        }

        return selected.map(String);
    }

    function setStatusMasterIds(select, selectedIds) {
        var values = Array.isArray(selectedIds) ? selectedIds.map(String) : [];
        if (select.tomselect) {
            select.tomselect.setValue(values, true);
            return;
        }

        Array.prototype.forEach.call(select.options, function (option) {
            option.selected = values.indexOf(option.value) !== -1;
        });
    }

    function syncFields() {
        var fields = Array.prototype.map.call(rowsContainer.querySelectorAll('[data-custom-report-field-row]'), function (row) {
            var fieldName = row.querySelector('[data-field-name]').value;
            var supportsStatusSelection = statusSelectionFields.indexOf(fieldName) !== -1;

            return {
                field_name: fieldName,
                date_time_format: dateTimeFields.indexOf(fieldName) !== -1
                    ? row.querySelector('[data-date-time-format]').value
                    : 'default',
                minute_format: minuteFormatFields.indexOf(fieldName) !== -1
                    ? row.querySelector('[data-minute-format]').value
                    : 'default',
                status_master_ids: supportsStatusSelection
                    ? selectedStatusMasterIds(row.querySelector('[data-status-master]'))
                    : [],
                use_in_summary: summaryFields.indexOf(fieldName) !== -1
                    && row.querySelector('[data-use-in-summary]').checked,
                half_status_count_as_half: supportsStatusSelection
                    && row.querySelector('[data-half-status-count]').checked
            };
        });

        fieldsInput.value = JSON.stringify(fields);
        if (submissionAttempted) {
            clientError.classList.toggle('d-none', fields.length > 0);
        }
    }

    function updateFieldOptions(row, storedFormat, storedStatusMasterIds) {
        var fieldName = row.querySelector('[data-field-name]').value;
        var dateTimeFormat = row.querySelector('[data-date-time-format]');
        var minuteFormat = row.querySelector('[data-minute-format]');
        var summaryContainer = row.querySelector('[data-summary-container]');
        var formatColumn = row.querySelector('[data-format-column]');
        var statusColumn = row.querySelector('[data-status-column]');
        var minuteFormatColumn = row.querySelector('[data-minute-format-column]');
        var halfStatusCountContainer = row.querySelector('[data-half-status-count-container]');
        var fieldColumn = row.querySelector('[data-field-column]');
        var statusMaster = row.querySelector('[data-status-master]');
        var isDateOnly = dateOnlyFields.indexOf(fieldName) !== -1;
        var isDateTimeOnly = dateTimeOnlyFields.indexOf(fieldName) !== -1;
        var isTimeOnly = timeOnlyFields.indexOf(fieldName) !== -1;
        var supportsDateTimeFormat = dateTimeFields.indexOf(fieldName) !== -1;
        var supportsMinuteFormat = minuteFormatFields.indexOf(fieldName) !== -1;
        var supportsSummary = summaryFields.indexOf(fieldName) !== -1;
        var supportsStatusSelection = statusSelectionFields.indexOf(fieldName) !== -1;
        var selectedStatusIds = Array.isArray(storedStatusMasterIds)
            ? storedStatusMasterIds.map(String)
            : selectedStatusMasterIds(statusMaster);
        var currentFormat = supportsDateTimeFormat ? (storedFormat || dateTimeFormat.value || 'default') : 'default';
        var currentMinuteFormat = minuteFormat.value || 'default';
        row.querySelector('[data-format-label]').firstChild.textContent = isTimeOnly ? 'Time Format ' : 'Date/Time Format ';

        if (dateTimeFormat.tomselect) {
            dateTimeFormat.tomselect.destroy();
        }
        dateTimeFormat.replaceChildren();

        formatOptions.forEach(function (option) {
            if ((isDateOnly && option.dateOnly)
                || (isDateTimeOnly && option.dateTime)
                || (isTimeOnly && option.timeOnly)
                || (!isDateOnly && !isDateTimeOnly && !isTimeOnly)) {
                dateTimeFormat.add(new Option(option.label, option.value));
            }
        });

        var hasStoredFormat = Array.prototype.some.call(dateTimeFormat.options, function (option) {
            return option.value === currentFormat;
        });
        dateTimeFormat.value = hasStoredFormat ? currentFormat : 'default';
        formatColumn.classList.toggle('d-none', !supportsDateTimeFormat);
        statusColumn.classList.toggle('d-none', !supportsStatusSelection);
        minuteFormatColumn.classList.toggle('d-none', !supportsMinuteFormat);
        summaryContainer.classList.toggle('d-none', !supportsSummary);
        halfStatusCountContainer.classList.toggle('d-none', !supportsStatusSelection);
        var optionCount = Number(supportsDateTimeFormat)
            + Number(supportsStatusSelection)
            + Number(supportsMinuteFormat)
            + Number(supportsSummary)
            + Number(supportsStatusSelection);
        var columnWidth = optionCount === 0 ? 12 : (optionCount === 1 ? 6 : (optionCount === 2 ? 4 : 3));
        var columnWidths = supportsStatusSelection
            ? [6, columnWidth, 6, columnWidth, 6, 6]
            : [columnWidth, columnWidth, columnWidth, columnWidth, columnWidth, columnWidth];
        fieldColumn.classList.remove('col-md-3', 'col-md-4', 'col-md-6', 'col-md-12');
        fieldColumn.classList.add('col-md-' + columnWidths[0]);
        [formatColumn, statusColumn, minuteFormatColumn, summaryContainer, halfStatusCountContainer].forEach(function (column, index) {
            column.classList.remove('col-md-3', 'col-md-4', 'col-md-6', 'col-md-12');
            column.classList.add('col-md-' + columnWidths[index + 1]);
        });
        setStatusMasterIds(statusMaster, supportsStatusSelection ? selectedStatusIds : []);
        minuteFormat.value = Array.prototype.some.call(minuteFormat.options, function (option) {
            return option.value === currentMinuteFormat;
        }) ? currentMinuteFormat : 'default';

        if (!supportsSummary) {
            row.querySelector('[data-use-in-summary]').checked = false;
        }
        if (!supportsStatusSelection) {
            row.querySelector('[data-half-status-count]').checked = false;
        }

        initializeSelect(dateTimeFormat);
        initializeSelect(minuteFormat);
        if (supportsStatusSelection) {
            initializeSelect(statusMaster);
        }
    }

    function addFieldRow(field) {
        var fragment = template.content.cloneNode(true);
        var row = fragment.querySelector('[data-custom-report-field-row]');
        var fieldName = row.querySelector('[data-field-name]');
        var minuteFormat = row.querySelector('[data-minute-format]');
        var useInSummary = row.querySelector('[data-use-in-summary]');
        var halfStatusCount = row.querySelector('[data-half-status-count]');

        rowNumber += 1;
        useInSummary.id = 'custom_report_use_in_summary_' + rowNumber;
        row.querySelector('[data-summary-label]').setAttribute('for', useInSummary.id);
        fieldName.value = field.field_name || '';
        var storedFormat = field.date_time_format || 'default';
        minuteFormat.value = field.minute_format || 'default';
        useInSummary.checked = field.use_in_summary === true;
        halfStatusCount.checked = field.half_status_count_as_half === true;

        rowsContainer.appendChild(fragment);
        updateFieldOptions(row, storedFormat, field.status_master_ids || []);
        initializeSelect(fieldName);
    }

    addButton.addEventListener('click', function () {
        addFieldRow({});
        syncFields();
    });

    rowsContainer.addEventListener('change', function (event) {
        if (event.target.matches('[data-field-name]')) {
            updateFieldOptions(event.target.closest('[data-custom-report-field-row]'));
        }
        syncFields();
    });
    rowsContainer.addEventListener('click', function (event) {
        var removeButton = event.target.closest('[data-remove-report-field]');
        if (removeButton) {
            var row = removeButton.closest('[data-custom-report-field-row]');
            var fieldName = row.querySelector('[data-field-name]');
            var dateTimeFormat = row.querySelector('[data-date-time-format]');
            var minuteFormat = row.querySelector('[data-minute-format]');
            if (fieldName.tomselect) fieldName.tomselect.destroy();
            if (dateTimeFormat.tomselect) dateTimeFormat.tomselect.destroy();
            if (minuteFormat.tomselect) minuteFormat.tomselect.destroy();
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
    function initializeInitialSelects() {
        rowsContainer.querySelectorAll('[data-report-search-select]').forEach(initializeSelect);
        rowsContainer.querySelectorAll('[data-custom-report-field-row]').forEach(function (row) {
            var fieldName = row.querySelector('[data-field-name]').value;
            if (statusSelectionFields.indexOf(fieldName) !== -1) {
                initializeSelect(row.querySelector('[data-status-master]'));
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initializeInitialSelects();
        }, {once: true});
    } else {
        initializeInitialSelects();
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
