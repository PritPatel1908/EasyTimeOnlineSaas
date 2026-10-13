<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveTypeImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessLeaveTypeImport implements ShouldQueue
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
            $user->notify(new LeaveTypeImportExportCompleted('import', 'The uploaded leave type import file could not be found.'));
            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === []) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new LeaveTypeImportExportCompleted('import', 'The leave type import file is empty or could not be read.'));
            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if (collect(array_values($row))->every(static fn($value): bool => trim((string) $value) === '')) {
                continue;
            }

            $validationErrors = $this->validateImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);
                continue;
            }

            $existing = LeaveType::query()->where('code', trim((string) $row['code']))->first();
            if ($existing !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate leave type detected for code ' . $row['code'] . '. Enable "Update Duplicate Records" to update existing records.';
                continue;
            }

            $payload = $this->buildPayload($row);
            if ($existing !== null) {
                $existing->update($payload);
                $updated++;
            } else {
                LeaveType::query()->create($payload);
                $created++;
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new leave type record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new LeaveTypeImportExportCompleted('import', $message));
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

    private function validateImportRow(array $row): array
    {
        $errors = [];
        if (trim((string) ($row['code'] ?? '')) === '') {
            $errors[] = 'code is required';
        }
        if (strlen((string) ($row['code'] ?? '')) > 255) {
            $errors[] = 'code must not exceed 255 characters';
        }
        if ($this->normalizeStatus((string) ($row['status'] ?? '1')) === null) {
            $errors[] = 'status must be Active, Inactive, 1 or 0';
        }
        if (($row['location'] ?? '') !== '' && $this->resolveLocationValue((string) $row['location']) === null) {
            $errors[] = 'location must match an existing location name or code';
        }

        return $errors;
    }

    private function buildPayload(array $row): array
    {
        return [
            'code' => trim((string) $row['code']),
            'description' => ($row['description'] ?? '') !== '' ? trim((string) $row['description']) : null,
            'location_id' => ($row['location'] ?? '') !== '' ? $this->resolveLocationValue((string) $row['location'])?->id : null,
            'status' => $this->normalizeStatus((string) ($row['status'] ?? '1')),
        ];
    }

    private function resolveLocationValue(string $locationValue): ?Location
    {
        $locationValue = trim($locationValue);
        if ($locationValue === '') {
            return null;
        }

        return Location::query()->where('name', $locationValue)->first()
            ?? Location::query()->where('code', $locationValue)->first()
            ?? (ctype_digit($locationValue) ? Location::query()->find((int) $locationValue) : null);
    }

    private function normalizeStatus(string $status): ?int
    {
        return match (strtolower(trim($status))) {
            'active', '1', 'true', 'yes' => 1,
            'inactive', '0', 'false', 'no' => 0,
            default => null,
        };
    }
}
