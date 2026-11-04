@php
    $application = $application ?? null;
    $selectedUser = old('user_id', $application?->user_id);
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="user_id">Employee <span class="text-danger">*</span></label>
        <select id="user_id" name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
            <option value="">Select employee</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) $selectedUser === (string) $user->id)>
                    {{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}
                </option>
            @endforeach
        </select>
        <x-validation-error field="user_id" />
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="date">Overtime Date <span class="text-danger">*</span></label>
        <input id="date" name="date" type="date" required
            class="form-control @error('date') is-invalid @enderror"
            value="{{ old('date', $application?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
        <x-validation-error field="date" />
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="overtime_minutes">Overtime Minutes <span class="text-danger">*</span></label>
        <input id="overtime_minutes" name="overtime_minutes" type="number" min="1" max="4294967295" step="1" required readonly
            class="form-control bg-light @error('overtime_minutes') is-invalid @enderror"
            value="{{ old('overtime_minutes', $application?->overtime_minutes) }}">
        <x-validation-error field="overtime_minutes" />
        <small id="overtime_minutes_lookup_status" class="form-text text-muted" aria-live="polite"></small>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var employeeInput = document.getElementById('user_id');
        var dateInput = document.getElementById('date');
        var minutesInput = document.getElementById('overtime_minutes');
        var lookupStatus = document.getElementById('overtime_minutes_lookup_status');
        if (!employeeInput || !dateInput || !minutesInput || !lookupStatus) return;

        var lookupUrl = @json(url('time-management/overtime-applications/attendance-overtime'));
        var requestController = null;
        var requestSequence = 0;

        function lookupOvertime() {
            if (requestController) requestController.abort();
            requestSequence++;
            var sequence = requestSequence;
            var userId = employeeInput.value;
            var date = dateInput.value;
            minutesInput.value = '';
            lookupStatus.textContent = '';

            if (!userId || !date) return;

            requestController = new AbortController();
            lookupStatus.textContent = 'Checking attendance overtime...';
            var query = new URLSearchParams({ user_id: userId, date: date });

            fetch(lookupUrl + '?' + query.toString(), {
                headers: { 'Accept': 'application/json' },
                signal: requestController.signal
            }).then(function (response) {
                if (!response.ok) throw new Error('Attendance overtime lookup failed with status ' + response.status);
                return response.json();
            }).then(function (result) {
                if (sequence !== requestSequence) return;
                minutesInput.value = result.eligible ? result.overtime_minutes : '';
                lookupStatus.textContent = result.message || '';
                lookupStatus.classList.toggle('text-danger', !result.eligible);
                lookupStatus.classList.toggle('text-muted', result.eligible);
            }).catch(function (error) {
                if (error.name === 'AbortError' || sequence !== requestSequence) return;
                console.error(error);
                lookupStatus.textContent = 'Unable to check attendance overtime. Please try again.';
                lookupStatus.classList.add('text-danger');
                lookupStatus.classList.remove('text-muted');
            });
        }

        employeeInput.addEventListener('change', lookupOvertime);
        dateInput.addEventListener('change', lookupOvertime);
        lookupOvertime();
    });
    </script>
    @endpush
    <div class="col-12 mb-3">
        <label class="form-label" for="reason">Reason</label>
        <textarea id="reason" name="reason" rows="2" maxlength="255"
            class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $application?->reason) }}</textarea>
        <x-validation-error field="reason" />
    </div>
</div>
