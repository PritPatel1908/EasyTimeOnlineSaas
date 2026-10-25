<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveGroupImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessLeaveGroupImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $filePath,
        public bool $updateDuplicateRecords = false,
    ) {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) {
            throw new RuntimeException('The Leave Group import user or file could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $path = Storage::disk('local')->path($this->filePath);
        $requiredImportColumns = array_values(array_diff(LeaveGroup::IMPORT_EXPORT_COLUMNS, ['financial_year_id']));
        $headerErrors = CsvImportTemplate::validateHeaders($path, $requiredImportColumns, true);
        if ($headerErrors !== []) {
            throw new RuntimeException('Invalid Leave Group import file: '.implode(' ', $headerErrors));
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the Leave Group import file.');
        }

        $headers = array_map(
            static fn (string $header): string => match (CsvImportTemplate::normalizeHeader($header)) {
                'financial_year' => 'financial_year_id',
                default => CsvImportTemplate::normalizeHeader($header),
            },
            fgetcsv($handle) ?: [],
        );
        $created = 0;
        $updated = 0;
        $errors = [];
        $rowNumber = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (CsvImportTemplate::isHintRow($values)) {
                continue;
            }

            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
            $result = is_array($row) ? $this->processRow($row) : ['created' => 0, 'updated' => 0, 'error' => 'Malformed CSV row'];
            $created += $result['created'];
            $updated += $result['updated'];
            if ($result['error'] !== null) {
                $errors[] = 'Row '.$rowNumber.': '.$result['error'];
            }
        }
        fclose($handle);
        Storage::disk('local')->delete($this->filePath);

        $message = $errors === []
            ? "Imported {$created} new Leave Group record(s) and updated {$updated} existing record(s)."
            : "Imported {$created} new Leave Group record(s) and updated {$updated} existing record(s); errors: ".implode('; ', array_slice($errors, 0, 5));
        $user->notify(new LeaveGroupImportExportCompleted('import', $message));
    }

    /**
     * @param  array<string, string>  $row
     * @return array{created: int, updated: int, error: string|null}
     */
    private function processRow(array $row): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return ['created' => 0, 'updated' => 0, 'error' => 'Leave Group name is required'];
        }

        $financialYearValue = trim((string) ($row['financial_year_id'] ?? ''));
        if ($financialYearValue === '') {
            $financialYearValue = trim((string) ($row['financial_year_code'] ?? ''));
        }
        $financialYear = null;
        if ($financialYearValue !== '') {
            $financialYear = (ctype_digit($financialYearValue)
                ? FinancialYear::query()->find((int) $financialYearValue)
                : null) ?? FinancialYear::query()
                    ->where('name', $financialYearValue)
                    ->orWhere('code', $financialYearValue)
                    ->first();

            if ($financialYear === null) {
                return ['created' => 0, 'updated' => 0, 'error' => 'Financial year is invalid for '.$name];
            }
        }

        $status = $this->parseStatus($row['status'] ?? '');
        if ($status === null) {
            return ['created' => 0, 'updated' => 0, 'error' => 'Status must be Active or Inactive for '.$name];
        }

        $query = LeaveGroup::query()
            ->where('name', $name)
            ->where('financial_year_id', $financialYear?->id);
        $leaveGroup = $query->first();
        if ($leaveGroup !== null && ! $this->updateDuplicateRecords) {
            return ['created' => 0, 'updated' => 0, 'error' => 'Duplicate Leave Group '.$name];
        }

        $payload = [
            'name' => $name,
            'financial_year_id' => $financialYear?->id,
            'status' => $status,
        ];
        if ($leaveGroup !== null) {
            $leaveGroup->update($payload);

            return ['created' => 0, 'updated' => 1, 'error' => null];
        }

        LeaveGroup::query()->create($payload);

        return ['created' => 1, 'updated' => 0, 'error' => null];
    }

    private function parseStatus(string $value): ?int
    {
        if (trim($value) === '') {
            return 1;
        }

        return match (strtolower(trim($value))) {
            '1', 'active', 'yes' => 1,
            '0', 'inactive', 'no' => 0,
            default => null,
        };
    }
}
