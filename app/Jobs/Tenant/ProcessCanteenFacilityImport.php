<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CanteenFacilityImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessCanteenFacilityImport implements ShouldQueue
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
        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
            if (! is_array($row) || trim((string) ($row['code'] ?? '')) === '') {
                continue;
            }

            $rows[] = $row;
        }

        fclose($handle);

        $groupedRows = [];
        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            $groupedRows[$code][] = $row;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($groupedRows as $code => $group) {
            $firstRow = $group[0];
            $location = $this->resolveLocationValue((string) ($firstRow['location'] ?? ''));

            if ($location === null) {
                $errors[] = 'Unauthorized or missing location for code ' . $code;
                continue;
            }

            $facility = CanteenFacility::query()->where('code', $code)->first();
            if ($facility !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Duplicate code ' . $code;
                continue;
            }

            $payload = [
                'name' => trim((string) ($firstRow['name'] ?? '')),
                'code' => $code,
                'total_cfa' => $firstRow['total_cfa'] ?? 0,
                'status' => strtolower(trim((string) ($firstRow['status'] ?? 'active'))) === 'inactive' ? 0 : 1,
                'location_id' => $location->id,
            ];

            if ($payload['name'] === '') {
                $errors[] = 'Missing name for code ' . $code;
                continue;
            }

            if (! is_numeric($payload['total_cfa']) || (float) $payload['total_cfa'] < 0) {
                $errors[] = 'Invalid total CFA for code ' . $code;
                continue;
            }

            $rules = [];
            foreach ($group as $row) {
                $percentageWise = in_array(strtolower(trim((string) ($row['company_contribution_in_percentage_wise'] ?? '0'))), ['1', 'true', 'yes'], true);
                $fixedContribution = trim((string) ($row['company_allowance_contribution_in_fixed'] ?? ''));
                $percentageContribution = trim((string) ($row['company_allowance_contribution_in_percentage'] ?? ''));

                if ($percentageWise && $percentageContribution === '') {
                    $errors[] = 'Missing percentage contribution for code ' . $code;
                    continue 2;
                }

                if (! $percentageWise && $fixedContribution === '') {
                    $errors[] = 'Missing fixed contribution for code ' . $code;
                    continue 2;
                }

                if (! is_numeric($row['total_absent_days'] ?? null) || (int) $row['total_absent_days'] < 0) {
                    $errors[] = 'Invalid total absent days for code ' . $code;
                    continue 2;
                }

                $rules[] = [
                    'total_absent_days' => (int) $row['total_absent_days'],
                    'company_contribution_in_percentage_wise' => $percentageWise,
                    'company_allowance_contribution_in_fixed' => $fixedContribution === '' ? null : $fixedContribution,
                    'company_allowance_contribution_in_percentage' => $percentageContribution === '' ? null : $percentageContribution,
                ];
            }

            if ($rules === []) {
                $errors[] = 'No valid rules found for code ' . $code;
                continue;
            }

            DB::transaction(function () use (&$facility, $payload, $rules, &$created, &$updated): void {
                if ($facility === null) {
                    $facility = CanteenFacility::create($payload);
                    $created++;
                } else {
                    $facility->update($payload);
                    $updated++;
                }

                $facility->rules()->delete();
                $facility->rules()->createMany($rules);
            });
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === [] ? "Imported {$created} new canteen facility record(s) and updated {$updated} existing record(s)." : 'Import completed with errors: ' . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new CanteenFacilityImportExportCompleted('import', $message));
    }

    private function resolveLocationValue(string $locationValue): ?Location
    {
        $locationValue = trim($locationValue);

        if ($locationValue === '') {
            return null;
        }

        if (is_numeric($locationValue)) {
            $location = Location::query()->find((int) $locationValue);

            if ($location !== null) {
                return $location;
            }
        }

        return Location::query()->where('name', $locationValue)->first()
            ?? Location::query()->where('code', $locationValue)->first();
    }
}
