<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\FinancialYearImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessFinancialYearImport implements ShouldQueue
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
        $user = $this->userId === null ? null : User::query()->find($this->userId);

        if ($user === null) {
            throw new RuntimeException('The user requesting the Financial Year import could not be found.');
        }

        if (! Storage::disk('local')->exists($this->filePath)) {
            throw new RuntimeException('The uploaded Financial Year import file could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
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

            $financialYear = FinancialYear::query()
                ->where('code', trim((string) ($row['code'] ?? '')))
                ->first();

            if ($financialYear !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate financial year code detected. Enable "Update Duplicate Records" to update it.';
                continue;
            }

            $payload = $this->buildPayload($row);

            if (
                $payload['is_started']
                && FinancialYear::query()
                    ->where('is_started', true)
                    ->whereKeyNot($financialYear?->getKey() ?? 0)
                    ->exists()
            ) {
                $errors[] = 'Row ' . $rowNumber . ': Another Financial Year is already started. Stop it before starting this one.';
                continue;
            }

            if ($financialYear !== null) {
                $financialYear->update($payload);
                $updated++;
                continue;
            }

            FinancialYear::query()->create($payload);
            $created++;
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new financial year record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new FinancialYearImportExportCompleted('import', $message));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('The Financial Year import CSV could not be opened.');
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false || $headerRow === []) {
            fclose($handle);
            return [];
        }

        $headers = array_map(fn (string $header): string => $this->normalizeHeader($header), $headerRow);
        $rows = [];
        $lineNumber = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (CsvImportTemplate::isHintRow($values)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }
            $rows[$lineNumber] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = CsvImportTemplate::normalizeHeader($header);

        return match ($normalized) {
            'financial_year_code' => 'code',
            'financial_year_name' => 'name',
            'location_id', 'location_name', 'location_code', 'locations' => 'location',
            'company_id', 'company_name', 'company_code', 'companies' => 'company',
            default => $normalized,
        };
    }

    /**
     * @param array<string, string> $row
     * @return array<int, string>
     */
    private function validateImportRow(array $row): array
    {
        $errors = [];

        if (trim((string) ($row['code'] ?? '')) === '') {
            $errors[] = 'code is required';
        } elseif (mb_strlen(trim((string) $row['code'])) > 50) {
            $errors[] = 'code must not exceed 50 characters';
        }

        if (mb_strlen(trim((string) ($row['name'] ?? ''))) > 255) {
            $errors[] = 'name must not exceed 255 characters';
        }

        $startDate = $this->validDate((string) ($row['start_date'] ?? ''));
        $endDate = $this->validDate((string) ($row['end_date'] ?? ''));
        if ($startDate === null) {
            $errors[] = 'start_date must be a valid YYYY-MM-DD date';
        }
        if ($endDate === null) {
            $errors[] = 'end_date must be a valid YYYY-MM-DD date';
        } elseif ($startDate !== null && $endDate < $startDate) {
            $errors[] = 'end_date must be on or after start_date';
        }

        if ($this->normalizeBoolean((string) ($row['is_started'] ?? 'No')) === null) {
            $errors[] = 'is_started must be Yes or No';
        }
        if ($this->normalizeBoolean((string) ($row['is_closed'] ?? 'No')) === null) {
            $errors[] = 'is_closed must be Yes or No';
        }
        if ($this->normalizeStatus((string) ($row['status'] ?? 'Active')) === null) {
            $errors[] = 'status must be Active, Inactive, 1 or 0';
        }

        foreach (['location' => Location::class, 'company' => Company::class] as $column => $model) {
            $values = $this->relatedValues((string) ($row[$column] ?? ''));
            if (collect($values)->contains(fn (string $value): bool => $this->resolveRecord($model, $value) === null)) {
                $errors[] = $column . ' contains an ID, name, or code that could not be resolved';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, string> $row
     * @return array<string, mixed>
     */
    private function buildPayload(array $row): array
    {
        $locationIds = $this->resolveRecords(Location::class, $this->relatedValues((string) ($row['location'] ?? '')))
            ->pluck('id')->all();
        $companyIds = $this->resolveRecords(Company::class, $this->relatedValues((string) ($row['company'] ?? '')))
            ->pluck('id')->all();

        return [
            'code' => trim((string) $row['code']),
            'name' => trim((string) ($row['name'] ?? '')) !== '' ? trim((string) $row['name']) : null,
            'start_date' => $this->validDate((string) $row['start_date']),
            'end_date' => $this->validDate((string) $row['end_date']),
            'location_id' => $locationIds,
            'company_id' => $companyIds,
            'is_started' => $this->normalizeBoolean((string) ($row['is_started'] ?? 'No')),
            'is_closed' => $this->normalizeBoolean((string) ($row['is_closed'] ?? 'No')),
            'status' => $this->normalizeStatus((string) ($row['status'] ?? 'Active')),
        ];
    }

    /**
     * @param array<int, string> $values
     * @return \Illuminate\Database\Eloquent\Collection<int, Location|Company>
     */
    private function resolveRecords(string $model, array $values)
    {
        $records = collect();

        foreach ($values as $value) {
            $record = $this->resolveRecord($model, $value);

            if ($record !== null) {
                $records->push($record);
            }
        }

        return $records->unique('id')->values();
    }

    private function resolveRecord(string $model, string $value): Location|Company|null
    {
        $record = is_numeric($value) ? $model::query()->find((int) $value) : null;
        $record ??= $model::query()->where('name', $value)->first();

        return $record ?? $model::query()->where('code', $value)->first();
    }

    /**
     * @return array<int, string>
     */
    private function relatedValues(string $value): array
    {
        return collect(explode(',', $value))
            ->map(static fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function validDate(string $value): ?string
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($value));

        return $date !== false && $date->format('Y-m-d') === trim($value)
            ? $date->format('Y-m-d')
            : null;
    }

    private function normalizeBoolean(string $value): ?bool
    {
        return match (strtolower(trim($value))) {
            '', 'no', '0', 'false' => false,
            'yes', '1', 'true' => true,
            default => null,
        };
    }

    private function normalizeStatus(string $value): ?int
    {
        return match (strtolower(trim($value))) {
            '', 'active', '1', 'true', 'yes' => 1,
            'inactive', '0', 'false', 'no' => 0,
            default => null,
        };
    }

    /**
     * @param array<string, string> $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(static fn (string $value): bool => trim($value) === '');
    }
}
