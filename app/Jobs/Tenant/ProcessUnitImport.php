<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Location;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Notifications\Tenant\UnitImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessUnitImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false) {}

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
            $candidate = Unit::withoutGlobalScopes()->where('code', trim((string) $row['code']))->first();
            $unit = $candidate === null ? null : Unit::query()->find($candidate->getKey());
            if ($candidate !== null && $unit === null) {
                $errors[] = 'Unit code ' . $row['code'] . ' is outside your data policy';
                continue;
            }
            if ($unit !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Duplicate code ' . $row['code'];
                continue;
            }
            $payload = [
                'name' => trim((string) ($row['name'] ?? '')),
                'code' => trim((string) $row['code']),
                'status' => strtolower(trim((string) ($row['status'] ?? 'active'))) === 'inactive' ? 0 : 1,
                'location_id' => $this->resolveRelatedIds($row['location'] ?? ''),
            ];
            if ($payload['name'] === '') {
                $errors[] = 'Missing name for code ' . $row['code'];
                continue;
            }
            if ($unit !== null) {
                $unit->update($payload);
                $updated++;
            } else {
                Unit::query()->create($payload);
                $created++;
            }
        }
        fclose($handle);
        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? "Imported {$created} new unit record(s) and updated {$updated} existing record(s)."
            : 'Import completed with errors: ' . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new UnitImportExportCompleted('import', $message));
    }

    private function resolveRelatedIds(mixed $value): array
    {
        $ids = [];
        foreach (array_values(array_filter(array_map('trim', explode(',', (string) $value)))) as $item) {
            $record = ctype_digit($item)
                ? Location::query()->find((int) $item)
                : (Location::query()->where('name', $item)->first() ?? Location::query()->where('code', $item)->first());
            if ($record !== null) $ids[] = $record->getKey();
        }
        return array_values(array_unique($ids));
    }
}