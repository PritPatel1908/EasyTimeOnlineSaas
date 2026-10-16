<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualLeaveBalance;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualLeaveBalanceImportExportCompleted;
use App\Support\CsvImportTemplate;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessManualLeaveBalanceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        $user = $this->userId !== null ? User::query()->find($this->userId) : null;
        if ($user === null) {
            return;
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        if (! Storage::disk('local')->exists($this->filePath)) {
            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === []) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ManualLeaveBalanceImportExportCompleted('import', 'The import file did not contain any data rows.'));
            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            [$payload, $userIds, $validationErrors] = $this->resolveRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);
                continue;
            }

            $existing = $this->findDuplicate($payload, $userIds);
            if ($existing !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': A balance already exists for these employees, leave type and date. Enable "Update Duplicate Records" to update it.';
                continue;
            }

            DB::transaction(function () use ($existing, $payload, $userIds, &$created, &$updated): void {
                $attributes = $payload + ['updated_by' => $this->userId];
                if ($existing !== null) {
                    $existing->update($attributes);
                    $existing->Users()->sync($userIds);
                    $updated++;
                    return;
                }

                $balance = ManualLeaveBalance::query()->create($attributes + ['created_by' => $this->userId]);
                $balance->Users()->sync($userIds);
                $created++;
            });
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new manual leave balance record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with ' . count($errors) . ' error(s). ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new ManualLeaveBalanceImportExportCompleted('import', $message));
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            return [];
        }

        $headers = array_map(static fn($header): string => CsvImportTemplate::normalizeHeader((string) $header), $headers);
        $rows = [];
        $lineNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if (CsvImportTemplate::isHintRow($row)) {
                continue;
            }

            $normalized = [];
            foreach ($headers as $index => $header) {
                $normalized[$header] = trim((string) ($row[$index] ?? ''));
            }
            $rows[$lineNumber] = $normalized;
        }
        fclose($handle);

        return $rows;
    }

    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn($value): bool => trim((string) $value) === '');
    }

    private function resolveRow(array $row): array
    {
        $errors = [];
        $employeeCodes = $this->splitCodes($row['employee_codes'] ?? '');
        $locationsInput = $this->splitCodes($row['location'] ?? $row['location_codes'] ?? '');
        $companiesInput = $this->splitCodes($row['company'] ?? $row['company_codes'] ?? '');

        if ($employeeCodes === []) {
            $errors[] = 'employee_codes is required';
        }
        $users = $this->resolveCodes(User::class, $employeeCodes, 'employee_codes', $errors);
        $locations = $this->resolveByNameOrCode(Location::class, $locationsInput, 'location', $errors);
        $companies = $this->resolveByNameOrCode(Company::class, $companiesInput, 'company', $errors);

        $leaveTypeCode = trim((string) ($row['leave_type_code'] ?? ''));
        $leaveType = $leaveTypeCode !== '' ? LeaveType::query()->where('code', $leaveTypeCode)->first() : null;
        if ($leaveType === null) {
            $errors[] = 'leave_type_code must match an existing leave type';
        }

        $balance = trim((string) ($row['balance'] ?? ''));
        if ($balance === '' || ! is_numeric($balance) || (float) $balance < -99999999.99 || (float) $balance > 99999999.99) {
            $errors[] = 'balance must be a valid decimal';
        }

        $date = trim((string) ($row['date'] ?? ''));
        $parsedDate = $this->parseDateTime($date);
        if ($parsedDate === null) {
            $errors[] = 'date must use YYYY-MM-DD HH:MM format';
        }

        return [[
            'location_id' => array_values(array_map(static fn(Location $location): int => (int) $location->getKey(), $locations)),
            'company_id' => array_values(array_map(static fn(Company $company): int => (int) $company->getKey(), $companies)),
            'leave_type_id' => $leaveType?->getKey(),
            'balance' => $balance !== '' && is_numeric($balance) ? (float) $balance : 0,
            'date' => $parsedDate?->format('Y-m-d H:i:s'),
            'reason' => ($row['reason'] ?? '') !== '' ? trim((string) $row['reason']) : null,
        ], array_values(array_map(static fn(User $employee): int => (int) $employee->getKey(), $users)), $errors];
    }

    private function parseDateTime(string $value): ?Carbon
    {
        foreach (['!Y-m-d H:i:s', '!Y-m-d H:i', '!Y-m-d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
            } catch (InvalidArgumentException) {
                continue;
            }

            if ($parsed !== false && $parsed->format(substr($format, 1)) === $value) {
                return $parsed;
            }
        }

        return null;
    }

    private function resolveCodes(string $model, array $codes, string $field, array &$errors): array
    {
        if ($codes === []) {
            return [];
        }

        $records = $model::query()->whereIn('code', $codes)->get()->keyBy('code');
        $missing = array_values(array_diff($codes, $records->keys()->all()));
        if ($missing !== []) {
            $errors[] = $field . ' contains unknown code(s): ' . implode(', ', $missing);
        }

        return $records->values()->all();
    }

    private function resolveByNameOrCode(string $model, array $values, string $field, array &$errors): array
    {
        $records = [];

        foreach ($values as $value) {
            if (preg_match('/\A(.+) \(([^()]*)\)\z/', $value, $matches) === 1) {
                $value = trim($matches[2]);
            }

            $matches = $model::query()
                ->where('code', $value)
                ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                ->get();

            if ($matches->count() !== 1) {
                $reason = $matches->isEmpty() ? 'unknown' : 'ambiguous';
                $errors[] = $field . ' contains ' . $reason . ' name or code: ' . $value;
                continue;
            }

            $record = $matches->first();
            $records[$record->getKey()] = $record;
        }

        return array_values($records);
    }

    private function splitCodes(string $codes): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[;|]/', $codes) ?: []),
            static fn(string $code): bool => $code !== '',
        )));
    }

    private function findDuplicate(array $payload, array $userIds): ?ManualLeaveBalance
    {
        $userIds = $this->sortIds($userIds);
        $candidateBalances = ManualLeaveBalance::query()
            ->where('leave_type_id', $payload['leave_type_id'])
            ->whereDate('date', $payload['date'])
            ->with('Users')
            ->get();

        foreach ($candidateBalances as $candidate) {
            if (
                $this->sortIds($candidate->Users->modelKeys()) === $userIds
                && $this->sortIds((array) $candidate->location_id) === $this->sortIds($payload['location_id'])
                && $this->sortIds((array) $candidate->company_id) === $this->sortIds($payload['company_id'])
            ) {
                return $candidate;
            }
        }

        return null;
    }

    private function sortIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);

        return $ids;
    }
}
