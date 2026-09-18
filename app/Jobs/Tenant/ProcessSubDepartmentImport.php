<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SubDepartmentImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessSubDepartmentImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) return;
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) return;
        $headers = array_map(fn($header) => strtolower(trim((string) $header)), fgetcsv($handle) ?: []);
        $created = 0;
        $updated = 0;
        $errors = [];
        while (($values = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
            if (! is_array($row) || trim((string) ($row['code'] ?? '')) === '') continue;
            $subDepartment = SubDepartment::query()->where('code', trim((string) $row['code']))->first();
            if ($subDepartment !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Duplicate code ' . $row['code'];
                continue;
            }
            $payload = [
                'name' => trim((string) ($row['name'] ?? '')),
                'code' => trim((string) $row['code']),
                'email' => trim((string) ($row['email'] ?? '')) ?: null,
                'status' => strtolower(trim((string) ($row['status'] ?? 'active'))) === 'inactive' ? 0 : 1,
                'department_id' => $this->resolveRelatedIds(Department::class, $row['department'] ?? ''),
                'location_id' => $this->resolveRelatedIds(Location::class, $row['location'] ?? ''),
            ];
            if ($payload['name'] === '') {
                $errors[] = 'Missing name for code ' . $row['code'];
                continue;
            }
            if ($subDepartment !== null) {
                $subDepartment->update($payload);
                $updated++;
            } else {
                SubDepartment::create($payload);
                $created++;
            }
        }
        fclose($handle);
        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? "Imported {$created} new sub department record(s) and updated {$updated} existing record(s)."
            : 'Import completed with errors: ' . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new SubDepartmentImportExportCompleted('import', $message));
    }

    private function names(mixed $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    private function resolveRelatedIds(string $modelClass, mixed $value): array
    {
        $ids = [];
        foreach ($this->names($value) as $item) {
            $record = ctype_digit($item)
                ? $modelClass::query()->find((int) $item)
                : ($modelClass::query()->where('name', $item)->first() ?? $modelClass::query()->where('code', $item)->first());
            if ($record !== null) $ids[] = $record->getKey();
        }
        return array_values(array_unique($ids));
    }
}
