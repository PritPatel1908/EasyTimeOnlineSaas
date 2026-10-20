@forelse ($financialYears as $financialYear)
    <tr>
        <td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
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
                        $isStarted = (bool) $financialYear->is_started;
                        $isClosed = (bool) $financialYear->is_closed;
                        $isCurrentStartedYear = (int) $startedFinancialYearId === (int) $financialYear->id;
                        $canStart = ! $isStarted && ! $isClosed && $startedFinancialYearId === null;
                        $canStop = $isStarted && ! $isClosed && $isCurrentStartedYear;
                        $canClose = ! $isStarted && ! $isClosed && $startedFinancialYearId === null;
                        $canChangeLifecycle = $canStart || $canStop;
                    @endphp
                    <a href="javascript:void(0);"
                        class="me-2 financial-year-flag-action {{ $canChangeLifecycle ? '' : 'disabled text-muted pe-none' }}"
                        @if ($canChangeLifecycle) data-form="financial-year-started-{{ $financialYear->id }}" @endif
                        title="{{ $isStarted ? 'Stop financial year' : ($isClosed ? 'Financial year cannot be reopened' : 'Start financial year') }}"
                        aria-label="{{ $isStarted ? 'Stop financial year' : ($isClosed ? 'Financial year cannot be reopened' : 'Start financial year') }}"
                        @if (! $canChangeLifecycle) aria-disabled="true" tabindex="-1" @endif>
                            <i class="ti {{ $isStarted ? 'ti-player-stop' : ($isClosed ? 'ti-circle-check' : 'ti-player-play') }}"></i>
                    </a>
                    <a href="javascript:void(0);"
                        class="me-2 financial-year-flag-action {{ $canClose ? '' : 'disabled text-muted pe-none' }}"
                        @if ($canClose) data-form="financial-year-closed-{{ $financialYear->id }}" @endif
                        title="{{ $isClosed ? 'Financial year is permanently closed' : 'Close financial year permanently' }}"
                        aria-label="{{ $isClosed ? 'Financial year is permanently closed' : 'Close financial year permanently' }}"
                        @if (! $canClose) aria-disabled="true" tabindex="-1" @endif>
                        <i class="ti {{ $isClosed ? 'ti-lock' : 'ti-lock-open' }}"></i>
                    </a>
                    <form id="financial-year-started-{{ $financialYear->id }}" method="POST" action="{{ url('time-management/financial-years/'.$financialYear->id.'/flags') }}" class="d-none">
                        @csrf
                        <input type="hidden" name="is_started" value="{{ $isStarted ? 0 : 1 }}">
                        <input type="hidden" name="is_closed" value="{{ $isStarted ? 1 : 0 }}">
                    </form>
                    <form id="financial-year-closed-{{ $financialYear->id }}" method="POST" action="{{ url('time-management/financial-years/'.$financialYear->id.'/flags') }}" class="d-none">
                        @csrf
                        <input type="hidden" name="is_started" value="0">
                        <input type="hidden" name="is_closed" value="1">
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="9" class="py-4 text-muted text-center"><i class="ti ti-calendar-off me-1"></i>No financial years found.</td></tr>
@endforelse
