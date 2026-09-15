<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CompanyImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessCompanyImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $filePath,
        public bool $updateDuplicateRecords = false,
        public ?string $originalFileName = null,
    ) {}

    public function handle(): void
    {
        if ($this->userId === null) {
            return;
        }

        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $filePath = $this->filePath;

        if (! Storage::disk('local')->exists($filePath)) {
            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($filePath));

        if ($rows === []) {
            Storage::disk('local')->delete($filePath);
            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            $rowNumber = $rowIndex + 2;
            $validationErrors = $this->validateImportRow($row);

            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);

                continue;
            }

            $existingCompany = $this->findExistingCompany($row);

            if ($existingCompany !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate company detected for code ' . ($row['code'] ?? '') . '. Enable "Update Duplicate Records" to update existing records.';

                continue;
            }

            $payload = $this->buildCompanyPayload($row);

            if ($existingCompany !== null) {
                $existingCompany->fill($payload);
                $existingCompany->save();
                $updated++;

                continue;
            }

            Company::query()->create($payload);
            $created++;
        }

        Storage::disk('local')->delete($filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new company record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new CompanyImportExportCompleted('import', $message));
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false || count($headerRow) === 0) {
            fclose($handle);

            return [];
        }

        $mappedHeaders = [];

        foreach ($headerRow as $index => $header) {
            $mappedHeaders[$index] = $this->normalizeHeader((string) $header);
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $normalizedRow = [];

            foreach ($mappedHeaders as $index => $header) {
                $normalizedRow[$header] = trim((string) ($row[$index] ?? ''));
            }

            $rows[] = $normalizedRow;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', trim($header));
        $normalized = strtolower(trim((string) $normalized, '_'));

        return match ($normalized) {
            'company_name', 'name' => 'name',
            'company_code', 'code' => 'code',
            'company_email', 'email' => 'email',
            'status' => 'status',
            'location', 'location_name', 'location_code', 'location_id', 'company_location_id' => 'location',
            default => $normalized,
        };
    }

    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(fn($value) => trim((string) $value) === '');
    }

    private function validateImportRow(array $row): array
    {
        $errors = [];

        if (trim((string) ($row['name'] ?? '')) === '') {
            $errors[] = 'name is required';
        }

        if (trim((string) ($row['code'] ?? '')) === '') {
            $errors[] = 'code is required';
        }

        $status = $this->normalizeStatus((string) ($row['status'] ?? '1'));

        if ($status === null) {
            $errors[] = 'status must be Active, Inactive, 1 or 0';
        }

        $locationValue = trim((string) ($row['location'] ?? ''));

        if ($locationValue !== '' && count($this->resolveLocations($row)) !== count($this->locationValues($row))) {
            $errors[] = 'one or more locations could not be resolved by ID, name or code';
        }

        return $errors;
    }

    private function findExistingCompany(array $row): ?Company
    {
        $uniqueFields = Company::getImportUniqueFields();

        foreach ($uniqueFields as $field) {
            $value = trim((string) ($row[$field] ?? ''));

            if ($value === '') {
                continue;
            }

            $company = Company::query()->where($field, $value)->first();

            if ($company !== null) {
                return $company;
            }
        }

        $name = trim((string) ($row['name'] ?? ''));

        if ($name !== '') {
            return Company::query()->where('name', $name)->first();
        }

        return null;
    }

    private function buildCompanyPayload(array $row): array
    {
        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'code' => trim((string) ($row['code'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')) !== '' ? trim((string) $row['email']) : null,
            'status' => $this->normalizeStatus((string) ($row['status'] ?? '1')),
            'location_id' => collect($this->resolveLocations($row))->pluck('id')->all(),
        ];
    }

    /**
     * @return array<int, Location>
     */
    private function resolveLocations(array $row): array
    {
        return collect($this->locationValues($row))
            ->map(fn(string $value): ?Location => $this->resolveLocationValue($value))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function locationValues(array $row): array
    {
        return collect(explode(',', (string) ($row['location'] ?? '')))
            ->map(fn(string $value): string => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveLocationValue(string $locationValue): ?Location
    {

        if ($locationValue === '') {
            return null;
        }

        if (is_numeric($locationValue)) {
            $location = Location::query()->find((int) $locationValue);

            if ($location !== null) {
                return $location;
            }
        }

        $location = Location::query()->where('name', $locationValue)->first();

        if ($location !== null) {
            return $location;
        }

        return Location::query()->where('code', $locationValue)->first();
    }

    private function normalizeStatus(string $status): ?int
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'active', '1', 'true', 'yes' => 1,
            'inactive', '0', 'false', 'no' => 0,
            default => null,
        };
    }
}
