@php
    $scheduleType = $cronJob?->schedule_type ?? 'daily';
@endphp

<div class="mb-3">
    <label class="form-label">Schedule type</label>
    <select name="schedule_type" class="form-control select2 cron-schedule-type" required>
        <option value="daily" @selected($scheduleType === 'daily')>Daily at a set time</option>
        <option value="interval" @selected($scheduleType === 'interval')>Repeat every interval</option>
    </select>
</div>
<div class="mb-3 cron-daily-fields" @if ($scheduleType !== 'daily') hidden @endif>
    <label class="form-label">Run time</label>
    <input type="time" name="schedule_time" class="form-control"
        value="{{ $cronJob && $cronJob->schedule_type === 'daily' ? substr($cronJob->schedule_time, 0, 5) : '00:00' }}">
    <small class="text-muted">Uses the Laravel TIMEZONE setting.</small>
</div>
<div class="mb-3 cron-interval-fields" @if ($scheduleType !== 'interval') hidden @endif>
    <label class="form-label">Repeat interval (minutes)</label>
    <input type="number" name="interval_minutes" class="form-control" min="1" max="1440"
        value="{{ $cronJob?->interval_minutes ?? 5 }}">
    <small class="text-muted">Allowed range: 1 to 1440 minutes.</small>
</div>
