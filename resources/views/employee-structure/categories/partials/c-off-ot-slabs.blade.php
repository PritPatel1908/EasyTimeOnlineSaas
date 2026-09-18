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
                        <div class="col-md-4 mb-3"><label class="form-label">From Hours</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_ot_slabs[{{ $index }}][from_hours]" class="form-control" value="{{ $formatHours($slab['from_hours'] ?? '') }}" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">To Hours</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_ot_slabs[{{ $index }}][to_hours]" class="form-control" value="{{ $formatHours($slab['to_hours'] ?? '') }}" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Credit Days</label><input type="number" step="0.01" min="0" name="c_off_against_ot_slabs[{{ $index }}][credit_days]" class="form-control" value="{{ $slab['credit_days'] ?? '' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <template id="c-off-ot-slab-template">
            <div class="c-off-ot-slab-row border rounded p-3 mb-3" data-slab-index="__INDEX__">
                <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Slab <span class="ot-slab-number"></span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-c-off-ot-slab"><i class="ti ti-trash me-1"></i>Remove</button></div>
                <div class="row"><div class="col-md-4 mb-3"><label class="form-label">From Hours</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_ot_slabs[__INDEX__][from_hours]" class="form-control" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div><div class="col-md-4 mb-3"><label class="form-label">To Hours</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_ot_slabs[__INDEX__][to_hours]" class="form-control" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div><div class="col-md-4 mb-3"><label class="form-label">Credit Days</label><input type="number" step="0.01" min="0" name="c_off_against_ot_slabs[__INDEX__][credit_days]" class="form-control"></div></div>
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
    let nextIndex = slabsList?.querySelectorAll('.c-off-ot-slab-row').length || 0;

    if (slabsWrapper && cOffRow) cOffRow.appendChild(slabsWrapper);

    function refreshRows() {
        slabsList.querySelectorAll('.c-off-ot-slab-row').forEach(function (row, index) {
            row.querySelector('.ot-slab-number').textContent = index + 1;
        });
    }

    addButton?.addEventListener('click', function () {
        slabsList.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
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
