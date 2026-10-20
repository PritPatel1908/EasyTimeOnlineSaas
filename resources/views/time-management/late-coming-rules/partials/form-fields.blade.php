@php($lateComingRule = $lateComingRule ?? null)

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Rule Details</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="late_coming_rule_code">Rule Code <span class="text-danger">*</span></label>
                <input id="late_coming_rule_code" name="code" type="text" maxlength="255" required
                    class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $lateComingRule?->code) }}">
                <x-validation-error field="code" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="ignore_late_coming_minutes">Ignore Late Coming (Minutes) <span class="text-danger">*</span></label>
                <input id="ignore_late_coming_minutes" name="ignore_late_coming_minutes" type="number" min="0" max="2147483647" step="1" required
                    class="form-control @error('ignore_late_coming_minutes') is-invalid @enderror"
                    value="{{ old('ignore_late_coming_minutes', $lateComingRule?->ignore_late_coming_minutes ?? 0) }}">
                <x-validation-error field="ignore_late_coming_minutes" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="late_coming_rule_description">Description</label>
                <textarea id="late_coming_rule_description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $lateComingRule?->description) }}</textarea>
                <x-validation-error field="description" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="late_coming_rule_status">Status <span class="text-danger">*</span></label>
                <select id="late_coming_rule_status" name="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                    <option value="1" @selected(old('status', $lateComingRule?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $lateComingRule?->status ?? 1) == 0)>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
        </div>
    </div>
</div>
