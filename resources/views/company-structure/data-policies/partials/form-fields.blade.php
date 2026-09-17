@php
    $booleanFields = [
        'self_only' => 'Self only',
        'all_locations' => 'All locations',
        'all_companies' => 'All companies',
        'all_departments' => 'All departments',
        'all_teams' => 'All teams',
        'all_sub_departments' => 'All sub departments',
        'all_categories' => 'All categories',
        'all_sub_categories' => 'All sub categories',
        'all_designations' => 'All designations',
        'all_grades' => 'All grades',
        'all_units' => 'All units',
        'all_bus_routes' => 'All bus routes',
        'all_areas' => 'All areas',
        'all_canteen_facilities' => 'All canteen facilities',
        'all_machines' => 'All machines',
    ];
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
        <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $dataPolicy?->code) }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $dataPolicy?->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="1" @selected(old('status', $dataPolicy?->status ?? 1) == 1)>Active</option>
            <option value="0" @selected(old('status', $dataPolicy?->status ?? 1) == 0)>Inactive</option>
        </select>
    </div>
</div>

<div class="border rounded p-3 mb-4">
    <h6 class="mb-3">Policy scope</h6>
    <div class="row">
        @foreach ($booleanFields as $field => $label)
            <div class="col-md-4 col-sm-6 mb-2" data-policy-scope-field="{{ $field }}">
                <div class="form-check form-switch">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="{{ $field }}" name="{{ $field }}" value="1"
                        aria-checked="{{ old($field, $dataPolicy?->{$field} ?? false) ? 'true' : 'false' }}"
                        @checked(old($field, $dataPolicy?->{$field} ?? false))>
                    <label class="form-label d-block" for="{{ $field }}">{{ $label }}?</label>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div id="data-policy-relations" class="border rounded p-3">
    <h6 class="mb-3">Selected records</h6>
    <div class="row">
        @foreach ($relationOptions as $relation => $options)
            @php
                $selected = old($relation, $dataPolicy?->{$relation}?->pluck('id')->all() ?? []);
                $label = str($relation)->replace('_', ' ')->headline();
            @endphp
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $relation }}">{{ $label }}</label>
                <select id="{{ $relation }}" name="{{ $relation }}[]"
                    class="form-control select2 js-example-placeholder-multiple js-states @error($relation) is-invalid @enderror"
                    placeholder="Select" multiple>
                    @foreach ($options as $option)
                        <option value="{{ $option->id }}"
                            data-location-ids='@json($option->location_id ?? [])'
                            data-company-ids='@json($option->company_id ?? [])'
                            data-department-ids='@json($option->department_id ?? [])'
                            data-category-ids='@json($option->category_id ?? [])'
                            data-area-ids='@json($option->area_id ?? [])'
                            @selected(in_array($option->id, (array) $selected))>
                            {{ $option->name }}{{ $option->code ? ' ('.$option->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error($relation.'.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var selfOnlySwitch = document.getElementById('self_only');
        var relationSection = document.getElementById('data-policy-relations');

        if (!selfOnlySwitch || !relationSection) {
            return;
        }

        function updatePolicyScopeVisibility() {
            var selfOnly = selfOnlySwitch.checked;

            document.querySelectorAll('[data-policy-scope-field]').forEach(function (field) {
                field.hidden = selfOnly && field.dataset.policyScopeField !== 'self_only';
            });

            relationSection.hidden = selfOnly;
        }

        var dependencies = {
            companies: { locations: 'locationIds' },
            departments: { locations: 'locationIds', companies: 'companyIds' },
            sub_departments: { locations: 'locationIds', departments: 'departmentIds' },
            categories: { locations: 'locationIds' },
            sub_categories: { locations: 'locationIds', categories: 'categoryIds' },
            designations: { locations: 'locationIds', companies: 'companyIds', categories: 'categoryIds' },
            grades: { locations: 'locationIds' },
            units: { locations: 'locationIds' },
            bus_routes: { locations: 'locationIds' },
            areas: { locations: 'locationIds' },
            canteen_facilities: { locations: 'locationIds' },
            machines: { locations: 'locationIds', companies: 'companyIds', areas: 'areaIds' }
        };

        var relationSelects = {};

        function parseIds(value) {
            if (!value) {
                return [];
            }

            try {
                var parsed = JSON.parse(value);
                return Array.isArray(parsed) ? parsed.map(String) : [String(parsed)];
            } catch (error) {
                return [String(value)];
            }
        }

        function selectedIds(select) {
            return select.tomselect
                ? select.tomselect.items.map(String)
                : Array.from(select.selectedOptions).map(function (option) { return String(option.value); });
        }

        function hasMatchingParent(option, parentIds) {
            return option.some(function (id) { return parentIds.indexOf(String(id)) !== -1; });
        }

        function optionMatches(option, relationDependencies) {
            return Object.keys(relationDependencies).every(function (parentRelation) {
                var parentIds = selectedIds(relationSelects[parentRelation]);

                if (!parentIds.length) {
                    return true;
                }

                return hasMatchingParent(option[relationDependencies[parentRelation]], parentIds);
            });
        }

        function refreshRelation(relation) {
            var select = relationSelects[relation];
            var relationOptions = select.policyOptions.filter(function (option) {
                return optionMatches(option, dependencies[relation] || {});
            });
            var allowedIds = relationOptions.map(function (option) { return option.value; });
            var selected = selectedIds(select).filter(function (id) { return allowedIds.indexOf(id) !== -1; });

            if (select.tomselect) {
                select.tomselect.clearOptions();
                select.tomselect.addOptions(relationOptions.map(function (option) {
                    return { value: option.value, text: option.text };
                }));
                select.tomselect.setValue(selected, true);
                select.tomselect.refreshOptions(false);
            }
        }

        relationSection.querySelectorAll('select').forEach(function (select) {
            relationSelects[select.id] = select;
            select.policyOptions = Array.from(select.options).map(function (option) {
                return {
                    value: String(option.value),
                    text: option.textContent.trim(),
                    locationIds: parseIds(option.dataset.locationIds),
                    companyIds: parseIds(option.dataset.companyIds),
                    departmentIds: parseIds(option.dataset.departmentIds),
                    categoryIds: parseIds(option.dataset.categoryIds),
                    areaIds: parseIds(option.dataset.areaIds)
                };
            });
        });

        Object.keys(relationSelects).forEach(function (relation) {
            relationSelects[relation].addEventListener('change', function () {
                Object.keys(relationSelects).forEach(refreshRelation);
            });
        });

        Object.keys(relationSelects).forEach(refreshRelation);

        selfOnlySwitch.addEventListener('change', updatePolicyScopeVisibility);
        updatePolicyScopeVisibility();
    });
</script>
@endpush
