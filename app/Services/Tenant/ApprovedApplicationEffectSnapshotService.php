<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\ApprovedApplicationEffectSnapshot;
use App\Models\Tenant\Coff;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ApprovedApplicationEffectSnapshotService
{
    public function capture(Model $application): void
    {
        if (ApprovedApplicationEffectSnapshot::query()
            ->where('approvable_type', $application::class)
            ->where('approvable_id', $application->getKey())
            ->exists()) {
            return;
        }

        [$userIds, $startDate, $endDate] = $this->scope($application);
        $tables = [];

        if ($userIds !== []) {
            $tableFamilies = $this->tableFamilies($application);
            foreach (range($startDate->year, $endDate->year) as $year) {
                foreach ($tableFamilies as $family) {
                    $table = $family.$year;
                    $this->captureDateScopedTable($tables, $table, $userIds, $startDate, $endDate);
                }
            }

            foreach ($this->scheduleTables($application) as $table) {
                $this->captureDateScopedTable($tables, $table, $userIds, $startDate, $endDate);
            }
        }

        $logSnapshots = $this->captureApplicationLogs($application);

        $userValues = [];
        if ($application instanceof ShiftChangeApplication || $application instanceof ShortLeaveApplication) {
            $columns = $application instanceof ShiftChangeApplication
                ? ['shift_type', 'shift_rotation_id', 'shift_status']
                : ['short_leave_minutes'];
            foreach ($userIds as $userId) {
                $userValues[$userId] = DB::table('users')
                    ->where('id', $userId)
                    ->first(array_merge(['id'], array_filter(
                        $columns,
                        static fn (string $column): bool => Schema::hasColumn('users', $column),
                    )));
                $userValues[$userId] = $userValues[$userId] === null
                    ? null
                    : (array) $userValues[$userId];
            }
        }

        $userShifts = $application instanceof ShiftChangeApplication && Schema::hasTable('user_shifts')
            ? DB::table('user_shifts')->whereIn('user_id', $userIds)->get()->map(static fn ($row): array => (array) $row)->all()
            : null;
        $applicationStatus = $application instanceof CoffApplication
            ? $application->getAttribute('status')
            : null;

        ApprovedApplicationEffectSnapshot::query()->create([
            'approvable_type' => $application::class,
            'approvable_id' => $application->getKey(),
            'snapshot' => [
                'captured_at' => now()->toDateTimeString(),
                'user_ids' => $userIds,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'tables' => $tables,
                'log_snapshots' => $logSnapshots,
                'user_values' => $userValues,
                'user_shifts' => $userShifts,
                'application_status' => $applicationStatus,
            ],
        ]);
    }

    public function restore(Model $application, ApprovedApplicationEffectSnapshot $snapshot): array
    {
        $data = $snapshot->snapshot;
        if ($data === null) {
            throw ValidationException::withMessages([
                'decision' => 'This approved request has no effect snapshot and cannot be safely cancelled.',
            ]);
        }

        foreach ($data['tables'] as $tableSnapshot) {
            $table = $tableSnapshot['table'];
            if (! Schema::hasTable($table)) {
                throw ValidationException::withMessages([
                    'decision' => "The saved effect table {$table} is unavailable, so this request cannot be cancelled safely.",
                ]);
            }

            $query = $this->dateScopedQuery(
                $table,
                $tableSnapshot['user_ids'],
                $tableSnapshot['start_date'],
                $tableSnapshot['end_date'],
            );
            if (Schema::hasColumn($table, 'is_locked') && (clone $query)->where('is_locked', true)->exists()) {
                throw ValidationException::withMessages([
                    'decision' => 'This request affects locked records and cannot be cancelled safely.',
                ]);
            }

            $query->delete();
            $this->insertSnapshotRows($table, $tableSnapshot['rows']);
        }

        foreach ($data['log_snapshots'] ?? [] as $logSnapshot) {
            $query = DB::table('attendance_logs')
                ->where('referance_type', $logSnapshot['referance_type'])
                ->where('referance_id', $logSnapshot['referance_id']);
            if ($query->where('is_locked', true)->exists()) {
                throw ValidationException::withMessages([
                    'decision' => 'This request affects locked attendance logs and cannot be cancelled safely.',
                ]);
            }

            DB::table('attendance_logs')
                ->where('referance_type', $logSnapshot['referance_type'])
                ->where('referance_id', $logSnapshot['referance_id'])
                ->delete();
            $this->insertSnapshotRows('attendance_logs', $logSnapshot['rows']);
        }

        foreach ($data['user_values'] ?? [] as $userId => $values) {
            if ($values === null) {
                continue;
            }

            unset($values['id']);
            DB::table('users')->where('id', $userId)->update($values);
        }

        if (is_array($data['user_shifts'] ?? null) && Schema::hasTable('user_shifts')) {
            DB::table('user_shifts')->whereIn('user_id', $data['user_ids'])->delete();
            $this->insertSnapshotRows('user_shifts', $data['user_shifts']);
        } elseif (is_array($data['user_shifts'] ?? null)) {
            throw ValidationException::withMessages([
                'decision' => 'The saved user shift table is unavailable, so this request cannot be cancelled safely.',
            ]);
        }

        if ($application instanceof CoffApplication && $data['application_status'] !== null) {
            $application->setAttribute('status', $data['application_status']);
            $application->save();
        }

        return [
            'userIds' => array_map('intval', $data['user_ids']),
            'startDate' => Carbon::parse($data['start_date']),
            'endDate' => Carbon::parse($data['end_date']),
        ];
    }

    private function captureDateScopedTable(
        array &$tables,
        string $table,
        array $userIds,
        Carbon $startDate,
        Carbon $endDate,
    ): void {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id') || ! Schema::hasColumn($table, 'date')) {
            return;
        }

        $tables[] = [
            'table' => $table,
            'user_ids' => $userIds,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'rows' => DB::table($table)
                ->whereIn('user_id', $userIds)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->map(static fn ($row): array => (array) $row)
                ->all(),
        ];
    }

    private function captureApplicationLogs(Model $application): array
    {
        $references = match (true) {
            $application instanceof ManualPunchApplication => [[
                $application->manual_punch_id === null ? ManualPunchApplication::class : ManualPunch::class,
                (int) ($application->manual_punch_id ?? $application->id),
            ]],
            $application instanceof ManualAttendanceApplication => [
                ['manual_attendance_application_start', (int) $application->id],
                ['manual_attendance_application_end', (int) $application->id],
            ],
            default => [],
        };

        $logSnapshots = [];
        if (Schema::hasTable('attendance_logs')) {
            foreach ($references as [$referenceType, $referenceId]) {
                $logSnapshots[] = [
                    'referance_type' => $referenceType,
                    'referance_id' => $referenceId,
                    'rows' => DB::table('attendance_logs')
                        ->where('referance_type', $referenceType)
                        ->where('referance_id', $referenceId)
                        ->get()
                        ->map(static fn ($row): array => (array) $row)
                        ->all(),
                ];
            }
        }

        return $logSnapshots;
    }

    private function dateScopedQuery(string $table, array $userIds, string $startDate, string $endDate)
    {
        return DB::table($table)
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertSnapshotRows(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        if (! preg_match('/\A[a-zA-Z_][a-zA-Z0-9_]*\z/', $table)) {
            throw ValidationException::withMessages([
                'decision' => 'The saved effect snapshot contains an invalid table name.',
            ]);
        }

        // Effect-row IDs are surrogate keys and are not referenced by related records.
        $rows = array_map(static function (array $row): array {
            unset($row['id']);

            return $row;
        }, $rows);

        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $maxBindings = match ($driver) {
            'sqlsrv' => 2000,
            'sqlite' => 900,
            default => 60000,
        };
        $chunkSize = min(200, max(1, intdiv($maxBindings, count($rows[0]))));

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $connection->table($table)->insert($chunk);
        }
    }

    /**
     * @return array{0: array<int, int>, 1: Carbon, 2: Carbon}
     */
    private function scope(Model $application): array
    {
        $relation = method_exists($application, 'Users')
            ? $application->Users()
            : (method_exists($application, 'users')
                ? $application->users()
                : (method_exists($application, 'user') ? $application->user() : null));
        $employees = $relation?->withoutGlobalScopes()->get();
        $userIds = $employees instanceof \Illuminate\Database\Eloquent\Collection
            ? $employees->modelKeys()
            : ($employees === null ? [] : [(int) $employees->getKey()]);

        [$start, $end] = match (true) {
            $application instanceof LeaveApplication => [$application->from_date, $application->to_date],
            $application instanceof Coff => [$application->from_date, $application->to_date],
            $application instanceof CoffApplication => [$application->coff_from_date, $application->coff_to_date],
            $application instanceof ShiftChangeApplication, $application instanceof WeekOffChangeApplication => [
                $application->from_date,
                $application->is_forever ? $this->financialYearEnd() : $application->to_date,
            ],
            $application instanceof WeekOffSwapApplication => [
                min($application->week_date->toDateString(), $application->swap_date->toDateString()),
                max($application->week_date->toDateString(), $application->swap_date->toDateString()),
            ],
            $application instanceof ManualAttendanceApplication => [$application->in_time, $application->out_time],
            $application instanceof ManualPunchApplication => [$application->punch_date, $application->punch_date],
            $application instanceof OvertimeApplication => [$application->date, $application->date],
            $application instanceof OutDuty => [$application->from_date_time, $application->to_date_time],
            $application instanceof ShortLeaveApplication => [$application->date, $application->date],
            default => [now(), now()],
        };

        return [
            array_values(array_unique(array_map('intval', $userIds))),
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->startOfDay(),
        ];
    }

    private function financialYearEnd(): Carbon
    {
        $endDate = FinancialYear::query()
            ->where('is_started', true)
            ->orderByDesc('id')
            ->value('end_date');

        return Carbon::parse($endDate ?? Carbon::today()->endOfYear())->startOfDay();
    }

    /**
     * @return array<int, string>
     */
    private function tableFamilies(Model $application): array
    {
        if (
            $application instanceof LeaveApplication
            || $application instanceof Coff
            || $application instanceof CoffApplication
            || $application instanceof ManualAttendanceApplication
            || $application instanceof ManualPunchApplication
            || $application instanceof OvertimeApplication
            || $application instanceof OutDuty
            || $application instanceof ShortLeaveApplication
        ) {
            return $application instanceof LeaveApplication
                || $application instanceof ManualAttendanceApplication
                || $application instanceof ManualPunchApplication
                || $application instanceof ShortLeaveApplication
                    ? ['attendances', 'in_out_musters']
                    : ['attendances'];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function scheduleTables(Model $application): array
    {
        return match (true) {
            $application instanceof ShiftChangeApplication => ['shift_musters'],
            $application instanceof WeekOffChangeApplication,
            $application instanceof WeekOffSwapApplication => ['week_off_musters'],
            $application instanceof LeaveApplication,
            $application instanceof ManualAttendanceApplication,
            $application instanceof ManualPunchApplication,
            $application instanceof ShortLeaveApplication => ['status_musters'],
            default => [],
        };
    }
}
