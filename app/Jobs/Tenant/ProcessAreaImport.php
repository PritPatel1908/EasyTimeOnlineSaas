<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Area;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\AreaImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessAreaImport implements ShouldQueue
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
        $user = $this->userId !== null ? User::query()->find($this->userId) : null;
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) {
            return;
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

            $companies = $this->resolveCompanies((string) ($row['company'] ?? ''));
            $locations = $this->resolveLocations((string) ($row['location'] ?? ''));
            $validationErrors = $this->validateRow($row, $companies, $locations);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);
                continue;
            }

            $existing = Area::query()->where('code', trim($row['code']))->first();
            if ($existing !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate area code ' . $row['code'] . '. Enable "Update Duplicate Records" to update it.';
                continue;
            }

            $payload = $this->buildPayload($row, $companies, $locations);
            if ($existing !== null) {
                $existing->update($payload);
                $updated++;
            } else {
                Area::query()->create($payload);
                $created++;
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new area record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new AreaImportExportCompleted('import', $message));
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

        $headers = array_map(fn ($header): string => $this->normalizeHeader((string) $header), $headers);
        $rows = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (CsvImportTemplate::isHintRow($row)) {
                continue;
            }

            $normalized = [];
            foreach ($headers as $index => $header) {
                $normalized[$header] = trim((string) ($row[$index] ?? ''));
            }
            $rows[$line] = $normalized;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', trim($header)), '_'));

        return match ($normalized) {
            'area_code', 'code' => 'code',
            'area_name', 'name' => 'name',
            'company', 'companies', 'company_id', 'company_ids', 'company_name', 'company_code' => 'company',
            'location', 'locations', 'location_id', 'location_ids', 'location_name', 'location_code' => 'location',
            'time_limit', 'time_limit_in_min', 'time_limit_in_minutes' => 'time_limit_in_min',
            'dms_area_id', 'dms_id' => 'dms_area_id',
            'status' => 'status',
            default => $normalized,
        };
    }

    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(fn ($value): bool => trim((string) $value) === '');
    }

    private function validateRow(array $row, array $companies, array $locations): array
    {
        $errors = [];

        if (trim((string) ($row['code'] ?? '')) === '') {
            $errors[] = 'code is required';
        }
        if (trim((string) ($row['name'] ?? '')) === '') {
            $errors[] = 'name is required';
        }

        $rawLocations = trim((string) ($row['location'] ?? ''));
        if ($rawLocations !== '' && count($locations) !== count($this->locationValues($rawLocations))) {
            $errors[] = 'one or more locations could not be resolved by ID, name or code';
        }

        $rawCompanies = trim((string) ($row['company'] ?? ''));
        if ($rawCompanies !== '' && count($companies) !== count($this->locationValues($rawCompanies))) {
            $errors[] = 'one or more companies could not be resolved by ID, name or code';
        }

        foreach (['time_limit_in_min' => 4294967295, 'dms_area_id' => PHP_INT_MAX] as $field => $maximum) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && $this->exceedsMaximum($value, $maximum)) {
                $errors[] = $field . ' must be a non-negative integer within its supported range';
            }
        }

        if ($this->normalizeStatus((string) ($row['status'] ?? '1')) === null) {
            $errors[] = 'status must be Active, Inactive, 1 or 0';
        }

        return $errors;
    }

    private function exceedsMaximum(string $value, int $maximum): bool
    {
        if (! ctype_digit($value)) {
            return true;
        }

        $normalizedValue = ltrim($value, '0') ?: '0';
        $maximumValue = (string) $maximum;

        return strlen($normalizedValue) > strlen($maximumValue)
            || (strlen($normalizedValue) === strlen($maximumValue) && strcmp($normalizedValue, $maximumValue) > 0);
    }

    private function locationValues(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));
    }

    private function resolveLocations(string $value): array
    {
        $locations = [];
        foreach ($this->locationValues($value) as $identifier) {
            $query = Location::query();
            $location = ctype_digit($identifier)
                ? $query->find((int) $identifier)
                : $query->where('name', $identifier)->orWhere('code', $identifier)->first();

            if ($location !== null) {
                $locations[$location->getKey()] = $location;
            }
        }

        return array_values($locations);
    }

    private function resolveCompanies(string $value): array
    {
        $companies = [];
        foreach ($this->locationValues($value) as $identifier) {
            $query = Company::query();
            $company = ctype_digit($identifier)
                ? $query->find((int) $identifier)
                : $query->where('name', $identifier)->orWhere('code', $identifier)->first();

            if ($company !== null) {
                $companies[$company->getKey()] = $company;
            }
        }

        return array_values($companies);
    }

    private function buildPayload(array $row, array $companies, array $locations): array
    {
        return [
            'code' => trim((string) $row['code']),
            'name' => trim((string) $row['name']),
            'company_id' => array_map(static fn (Company $company): int => (int) $company->getKey(), $companies),
            'location_id' => array_map(static fn (Location $location): int => (int) $location->getKey(), $locations),
            'time_limit_in_min' => trim((string) ($row['time_limit_in_min'] ?? '')) === ''
                ? 0
                : (int) $row['time_limit_in_min'],
            'dms_area_id' => trim((string) ($row['dms_area_id'] ?? '')) === ''
                ? null
                : (int) $row['dms_area_id'],
            'status' => $this->normalizeStatus((string) ($row['status'] ?? '1')),
        ];
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
