<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\Shift;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ShiftImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProcessShiftImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $filePath,
        public bool $updateDuplicateRecords = false,
    ) {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        if ($this->userId === null) {
            throw new RuntimeException('A user is required to process the shift import.');
        }

        $user = User::query()->find($this->userId);

        if ($user === null) {
            throw new RuntimeException('The user requesting the shift import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ShiftImportExportCompleted('import', 'The uploaded shift import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ShiftImportExportCompleted('import', 'The shift import file is empty or could not be read.'));

            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            $validationErrors = $this->validateImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);
                continue;
            }

            $existingShift = $this->findExistingShift($row);
            if ($existingShift !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate shift detected for code ' . $row['code'] . '. Enable "Update Duplicate Records" to update existing records.';
                continue;
            }

            if ($this->conflictingUniqueRecords($row, $existingShift)) {
                $errors[] = 'Row ' . $rowNumber . ': The shift code and name belong to different existing records.';
                continue;
            }

            $payload = $this->buildPayload($row);
            if ($existingShift !== null) {
                $existingShift->fill($payload)->save();
                $updated++;
                continue;
            }

            Shift::query()->create($payload);
            $created++;
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new shift record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new ShiftImportExportCompleted('import', $message));
    }

    /**
     * @return array<int, array<string, string>>|null
     */
    private function parseCsv(string $path): ?array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return null;
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false || $headerRow === []) {
            fclose($handle);

            return null;
        }

        $mappedHeaders = [];
        foreach ($headerRow as $index => $header) {
            $mappedHeaders[$index] = $this->normalizeHeader((string) $header);
        }

        $rows = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (CsvImportTemplate::isHintRow($row)) {
                continue;
            }

            $normalizedRow = [];
            foreach ($mappedHeaders as $index => $header) {
                $normalizedRow[$header] = trim((string) ($row[$index] ?? ''));
            }

            foreach ([
                'in_time',
                'out_time',
                'first_half_end_time',
                'second_half_start_time',
                'brack_start_time',
                'brack_end_time',
                'auto_from',
                'auto_to',
                'cutoff_time',
            ] as $field) {
                $normalizedRow[$field] = $this->normalizeTime((string) ($normalizedRow[$field] ?? ''));
            }

            $rows[$lineNumber] = $normalizedRow;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', trim($header));
        $normalized = strtolower(trim((string) $normalized, '_'));

        return match ($normalized) {
            'shift_name', 'name' => 'name',
            'shift_code', 'code' => 'code',
            'location', 'locations', 'location_name', 'location_code', 'location_id' => 'location',
            'company', 'companies', 'company_name', 'company_code', 'company_id' => 'company',
            'break_start_time' => 'brack_start_time',
            'break_end_time' => 'brack_end_time',
            default => $normalized,
        };
    }

    /**
     * @param array<string, string> $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn(string $value): bool => trim($value) === '');
    }

    /**
     * @param array<string, string> $row
     * @return array<int, string>
     */
    private function validateImportRow(array $row): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'in_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'out_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'first_half_end_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'second_half_start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'brack_start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'brack_end_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'auto_from' => ['nullable', 'date_format:H:i,H:i:s'],
            'auto_to' => ['nullable', 'date_format:H:i,H:i:s'],
            'cutoff_time' => ['nullable', 'regex:/^(?:[0-9]{1,2}|[0-7][0-9]{2}|8(?:[0-2][0-9]|3[0-8])):[0-5][0-9](?::[0-5][0-9])?$/'],
        ];

        foreach (['is_night_shift', 'set_cutoff', 'status'] as $field) {
            if ($this->normalizedBoolean((string) ($row[$field] ?? ''), $field === 'status' ? 1 : 0) === null) {
                return [$field . ' must be Active, Inactive, Yes, No, 1, or 0'];
            }
        }

        $validator = Validator::make($row, $rules);
        $errors = $validator->errors()->all();

        if ($this->normalizedBoolean((string) ($row['set_cutoff'] ?? ''), 0) === 1
            && trim((string) ($row['cutoff_time'] ?? '')) === '') {
            $errors[] = 'cutoff_time is required when set_cutoff is Yes';
        }

        if ($this->resolveIds(Location::class, $row['location'] ?? '') === null) {
            $errors[] = 'one or more locations could not be resolved by ID, name or code';
        }

        if ($this->resolveIds(Company::class, $row['company'] ?? '') === null) {
            $errors[] = 'one or more companies could not be resolved by ID, name or code';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $row
     */
    private function findExistingShift(array $row): ?Shift
    {
        foreach (Shift::getImportUniqueFields() as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $shift = Shift::query()->where($field, $value)->first();
                if ($shift !== null) {
                    return $shift;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, string> $row
     */
    private function conflictingUniqueRecords(array $row, ?Shift $existingShift): bool
    {
        if ($existingShift === null) {
            return false;
        }

        $codeRecord = Shift::query()->where('code', trim($row['code']))->first();
        $nameRecord = Shift::query()->where('name', trim($row['name']))->first();

        return ($codeRecord !== null && $codeRecord->id !== $existingShift->id)
            || ($nameRecord !== null && $nameRecord->id !== $existingShift->id);
    }

    /**
     * @param array<string, string> $row
     * @return array<string, mixed>
     */
    private function buildPayload(array $row): array
    {
        $payload = [
            'name' => trim($row['name']),
            'code' => trim($row['code']),
            'location_id' => $this->resolveIds(Location::class, $row['location'] ?? '') ?? [],
            'company_id' => $this->resolveIds(Company::class, $row['company'] ?? '') ?? [],
            'is_night_shift' => $this->normalizedBoolean((string) ($row['is_night_shift'] ?? ''), 0),
            'set_cutoff' => $this->normalizedBoolean((string) ($row['set_cutoff'] ?? ''), 0),
            'status' => $this->normalizedBoolean((string) ($row['status'] ?? ''), 1),
        ];

        foreach ([
            'in_time',
            'out_time',
            'first_half_end_time',
            'second_half_start_time',
            'brack_start_time',
            'brack_end_time',
            'auto_from',
            'auto_to',
            'cutoff_time',
        ] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            $payload[$field] = $value === '' ? null : (strlen($value) === 5 ? $value . ':00' : $value);
        }

        return $payload;
    }

    /**
     * @return array<int, int>|null
     */
    private function resolveIds(string $model, string $values): ?array
    {
        $resolved = [];

        $values = array_filter(array_map('trim', explode(',', $values)), static fn(string $value): bool => $value !== '');

        foreach ($values as $value) {
            $query = $model::query();
            $record = is_numeric($value) ? $query->find((int) $value) : null;
            $record ??= $model::query()->where('name', $value)->first();
            $record ??= $model::query()->where('code', $value)->first();

            if ($record === null) {
                return null;
            }
            $resolved[] = (int) $record->id;
        }

        return array_values(array_unique($resolved));
    }

    private function normalizedBoolean(string $value, int $default): ?int
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return $default;
        }

        return match ($normalized) {
            'yes', 'true', 'active', '1' => 1,
            'no', 'false', 'inactive', '0' => 0,
            default => null,
        };
    }

    private function normalizeTime(string $value): string
    {
        $value = trim($value);

        foreach (['!H:i', '!H:i:s', '!g:i A', '!g:i:s A'] as $format) {
            $time = \DateTimeImmutable::createFromFormat($format, $value);
            $errors = \DateTimeImmutable::getLastErrors();

            if ($time !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $time->format('H:i:s');
            }
        }

        return $value;
    }
}
