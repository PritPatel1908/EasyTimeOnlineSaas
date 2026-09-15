{{--
    Generic import AJAX + notification-poll + table-reload partial.

    HOW TO USE ON ANY PAGE
    ──────────────────────
    1. On your import <form> add these attributes:
         id="..."                          (any unique id, e.g. "import_company_form")
         data-import-form="true"
         data-status-alert="#company-status-alert"   ← CSS selector of the status <div>
         data-modal="#import_company_modal"           ← CSS selector of the Bootstrap modal wrapper
         data-url-match="companies"                   ← substring matched against notification url
         data-entity-label="Company"                  ← human-readable label shown in the alert

    2. @include('partials.import-poll') once anywhere inside @push('scripts').

    The partial wires itself to EVERY form that has data-import-form="true" on the page,
    so you can safely include it once even if there are multiple import forms.
--}}
<script>
(function () {
    'use strict';

    var POLL_URL      = '{{ url('/notifications/poll') }}';
    var MARK_READ_URL = '{{ url('/notifications/mark-read') }}';
    var CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]')
                            ? document.querySelector('meta[name="csrf-token"]').content
                            : '{{ csrf_token() }}';
    var POLL_MS     = 3000;   // poll every 3 s
    var MAX_RETRIES = 100;    // stop after ~5 min

    /* ─────────────────────────── helpers ─────────────────────────── */

    function showAlert(alertEl, type, message) {
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + type + ' alert-dismissible d-flex align-items-center';
        var icon = alertEl.querySelector('.alert-icon');
        if (icon) {
            icon.className = (type === 'success')
                ? 'ti ti-circle-check me-2 alert-icon'
                : 'ti ti-alert-circle me-2 alert-icon';
        }
        var msg = alertEl.querySelector('.alert-message');
        if (msg) msg.textContent = message;
        alertEl.classList.remove('d-none');
        autoDismissAlert(alertEl);
    }

    // Expose on window so other scripts on the same page can reuse it (e.g. filter handlers)
    window.importPollShowAlert = showAlert;

    function autoDismissAlert(alertEl) {
        if (!alertEl) return;
        if (alertEl._dismissTimer) clearTimeout(alertEl._dismissTimer);

        var remaining = 7000;
        var startedAt;
        var timer;

        function dismiss() { alertEl.classList.add('d-none'); }

        function startTimer() {
            startedAt = Date.now();
            timer = setTimeout(dismiss, remaining);
            alertEl._dismissTimer = timer;
        }

        alertEl.addEventListener('mouseenter', function () {
            if (timer) {
                clearTimeout(timer);
                remaining = Math.max(0, remaining - (Date.now() - startedAt));
                timer = null;
            }
        });
        alertEl.addEventListener('mouseleave', function () {
            if (!timer && remaining > 0) startTimer();
        });

        startTimer();
    }

    function closeModal(modalEl) {
        if (!modalEl) return;
        try {
            var instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) { instance.hide(); return; }
        } catch (e) {}
        // Fallback
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
        var backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
    }

    function reloadTableBody() {
        return fetch(window.location.href, { headers: { 'Accept': 'text/html' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var doc    = new DOMParser().parseFromString(html, 'text/html');
                var newTbody = doc.querySelector('table.datatable tbody');
                var curTbody = document.querySelector('table.datatable tbody');
                if (newTbody && curTbody) {
                    curTbody.innerHTML = newTbody.innerHTML;
                }
            });
    }

    /* ─────────────────────── polling engine ──────────────────────── */

    function startPolling(urlMatch, alertEl, label) {
        var retries = 0;

        var interval = setInterval(function () {
            retries++;

            fetch(POLL_URL, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var items = data.items || [];   // NOTE: endpoint returns "items", not "notifications"

                    var found = items.filter(function (item) {
                        return item.type === 'import'
                            && item.url
                            && item.url.toString().indexOf(urlMatch) !== -1;
                    });

                    if (found.length > 0) {
                        clearInterval(interval);

                        // Mark the detected notifications as read so they don't re-trigger
                        var ids = found.map(function (item) { return item.id; });
                        fetch(MARK_READ_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                            },
                            body: JSON.stringify({ ids: ids }),
                        }).catch(function () {}); // fire-and-forget

                        reloadTableBody().then(function () {
                            showAlert(alertEl, 'success',
                                (label || 'Import') + ' completed. Table has been refreshed.');
                        });
                    }
                })
                .catch(function (err) {
                    console.warn('[import-poll] poll error:', err);
                });

            if (retries >= MAX_RETRIES) {
                clearInterval(interval);
                showAlert(alertEl, 'warning',
                    'Import is taking longer than expected. Please refresh the page manually.');
            }
        }, POLL_MS);

        return interval;
    }

    /* ─────────────────── wire up each import form ─────────────────── */

    function wireForm(form) {
        var alertSel   = form.dataset.statusAlert;
        var modalSel   = form.dataset.modal;
        var urlMatch   = form.dataset.urlMatch   || window.location.pathname.split('/').pop();
        var label      = form.dataset.entityLabel || 'Import';

        var alertEl  = alertSel ? document.querySelector(alertSel) : null;
        var modalEl  = modalSel ? document.querySelector(modalSel) : null;
        var submitBtn = form.querySelector('button[type="submit"]');
        var origBtnHtml = submitBtn ? submitBtn.innerHTML : '';

        // Wire close button on the alert
        if (alertEl) {
            var closeBtn = alertEl.querySelector('.btn-close');
            if (closeBtn && !closeBtn._importPollBound) {
                closeBtn._importPollBound = true;
                closeBtn.addEventListener('click', function () {
                    if (alertEl._dismissTimer) clearTimeout(alertEl._dismissTimer);
                    alertEl.classList.add('d-none');
                });
            }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(form);
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ti ti-loader-2 me-1 animate-spin"></i>Importing...';
            }

            fetch(form.action, { method: 'POST', body: formData })
                .then(function (response) {
                    if (response.ok || response.status === 302) {
                        form.reset();
                        closeModal(modalEl);
                        showAlert(alertEl, 'success',
                            label + ' import has started. Table will refresh automatically...');
                        startPolling(urlMatch, alertEl, label);
                    } else {
                        return response.json().then(function (data) {
                            throw new Error(data.message || label + ' import failed. Please try again.');
                        });
                    }
                })
                .catch(function (err) {
                    showAlert(alertEl, 'danger', err.message);
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origBtnHtml;
                    }
                });
        });
    }

    /* ──────────────────────── boot ────────────────────────────────── */

    document.querySelectorAll('form[data-import-form="true"]').forEach(wireForm);
})();
</script>
