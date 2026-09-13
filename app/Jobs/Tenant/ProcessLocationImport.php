<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LocationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessLocationImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false) {}

    public function handle(): void
    {
        $user = $this->userId !== null ? User::query()->find($this->userId) : null;
        if ($user === null) return;

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new LocationImportExportCompleted('import', 'The uploaded location import file was not found.'));
            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));

        if ($rows === []) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new LocationImportExportCompleted('import', 'The uploaded location import file is empty or invalid.'));

            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            if ($this->isBlankRow($row)) continue;
            $rowNumber = $rowIndex + 2;
            $validationErrors = $this->validateImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validationErrors);
                continue;
            }

            $existing = $this->findExistingLocation($row);
            if ($existing !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate location detected for code ' . ($row['code'] ?? '') . '. Enable "Update Duplicate Records" to update existing records.';
                continue;
            }

            $payload = $this->buildPayload($row);
            if ($existing !== null) {
                $existing->update($payload);
                $updated++;
            } else {
                Location::query()->create($payload);
                $created++;
            }
        }

        Storage::disk('local')->delete($this->filePath);
        if ($errors !== []) {
            $user->notify(new LocationImportExportCompleted('import', 'Import finished with ' . count($errors) . ' invalid row(s): ' . implode(' | ', array_slice($errors, 0, 5))));
            return;
        }

        $user->notify(new LocationImportExportCompleted('import', 'Imported ' . $created . ' new location record(s) and updated ' . $updated . ' existing record(s).'));
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) return [];
        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            return [];
        }
        $headers = array_map(fn($header) => $this->normalizeHeader((string) $header), $headers);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $normalized = [];
            foreach ($headers as $index => $header) $normalized[$header] = trim((string) ($row[$index] ?? ''));
            $rows[] = $normalized;
        }
        fclose($handle);
        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', trim($header)), '_'));
        return match ($normalized) {
            'location_name', 'name' => 'name',
            'location_code', 'code' => 'code',
            'location_email', 'email' => 'email',
            'lat', 'location_latitude', 'latitude' => 'latitude',
            'lng', 'lon', 'location_longitude', 'longitude' => 'longitude',
            'status' => 'status',
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
        if (trim((string) ($row['name'] ?? '')) === '') $errors[] = 'name is required';
        if (trim((string) ($row['code'] ?? '')) === '') $errors[] = 'code is required';
        if ($this->normalizeStatus((string) ($row['status'] ?? '1')) === null) $errors[] = 'status must be Active, Inactive, 1 or 0';
        foreach (['latitude' => [-90, 90], 'longitude' => [-180, 180]] as $field => [$min, $max]) {
            if (($row[$field] ?? '') !== '' && (! is_numeric($row[$field]) || (float) $row[$field] < $min || (float) $row[$field] > $max)) $errors[] = $field . ' must be a valid coordinate';
        }
        if (($row['email'] ?? '') !== '' && filter_var($row['email'], FILTER_VALIDATE_EMAIL) === false) $errors[] = 'email must be valid';
        return $errors;
    }

    private function findExistingLocation(array $row): ?Location
    {
        foreach (Location::getImportUniqueFields() as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && ($location = Location::query()->where($field, $value)->first()) !== null) return $location;
        }
        return null;
    }

    private function buildPayload(array $row): array
    {
        return [
            'name' => trim((string) $row['name']),
            'code' => trim((string) $row['code']),
            'email' => ($row['email'] ?? '') !== '' ? trim((string) $row['email']) : null,
            'latitude' => ($row['latitude'] ?? '') !== '' ? (float) $row['latitude'] : null,
            'longitude' => ($row['longitude'] ?? '') !== '' ? (float) $row['longitude'] : null,
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
