@extends('layout.mainlayout')

@section('content')
@php
    $attendanceRules = [
        'Need Approval For Overtime' => $category->need_approval_for_overtime !== null ? ($category->need_approval_for_overtime ? 'Yes' : 'No') : '-',
        'Regular OT On Week Off' => $category->regular_ot_on_wo !== null ? ($category->regular_ot_on_wo ? 'Yes' : 'No') : '-',
        'Bypass Timing Rule' => $category->bypass_timing_rule !== null ? ($category->bypass_timing_rule ? 'Yes' : 'No') : '-',
        'Ignore Before/After Shift Punch' => $category->ignore_before_after_shift_punch !== null ? ($category->ignore_before_after_shift_punch ? 'Yes' : 'No') : '-',
        'Fix Work Hours' => $category->fix_work_hours !== null ? ($category->fix_work_hours ? 'Yes' : 'No') : '-',
        'Fix Work Hours As Per Shift' => $category->fix_work_hours_as_per_shift !== null ? ($category->fix_work_hours_as_per_shift ? 'Yes' : 'No') : '-',
        'Fix Work Hours Value' => $category->fix_work_hours_value ?: '-',
        'Ignore Break In Attendance' => $category->ignore_break_in_attendance !== null ? ($category->ignore_break_in_attendance ? 'Yes' : 'No') : '-',
        'Reset Halfday Rule Cycle' => $category->reset_halfday_rule_cycle !== null ? ($category->reset_halfday_rule_cycle ? 'Yes' : 'No') : '-',
        'Double OT On Public Holiday' => $category->give_double_ot_in_public_holiday !== null ? ($category->give_double_ot_in_public_holiday ? 'Yes' : 'No') : '-',
        'Double C-Off On Public Holiday' => $category->give_double_coff_in_public_holiday !== null ? ($category->give_double_coff_in_public_holiday ? 'Yes' : 'No') : '-',
        'Week Off Paid' => $category->is_week_off_paid !== null ? ($category->is_week_off_paid ? 'Yes' : 'No') : '-',
        'Holiday Paid' => $category->is_holiday_paid !== null ? ($category->is_holiday_paid ? 'Yes' : 'No') : '-',
        'Single Punch Allowed Present' => $category->single_punch_allowed_present !== null ? ($category->single_punch_allowed_present ? 'Yes' : 'No') : '-',
        'Single Punch Allowed Half Day' => $category->single_punch_allowed_half_day !== null ? ($category->single_punch_allowed_half_day ? 'Yes' : 'No') : '-',
    ];

    $leaveTypeIds = is_array($category->leave_type_id)
        ? $category->leave_type_id
        : (json_decode((string) ($category->leave_type_id ?? '[]'), true) ?: [$category->leave_type_id]);
    $leaveTypeIds = array_values(array_filter(array_map('intval', $leaveTypeIds), fn ($id) => $id > 0));
    $leaveTypeNames = $leaveTypeIds === []
        ? '-'
        : (\App\Models\Tenant\LeaveType::query()->whereIn('id', $leaveTypeIds)->pluck('description')->filter()->implode(', ') ?: '-');

    $companyNames = $category->companies->pluck('name')->filter()->implode(', ') ?: '-';
    $locationNames = $category->locations->pluck('name')->filter()->implode(', ') ?: '-';
    $createdByName = $category->created_by ? (\App\Models\Tenant\User::find($category->created_by)?->name ?? '-') : '-';
    $updatedByName = $category->updated_by ? (\App\Models\Tenant\User::find($category->updated_by)?->name ?? '-') : '-';

    $formatTimeValue = function ($value) {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_numeric($value)) {
            $totalMinutes = (int) round((float) $value * 60);
            return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $woHlSlabs = $category->c_off_against_wo_hl_slabs ?? collect();
    $otSlabs = $category->c_off_against_ot_slabs ?? collect();
@endphp

<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">View Category</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Employee Structure</li>
                        <li class="breadcrumb-item"><a href="{{ url('employee-structure/categories') }}">Categories</a></li>
                        <li class="breadcrumb-item active">{{ $category->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(\App\Support\TenantPermissions::userCan('Category', 'write'))
                    <a href="{{ url('employee-structure/categories/'.$category->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('employee-structure/categories') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Category Name</small>{{ $category->name }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Category Code</small>{{ $category->code }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Email</small>{{ $category->email ?: '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Canteen Break Limit</small>{{ $category->canteen_break_limit ?: '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Skip Overtime</small>{{ $category->skip_overtime ? \Carbon\Carbon::parse($category->skip_overtime)->format('H:i') : '-' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Organization</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small>{{ $companyNames }}</div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Locations</small>{{ $locationNames }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Attendance Rules</h5></div>
            <div class="card-body">
                <div class="row">
                    @foreach($attendanceRules as $label => $value)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ $label }}</small>
                            {{ $value }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">C-Off Settings</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Eligible For C-Off</small>{{ $category->is_eligible_for_c_off !== null ? ($category->is_eligible_for_c_off ? 'Yes' : 'No') : '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">C-Off Lapse In Days</small>{{ $category->c_off_lapse_in_days ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Allow Halfday C-Off</small>{{ $category->allow_halfday_c_off !== null ? ($category->allow_halfday_c_off ? 'Yes' : 'No') : '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Allow Backdated Leave</small>{{ $category->allow_backdated_leave !== null ? ($category->allow_backdated_leave ? 'Yes' : 'No') : '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Backdated Day Limit</small>{{ $category->backdated_day_limit ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Advance Day Limit</small>{{ $category->advance_day_limit ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Maximum Accumulation</small>{{ $category->maximum_accumulation ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Maximum Request In A Month</small>{{ $category->maximum_request_in_a_month ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Maximum Request In A Year</small>{{ $category->maximum_request_in_a_year ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">C-Off Against WO/HL Slabs</h5></div>
            <div class="card-body">
                @if($woHlSlabs->isEmpty())
                    <div class="text-muted">-</div>
                @else
                    <div class="row">
                        @foreach($woHlSlabs as $index => $slab)
                            <div class="col-md-4 mb-3">
                                <small class="text-muted d-block">Slab {{ $index + 1 }}</small>
                                <div>From Time: {{ $formatTimeValue($slab->from_time ?? null) }}</div>
                                <div>To Time: {{ $formatTimeValue($slab->to_time ?? null) }}</div>
                                <div>Credit Days: {{ $slab->credit_days ?? '-' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">C-Off Against OT Slabs</h5></div>
            <div class="card-body">
                @if($otSlabs->isEmpty())
                    <div class="text-muted">-</div>
                @else
                    <div class="row">
                        @foreach($otSlabs as $index => $slab)
                            <div class="col-md-4 mb-3">
                                <small class="text-muted d-block">Slab {{ $index + 1 }}</small>
                                <div>From Hours: {{ $formatTimeValue($slab->from_hours ?? null) }}</div>
                                <div>To Hours: {{ $formatTimeValue($slab->to_hours ?? null) }}</div>
                                <div>Credit Days: {{ $slab->credit_days ?? '-' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Leave Rules</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Max Short Leave Minutes Per Month</small>{{ $category->max_short_leave_minutes_per_month ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Max Short Leave Minutes Per Application</small>{{ $category->max_short_leave_minutes_per_application ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Max Occurrence Of Short Leave In Month</small>{{ $category->max_occurance_of_short_leave_in_month ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Advance Short Leave Application</small>{{ $category->advance_short_leave_application ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Leave Type</small>{{ $leaveTypeNames }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Min Avail</small>{{ $category->min_avail ?? '-' }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Max Avail</small>{{ $category->max_avail ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Configuration</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Status</small><span class="badge {{ $category->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $category->status === 1 ? 'Active' : 'Inactive' }}</span></div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Created By</small>{{ $createdByName }}</div>
                    <div class="col-md-4 mb-3"><small class="text-muted d-block">Updated By</small>{{ $updatedByName }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
