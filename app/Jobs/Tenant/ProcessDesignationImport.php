<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\DesignationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessDesignationImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) {
            return;
        }
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            return;
        }
        $headers = array_map(fn($header) => strtolower(trim((string) $header)), fgetcsv($handle) ?: []);
        $created = 0;
        $updated = 0;
        $errors = [];
        while (($values = fgetcsv($handle)) !== false) {
            $result = $this->processRow($headers, $values);
            $created += $result['created'];
            $updated += $result['updated'];
            if ($result['error'] !== null) {
                $errors[] = $result['error'];
            }
        }
        fclose($handle);
        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? "Imported {$created} new designation record(s) and updated {$updated} existing record(s)."
            : 'Import completed with errors: ' . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new DesignationImportExportCompleted('import', $message));
    }

    private function processRow(array $headers, array $values): array
    {
        $result = ['created' => 0, 'updated' => 0, 'error' => null];
        $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
        if (! is_array($row) || trim((string) ($row['code'] ?? '')) === '') {
            return $result;
        }
        $code = trim((string) $row['code']);
        $designation = Designation::query()->where('code', $code)->first();
        if ($designation !== null && ! $this->updateDuplicateRecords) {
            $result['error'] = 'Duplicate code ' . $code;
            return $result;
        }
        $payload = $this->payloadFromRow($row, $code);
        if ($payload['name'] === '') {
            $result['error'] = 'Missing name for code ' . $code;
        } elseif ($designation !== null) {
            $designation->update($payload);
            $result['updated'] = 1;
        } else {
            Designation::create($payload);
            $result['created'] = 1;
        }

        return $result;
    }

    private function payloadFromRow(array $row, string $code): array
    {
        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'code' => $code,
            'status' => $this->parseStatus($row['status'] ?? 'active'),
            'company_id' => $this->resolveRelatedIds(Company::class, $row['company'] ?? ''),
            'location_id' => $this->resolveRelatedIds(Location::class, $row['location'] ?? ''),
            'category_id' => $this->resolveRelatedIds(Category::class, $row['category'] ?? ''),
        ];
    }

    private function parseStatus(mixed $value): int
    {
        return in_array(strtolower(trim((string) $value)), ['inactive', '0', 'no', 'false'], true) ? 0 : 1;
    }

    private function resolveRelatedIds(string $modelClass, mixed $value): array
    {
        $ids = [];
        foreach (array_values(array_filter(array_map('trim', explode(',', (string) $value)))) as $item) {
            $record = ctype_digit($item)
                ? $modelClass::query()->find((int) $item)
                : ($modelClass::query()->where('name', $item)->first() ?? $modelClass::query()->where('code', $item)->first());
            if ($record !== null) {
                $ids[] = $record->getKey();
            }
        }

        return array_values(array_unique($ids));
    }
}
