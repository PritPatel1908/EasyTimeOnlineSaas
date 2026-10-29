<div class="modal fade" id="delete_attendance_log_modal_{{ $attendanceLog->id }}" tabindex="-1" aria-labelledby="delete_attendance_log_title_{{ $attendanceLog->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_attendance_log_title_{{ $attendanceLog->id }}">Delete Punch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/delete') }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to delete punch #{{ $attendanceLog->id }}?</p>
                    <p class="mb-0 text-muted">
                        {{ $attendanceLog->user?->name ?? 'Unknown employee' }}
                        {{ $attendanceLog->datetime?->format('Y-m-d H:i:s') ? ' - '.$attendanceLog->datetime->format('Y-m-d H:i:s') : '' }}
                    </p>
                    <div class="alert alert-warning mt-3 mb-0" role="alert">
                        This punch will no longer be used for attendance calculation. You can restore it later from Deleted Punches.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Delete Punch</button>
                </div>
            </form>
        </div>
    </div>
</div>
