<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Area;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\Machine;
use App\Models\Tenant\User;
use App\Notifications\Tenant\MachineImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessMachineImport implements ShouldQueue
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
        if ($user === null) {
            return;
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new MachineImportExportCompleted('import', 'The machine import file is no longer available.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            $company = $this->resolveRecord(Company::class, (string) ($row['company'] ?? ''));
            $location = $this->resolveRecord(Location::class, (string) ($row['location'] ?? ''));
            $area = $this->resolveRecord(Area::class, (string) ($row['area'] ?? ''));
            $validationErrors = $this->validateRow($row, $company, $location, $area);

            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);
                continue;
            }

            $serialNumber = trim((string) ($row['machine_serial_number'] ?? ''));
            $existing = $serialNumber === ''
                ? null
                : Machine::query()->where('machine_serial_number', $serialNumber)->first();

            if ($existing !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate machine serial number ' . $serialNumber . '. Enable "Update Duplicate Records" to update it.';
                continue;
            }

            $payload = $this->buildPayload($row, $company, $location, $area);
            if ($existing !== null) {
                $existing->update($payload);
                $updated++;
            } else {
                Machine::query()->create($payload);
                $created++;
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new machine record(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new MachineImportExportCompleted('import', $message));
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
        $normalized = (string) preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim($header)));
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'machine_name', 'name' => 'name',
            'ip', 'machine_ip' => 'machine_ip',
            'serial_number', 'serial', 'machine_serial_number' => 'machine_serial_number',
            'device_id', 'dms_device_id' => 'dms_device_id',
            'dms_area_id' => 'dms_area_id',
            'sync_dms' => 'sync_dms',
            'network_status' => 'network_status',
            'access_direction', 'direction' => 'access_direction',
            'location', 'location_id', 'location_name', 'location_code' => 'location',
            'company', 'company_id', 'company_name', 'company_code' => 'company',
            'area', 'area_id', 'area_name', 'area_code' => 'area',
            'status' => 'status',
            default => $normalized,
        };
    }

    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn ($value): bool => trim((string) $value) === '');
    }

    private function validateRow(array $row, ?Company $company, ?Location $location, ?Area $area): array
    {
        $errors = [];
        if (trim((string) ($row['name'] ?? '')) === '') {
            $errors[] = 'name is required';
        } elseif (mb_strlen(trim((string) $row['name'])) > 255) {
            $errors[] = 'name must not exceed 255 characters';
        }

        foreach (['machine_ip', 'machine_serial_number'] as $field) {
            if (mb_strlen(trim((string) ($row[$field] ?? ''))) > 255) {
                $errors[] = $field . ' must not exceed 255 characters';
            }
        }

        if (mb_strlen(trim((string) ($row['network_status'] ?? ''))) > 255) {
            $errors[] = 'network_status must not exceed 255 characters';
        }

        foreach ([
            'company' => $company,
            'location' => $location,
            'area' => $area,
        ] as $field => $record) {
            if (trim((string) ($row[$field] ?? '')) !== '' && $record === null) {
                $errors[] = 'the ' . $field . ' could not be resolved by ID, name or code';
            }
        }

        foreach (['dms_device_id', 'dms_area_id'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && (! ctype_digit($value) || $this->exceedsMaximum($value, PHP_INT_MAX))) {
                $errors[] = $field . ' must be a non-negative integer within its supported range';
            }
        }

        $syncDms = trim((string) ($row['sync_dms'] ?? ''));
        if ($syncDms !== '' && $this->normalizeBoolean($syncDms) === null) {
            $errors[] = 'sync_dms must be 1, 0, Yes or No';
        }

        $direction = strtolower(trim((string) ($row['access_direction'] ?? '')));
        if ($direction !== '' && ! in_array($direction, ['in', 'out', 'both', '0', '1', '2'], true)) {
            $errors[] = 'access_direction must be in, out, both, 0, 1 or 2';
        }

        if ($this->normalizeStatus((string) ($row['status'] ?? '1')) === null) {
            $errors[] = 'status must be Active, Inactive, 1 or 0';
        }

        return $errors;
    }

    private function exceedsMaximum(string $value, int $maximum): bool
    {
        $normalizedValue = ltrim($value, '0') ?: '0';
        $maximumValue = (string) $maximum;

        return strlen($normalizedValue) > strlen($maximumValue)
            || (strlen($normalizedValue) === strlen($maximumValue) && strcmp($normalizedValue, $maximumValue) > 0);
    }

    private function resolveRecord(string $model, string $identifier): Company|Location|Area|null
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $query = $model::query();

        return ctype_digit($identifier)
            ? $query->find((int) $identifier)
            : $query->where('name', $identifier)->when(
                in_array('code', (new $model)->getFillable(), true),
                static fn ($query) => $query->orWhere('code', $identifier),
            )->first();
    }

    private function buildPayload(array $row, ?Company $company, ?Location $location, ?Area $area): array
    {
        return [
            'name' => trim((string) $row['name']),
            'machine_ip' => $this->nullableString($row['machine_ip'] ?? ''),
            'machine_serial_number' => $this->nullableString($row['machine_serial_number'] ?? ''),
            'dms_device_id' => $this->nullableInteger($row['dms_device_id'] ?? ''),
            'dms_area_id' => $this->nullableInteger($row['dms_area_id'] ?? ''),
            'sync_dms' => $this->normalizeBoolean((string) ($row['sync_dms'] ?? '0')) ?? 0,
            'network_status' => trim((string) ($row['network_status'] ?? '')) ?: 'offline',
            'access_direction' => $this->nullableString(strtolower((string) ($row['access_direction'] ?? ''))),
            'location_id' => $location?->getKey(),
            'company_id' => $company?->getKey(),
            'area_id' => $area?->getKey(),
            'status' => $this->normalizeStatus((string) ($row['status'] ?? '1')),
        ];
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function nullableInteger(string $value): ?int
    {
        $value = trim($value);

        return $value === '' ? null : (int) $value;
    }

    private function normalizeBoolean(string $value): ?int
    {
        return match (strtolower(trim($value))) {
            '1', 'true', 'yes' => 1,
            '0', 'false', 'no' => 0,
            default => null,
        };
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
