@forelse ($financialYears as $financialYear)
    <tr>
        <td>{{ $financialYear->code }}</td>
        <td>{{ $financialYear->name ?: '—' }}</td>
        <td>{{ $financialYear->start_date?->format('d M Y') }}</td>
        <td>{{ $financialYear->end_date?->format('d M Y') }}</td>
        <td>{{ $financialYear->location_records->pluck('name')->join(', ') ?: '—' }}</td>
        <td>{{ $financialYear->company_records->pluck('name')->join(', ') ?: '—' }}</td>
        <td><span class="badge {{ (int) $financialYear->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ (int) $financialYear->status === 1 ? 'Active' : 'Inactive' }}</span></td>
        <td>
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('FinancialYear', 'read'))
                    <a href="{{ url('time-management/financial-years/'.$financialYear->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('FinancialYear', 'write'))
                    <a href="{{ url('time-management/financial-years/'.$financialYear->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('FinancialYear', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-financial-year-btn" data-url="{{ url('time-management/financial-years/'.$financialYear->id) }}" data-name="{{ $financialYear->name ?: $financialYear->code }}" data-bs-toggle="modal" data-bs-target="#delete_financial_year_modal" title="Delete"><i class="ti ti-trash"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('FinancialYear', 'write'))
                    @php
                        $canToggleStarted = $startedFinancialYearId === null
                            || (int) $startedFinancialYearId === (int) $financialYear->id;
                    @endphp
                    <a href="javascript:void(0);"
                        class="me-2 financial-year-flag-action {{ $canToggleStarted ? '' : 'disabled text-muted pe-none' }}"
                        @if ($canToggleStarted) data-form="financial-year-started-{{ $financialYear->id }}" @endif
                        title="{{ $financialYear->is_started ? 'Mark as not started' : 'Mark as started' }}"
                        aria-label="{{ $financialYear->is_started ? 'Mark as not started' : 'Mark as started' }}"
                        @if (! $canToggleStarted) aria-disabled="true" tabindex="-1" @endif>
                            <i class="ti {{ $financialYear->is_started ? 'ti-circle-check' : 'ti-player-play' }}"></i>
                    </a>
                    <a href="javascript:void(0);"
                        class="me-2 financial-year-flag-action"
                        data-form="financial-year-closed-{{ $financialYear->id }}"
                        title="{{ $financialYear->is_closed ? 'Reopen financial year' : 'Close financial year' }}"
                        aria-label="{{ $financialYear->is_closed ? 'Reopen financial year' : 'Close financial year' }}">
                        <i class="ti {{ $financialYear->is_closed ? 'ti-lock' : 'ti-lock-open' }}"></i>
                    </a>
                    <form id="financial-year-started-{{ $financialYear->id }}" method="POST" action="{{ url('time-management/financial-years/'.$financialYear->id.'/flags') }}" class="d-none">
                        @csrf
                        <input type="hidden" name="is_started" value="{{ $financialYear->is_started ? 0 : 1 }}">
                        <input type="hidden" name="is_closed" value="{{ (int) $financialYear->is_closed }}">
                    </form>
                    <form id="financial-year-closed-{{ $financialYear->id }}" method="POST" action="{{ url('time-management/financial-years/'.$financialYear->id.'/flags') }}" class="d-none">
                        @csrf
                        <input type="hidden" name="is_started" value="{{ (int) $financialYear->is_started }}">
                        <input type="hidden" name="is_closed" value="{{ $financialYear->is_closed ? 0 : 1 }}">
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="py-4 text-muted text-center"><i class="ti ti-calendar-off me-1"></i>No financial years found.</td></tr>
@endforelse
