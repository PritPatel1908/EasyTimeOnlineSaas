@php
    $flashAlerts = [
        'success' => ['icon' => 'circle-check', 'class' => 'success'],
        'error' => ['icon' => 'alert-circle', 'class' => 'danger'],
        'warning' => ['icon' => 'alert-triangle', 'class' => 'warning'],
        'info' => ['icon' => 'info-circle', 'class' => 'info'],
    ];
@endphp

@foreach ($flashAlerts as $key => $alert)
    @if (session($key))
        <div class="alert alert-{{ $alert['class'] }} alert-dismissible fade show auto-dismiss-alert d-flex align-items-center" role="alert">
            <i class="ti ti-{{ $alert['icon'] }} me-2"></i>
            <span>{{ session($key) }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show auto-dismiss-alert d-flex align-items-center" role="alert">
        <i class="ti ti-alert-circle me-2"></i>
        <span>{{ $errors->first() }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@once
    @push('scripts')
        <script>
            document.querySelectorAll('.auto-dismiss-alert').forEach(function (alert) {
                var remaining = 5000;
                var timer;
                var startedAt;

                function startTimer() {
                    startedAt = Date.now();
                    timer = window.setTimeout(function () {
                        alert.remove();
                    }, remaining);
                }

                alert.addEventListener('mouseenter', function () {
                    window.clearTimeout(timer);
                    remaining = Math.max(0, remaining - (Date.now() - startedAt));
                });

                alert.addEventListener('mouseleave', function () {
                    if (remaining > 0) {
                        startTimer();
                    }
                });

                startTimer();
            });
        </script>
    @endpush
@endonce