@php
    $slabs = old('c_off_against_wo_hl_slabs', $category?->c_off_against_wo_hl_slabs?->toArray() ?? []);
@endphp
<div class="col-12 mt-3 c-off-slabs-wrapper">
    <div class="border rounded p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">C-Off Against WO/HL Slabs</h6>
            <button type="button" class="btn btn-sm btn-primary" id="add-c-off-slab" onclick="const list=document.getElementById('c-off-slabs-list');const template=document.getElementById('c-off-slab-template');const index=list.querySelectorAll('.c-off-slab-row').length;list.insertAdjacentHTML('beforeend',template.innerHTML.replaceAll('__INDEX__',index));list.querySelectorAll('.slab-number').forEach((number,rowIndex)=>number.textContent=rowIndex+1)"><i class="ti ti-plus me-1"></i>Add Slab</button>
        </div>
        <div id="c-off-slabs-list">
            @foreach($slabs as $index => $slab)
                <div class="c-off-slab-row border rounded p-3 mb-3" data-slab-index="{{ $index }}">
                    <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Slab <span class="slab-number">{{ $index + 1 }}</span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-c-off-slab" onclick="this.closest('.c-off-slab-row').remove();const list=document.getElementById('c-off-slabs-list');list.querySelectorAll('.slab-number').forEach((number,rowIndex)=>number.textContent=rowIndex+1)"><i class="ti ti-trash me-1"></i>Remove</button></div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">From Time</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_wo_hl_slabs[{{ $index }}][from_time]" class="form-control" value="{{ !empty($slab['from_time']) ? \Carbon\Carbon::parse($slab['from_time'])->format('H:i') : '' }}" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">To Time</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_wo_hl_slabs[{{ $index }}][to_time]" class="form-control" value="{{ !empty($slab['to_time']) ? \Carbon\Carbon::parse($slab['to_time'])->format('H:i') : '' }}" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Credit Days</label><input type="number" step="0.01" min="0" name="c_off_against_wo_hl_slabs[{{ $index }}][credit_days]" class="form-control" value="{{ $slab['credit_days'] ?? '' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <template id="c-off-slab-template">
            <div class="c-off-slab-row border rounded p-3 mb-3" data-slab-index="__INDEX__">
                <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Slab <span class="slab-number"></span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-c-off-slab" onclick="this.closest('.c-off-slab-row').remove();const list=document.getElementById('c-off-slabs-list');list.querySelectorAll('.slab-number').forEach((number,rowIndex)=>number.textContent=rowIndex+1)"><i class="ti ti-trash me-1"></i>Remove</button></div>
                <div class="row"><div class="col-md-4 mb-3"><label class="form-label">From Time</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_wo_hl_slabs[__INDEX__][from_time]" class="form-control" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div><div class="col-md-4 mb-3"><label class="form-label">To Time</label><input type="text" inputmode="numeric" maxlength="5" name="c_off_against_wo_hl_slabs[__INDEX__][to_time]" class="form-control" placeholder="HH:MM" oninput="this.value=this.value.replace(/\D/g,'').slice(0,4).replace(/^(\d{2})(?=\d)/,'$1:');if(this.value.length===2&&event.inputType!=='deleteContentBackward')this.value+=':'"></div><div class="col-md-4 mb-3"><label class="form-label">Credit Days</label><input type="number" step="0.01" min="0" name="c_off_against_wo_hl_slabs[__INDEX__][credit_days]" class="form-control"></div></div>
            </div>
        </template>
    </div>
</div>
@push('scripts')
<script>
function initializeCoffSlabs() {
    const addButton = document.getElementById('add-c-off-slab');
    const slabsList = document.getElementById('c-off-slabs-list');
    const template = document.getElementById('c-off-slab-template');
    const slabsWrapper = document.querySelector('.c-off-slabs-wrapper')?.parentElement;
    const cOffRow = document.querySelector('.c-off-settings-card .card-body .row');
    let nextIndex = slabsList?.querySelectorAll('.c-off-slab-row').length || 0;

    if (slabsWrapper && cOffRow) cOffRow.appendChild(slabsWrapper);

    function refreshRows() {
        const rows = slabsList.querySelectorAll('.c-off-slab-row');
        rows.forEach(function (row, index) {
            row.querySelector('.slab-number').textContent = index + 1;
        });
    }

    addButton?.addEventListener('click', function () {
        slabsList.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        refreshRows();
    });

    slabsList?.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-c-off-slab');
        if (button) {
            event.target.closest('.c-off-slab-row').remove();
            refreshRows();
        }
    });
    refreshRows();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCoffSlabs);
} else {
    initializeCoffSlabs();
}
</script>
@endpush
