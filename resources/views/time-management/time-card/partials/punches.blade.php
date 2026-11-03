@if ($punches->isEmpty())
    <p class="text-muted mb-0">No punches found between the first In and last Out for this attendance.</p>
@else
    @if ($attendanceRemarks)
        <div class="alert alert-light border py-2 mb-3">
            <strong>Attendance Remarks:</strong> {{ $attendanceRemarks }}
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Punch Date &amp; Time</th>
                    <th>Direction</th>
                    <th>Source / Reference</th>
                    <th>Device / Area</th>
                    <th>Punch / Verify Type</th>
                    <th>Remarks</th>
                    <th>Processing</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($punches as $punch)
                    @php
                        $referenceSource = $referenceSources[$punch->referance_type] ?? null;
                        $machine = $machines[$punch->dms_device_id] ?? null;
                        $area = $areas[$punch->dms_area_id] ?? null;
                        $referenceKey = $punch->referance_type.':'.$punch->referance_id;
                        $punchRemarks = $referenceReasons[$referenceKey] ?? null;
                    @endphp
                    <tr>
                        <td>#{{ $punch->id }}</td>
                        <td>{{ $punch->datetime?->format('Y-m-d H:i:s') ?? '—' }}</td>
                        <td>{{ $punch->is_out ? 'Out' : 'In' }}</td>
                        <td>
                            {{ $punch->is_manual ? ($referenceSource['label'] ?? 'Manual') : 'Device' }}
                            @if ($punch->referance_type)
                                <small class="d-block text-muted">
                                    {{ class_exists($punch->referance_type) ? class_basename($punch->referance_type) : str($punch->referance_type)->replace('_', ' ')->title() }}
                                    @if ($punch->referance_id) #{{ $punch->referance_id }} @endif
                                </small>
                            @endif
                        </td>
                        <td>
                            @if ($machine)
                                {{ $machine->name }}
                                @if ($machine->machine_serial_number)
                                    <small class="d-block text-muted">SN: {{ $machine->machine_serial_number }}</small>
                                @endif
                            @elseif ($punch->dms_device_id)
                                Device #{{ $punch->dms_device_id }}
                            @else
                                —
                            @endif
                            @if ($area || $punch->dms_area_id || $punch->area_name)
                                <small class="d-block text-muted">
                                    Area: {{ $area->name ?? $punch->area_name ?? 'Area #'.$punch->dms_area_id }}
                                </small>
                            @endif
                        </td>
                        <td>
                            {{ $punch->punch_type ?? '—' }}
                            <small class="d-block text-muted">Verify: {{ $punch->verify_type ?? '—' }}</small>
                        </td>
                        <td>{{ $punchRemarks ?: '—' }}</td>
                        <td>
                            @if ($punch->is_ignored)
                                <span class="badge bg-warning">Ignored</span>
                            @elseif ($punch->has_error)
                                <span class="badge bg-danger">Error</span>
                            @elseif ($punch->is_staged)
                                <span class="badge bg-warning">Staged</span>
                            @elseif ($punch->is_calculated)
                                <span class="badge bg-success">Processed</span>
                            @else
                                <span class="badge bg-light text-dark">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
