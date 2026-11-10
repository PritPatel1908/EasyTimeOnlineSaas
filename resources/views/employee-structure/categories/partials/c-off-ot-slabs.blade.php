@php
    $slabs = old('c_off_against_ot_slabs', $category?->c_off_against_ot_slabs?->toArray() ?? []);
    $formatHours = static function ($hours): string {
        if ($hours === null || $hours === '') {
            return '';
        }
        $totalMinutes = (int) round((float) $hours * 60);
        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    };
@endphp
<div class="col-12 mt-3 c-off-ot-slabs-wrapper">
    <div class="border rounded p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">C-Off Against OT Slabs</h6>
            <button type="button" class="btn btn-sm btn-primary" id="add-c-off-ot-slab"><i class="ti ti-plus me-1"></i>Add Slab</button>
        </div>
        <div id="c-off-ot-slabs-list">
            @foreach($slabs as $index => $slab)
                <div class="c-off-ot-slab-row border rounded p-3 mb-3" data-slab-index="{{ $index }}">
                    <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Slab <span class="ot-slab-number">{{ $index + 1 }}</span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-c-off-ot-slab"><i class="ti ti-trash me-1"></i>Remove</button></div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">From Hours</label><input type="text" inputmode="numeric" maxlength="6" name="c_off_against_ot_slabs[{{ $index }}][from_hours]" class="form-control" value="{{ $formatHours($slab['from_hours'] ?? '') }}" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,5).replace(/^(\d+)(\d{2})$/,'$1:$2');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">To Hours</label><input type="text" inputmode="numeric" maxlength="6" name="c_off_against_ot_slabs[{{ $index }}][to_hours]" class="form-control" value="{{ $formatHours($slab['to_hours'] ?? '') }}" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,5).replace(/^(\d+)(\d{2})$/,'$1:$2');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Credit Days</label><input type="number" step="0.01" min="0" name="c_off_against_ot_slabs[{{ $index }}][credit_days]" class="form-control" value="{{ $slab['credit_days'] ?? '' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <template id="c-off-ot-slab-template">
            <div class="c-off-ot-slab-row border rounded p-3 mb-3" data-slab-index="__INDEX__">
                <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Slab <span class="ot-slab-number"></span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-c-off-ot-slab"><i class="ti ti-trash me-1"></i>Remove</button></div>
                <div class="row"><div class="col-md-4 mb-3"><label class="form-label">From Hours</label><input type="text" inputmode="numeric" maxlength="6" name="c_off_against_ot_slabs[__INDEX__][from_hours]" class="form-control" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,5).replace(/^(\d+)(\d{2})$/,'$1:$2');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div><div class="col-md-4 mb-3"><label class="form-label">To Hours</label><input type="text" inputmode="numeric" maxlength="6" name="c_off_against_ot_slabs[__INDEX__][to_hours]" class="form-control" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,5).replace(/^(\d+)(\d{2})$/,'$1:$2');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div><div class="col-md-4 mb-3"><label class="form-label">Credit Days</label><input type="number" step="0.01" min="0" name="c_off_against_ot_slabs[__INDEX__][credit_days]" class="form-control"></div></div>
            </div>
        </template>
    </div>
</div>
@push('scripts')
<script>
function initializeCoffOtSlabs() {
    const addButton = document.getElementById('add-c-off-ot-slab');
    const slabsList = document.getElementById('c-off-ot-slabs-list');
    const template = document.getElementById('c-off-ot-slab-template');
    const slabsWrapper = document.querySelector('.c-off-ot-slabs-wrapper');
    const cOffRow = document.querySelector('.c-off-settings-card .card-body .row');
    let nextIndex = 0;

    slabsList?.querySelectorAll('.c-off-ot-slab-row').forEach(function (row) {
        nextIndex = Math.max(nextIndex, Number(row.dataset.slabIndex) + 1);
    });

    if (slabsWrapper && cOffRow) cOffRow.appendChild(slabsWrapper);

    function parseSlabTime(row, field) {
        const input = row.querySelector('input[name$="[' + field + ']"]');
        const match = input?.value.trim().match(/^(\d{1,3}):([0-5]\d)$/);

        return match ? Number(match[1]) * 60 + Number(match[2]) : null;
    }

    function formatSlabTime(minutes) {
        if (minutes < 0) {
            return '';
        }

        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;

        return String(hours).padStart(2, '0') + ':' + String(remainingMinutes).padStart(2, '0');
    }

    function getNextCreditDays() {
        const rows = Array.from(slabsList.querySelectorAll('.c-off-ot-slab-row'));
        const lastInput = rows.length
            ? rows[rows.length - 1].querySelector('input[name$="[credit_days]"]')
            : null;
        const lastValue = lastInput?.value.trim() ? Number(lastInput.value) : NaN;

        if (!Number.isFinite(lastValue)) {
            return null;
        }

        const previousInput = rows.length > 1
            ? rows[rows.length - 2].querySelector('input[name$="[credit_days]"]')
            : null;
        const previousValue = previousInput?.value.trim() ? Number(previousInput.value) : NaN;
        const step = Number.isFinite(previousValue) && lastValue > previousValue
            ? lastValue - previousValue
            : lastValue;

        return lastValue + step;
    }

    function refreshRows() {
        slabsList.querySelectorAll('.c-off-ot-slab-row').forEach(function (row, index) {
            row.querySelector('.ot-slab-number').textContent = index + 1;
        });
    }

    addButton?.addEventListener('click', function () {
        const lastRow = slabsList.querySelector('.c-off-ot-slab-row:last-child');
        const lastFrom = lastRow ? parseSlabTime(lastRow, 'from_hours') : null;
        const lastTo = lastRow ? parseSlabTime(lastRow, 'to_hours') : null;
        const nextCreditDays = getNextCreditDays();
        const interval = lastFrom !== null && lastTo !== null ? lastTo - lastFrom : 0;

        slabsList.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        const newRow = slabsList.querySelector('.c-off-ot-slab-row:last-child');

        if (nextCreditDays !== null) {
            newRow.querySelector('input[name$="[credit_days]"]').value = nextCreditDays.toFixed(2);
        }

        if (interval > 0) {
            newRow.querySelector('input[name$="[from_hours]"]').value = formatSlabTime(lastTo);
            newRow.querySelector('input[name$="[to_hours]"]').value = formatSlabTime(lastTo + interval);
        }

        refreshRows();
    });

    slabsList?.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-c-off-ot-slab');
        if (button) {
            button.closest('.c-off-ot-slab-row').remove();
            refreshRows();
        }
    });
    refreshRows();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCoffOtSlabs);
} else {
    initializeCoffOtSlabs();
}
</script>
@endpush
