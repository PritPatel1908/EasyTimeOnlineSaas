<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Jobs\Tenant\CreateOvertimeApplicationApprovals;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OvertimeApplicationWorkflow
{
    public function availableOvertimeMinutes(int $userId, string $date): ?int
    {
        $attendanceDate = Carbon::parse($date)->toDateString();
        $year = (int) Carbon::parse($attendanceDate)->format('Y');
        $table = 'attendances'.$year;
        if (! Schema::hasTable($table)) {
            return null;
        }

        $minutes = DB::table($table)
            ->where('user_id', $userId)
            ->whereDate('date', $attendanceDate)
            ->whereNotNull('total_overtime_minutes')
            ->where('total_overtime_minutes', '>', 0)
            ->where(function ($query): void {
                $query->whereNull('approved_overtime_minutes')
                    ->orWhere('approved_overtime_minutes', 0);
            })
            ->value('total_overtime_minutes');

        return $minutes === null ? null : (int) $minutes;
    }

    public function unavailableOvertimeMessage(int $userId, string $date): string
    {
        $attendanceDate = Carbon::parse($date)->toDateString();
        $table = 'attendances'.Carbon::parse($attendanceDate)->format('Y');
        if (! Schema::hasTable($table)) {
            return 'Attendance records are not available for the selected date.';
        }

        $attendance = DB::table($table)
            ->where('user_id', $userId)
            ->whereDate('date', $attendanceDate)
            ->first(['total_overtime_minutes', 'approved_overtime_minutes']);

        if ($attendance === null || (int) $attendance->total_overtime_minutes <= 0) {
            return 'No overtime was recorded for this employee on the selected date. Please choose a date with recorded overtime.';
        }

        return 'Overtime for this date has already been approved and cannot be submitted again.';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, int $actorId): OvertimeApplication
    {
        return DB::transaction(function () use ($payload, $actorId): OvertimeApplication {
            $flowId = $this->approvalFlowId((int) $payload['user_id']);
            $payload['overtime_minutes'] = $this->requiredOvertimeMinutes(
                (int) $payload['user_id'],
                (string) $payload['date'],
            );
            $application = OvertimeApplication::query()->create($payload + [
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            CreateOvertimeApplicationApprovals::dispatch(
                (int) $application->id,
                [$flowId],
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();

            return $application;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(OvertimeApplication $application, array $payload, int $actorId): void
    {
        DB::transaction(function () use ($application, $payload, $actorId): void {
            $flowId = $this->approvalFlowId((int) $payload['user_id']);
            $payload['overtime_minutes'] = $this->requiredOvertimeMinutes(
                (int) $payload['user_id'],
                (string) $payload['date'],
            );
            $application->update($payload + ['updated_by' => $actorId]);

            CreateOvertimeApplicationApprovals::dispatch(
                (int) $application->id,
                [$flowId],
                $actorId,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    private function approvalFlowId(int $userId): int
    {
        $user = User::query()
            ->with('overtime_approval_flow.approval_flow_details')
            ->find($userId);

        if ($user === null) {
            throw ValidationException::withMessages([
                'user_id' => 'The selected employee could not be found.',
            ]);
        }

        if ($user->overtime_approval_flow === null) {
            throw ValidationException::withMessages([
                'user_id' => 'An overtime approval flow must be assigned to the selected employee.',
            ]);
        }

        if ($user->overtime_approval_flow->approval_flow_details->isEmpty()) {
            throw ValidationException::withMessages([
                'user_id' => 'The assigned overtime approval flow has no approval levels.',
            ]);
        }

        return (int) $user->overtime_approval_flow_id;
    }

    private function requiredOvertimeMinutes(int $userId, string $date): int
    {
        $minutes = $this->availableOvertimeMinutes($userId, $date);
        if ($minutes === null) {
            throw ValidationException::withMessages([
                'date' => $this->unavailableOvertimeMessage($userId, $date),
            ]);
        }

        return $minutes;
    }
}
