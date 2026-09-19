@php
    $createdByName = $record->created_by
        ? (\App\Models\Tenant\User::find($record->created_by)?->name ?? '-')
        : '-';
    $updatedByName = $record->updated_by
        ? (\App\Models\Tenant\User::find($record->updated_by)?->name ?? '-')
        : '-';
@endphp

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Audit Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Created By</small>{{ $createdByName }}</div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Updated By</small>{{ $updatedByName }}</div>
        </div>
    </div>
</div>
