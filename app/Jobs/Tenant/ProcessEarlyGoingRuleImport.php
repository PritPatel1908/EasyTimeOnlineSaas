<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\EarlyGoingRule;
use App\Models\Tenant\User;
use App\Notifications\Tenant\EarlyGoingRuleImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProcessEarlyGoingRuleImport implements ShouldQueue
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
        if ($this->userId === null) {
            throw new RuntimeException('A user is required to process the early going rule import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the early going rule import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new EarlyGoingRuleImportExportCompleted('import', 'The uploaded early going rule import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new EarlyGoingRuleImportExportCompleted('import', 'The early going rule import file is empty or could not be read.'));

            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if (collect($row)->every(static fn (string $value): bool => trim($value) === '')) {
                continue;
            }

            $row['code'] = trim($row['code'] ?? '');
            $row['status'] = trim($row['status'] ?? '') ?: 'active';
            $validator = Validator::make($row, [
                'code' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'ignore_early_going_minutes' => ['required', 'integer', 'min:0', 'max:2147483647'],
                'status' => ['nullable', 'in:active,inactive,1,0'],
            ]);

            if ($validator->fails()) {
                $errors[] = 'Row ' . $rowNumber . ': ' . implode(', ', $validator->errors()->all());
                continue;
            }

            $existingRule = EarlyGoingRule::query()->where('code', trim($row['code']))->first();
            if ($existingRule !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row ' . $rowNumber . ': Duplicate early going rule code ' . $row['code'] . '. Enable "Update Duplicate Records" to update it.';
                continue;
            }

            $payload = [
                'code' => trim($row['code']),
                'description' => trim($row['description'] ?? '') ?: null,
                'ignore_early_going_minutes' => (int) $row['ignore_early_going_minutes'],
                'status' => match (strtolower(trim($row['status'] ?? 'active'))) {
                    'inactive', '0' => 0,
                    default => 1,
                },
            ];

            if ($existingRule !== null) {
                $existingRule->fill($payload)->save();
                $updated++;
                continue;
            }

            EarlyGoingRule::query()->create($payload);
            $created++;
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' new early going rule(s) and updated ' . $updated . ' existing record(s).'
            : 'Import completed with errors: ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new EarlyGoingRuleImportExportCompleted('import', $message));
    }

    /**
     * @return array<int, array<string, string>>|null
     */
    private function parseCsv(string $path): ?array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return null;
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false || $headerRow === []) {
            fclose($handle);

            return null;
        }

        $headers = array_map(static fn (string $header): string => CsvImportTemplate::normalizeHeader($header), $headerRow);
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
            if (! array_key_exists('code', $row) && isset($row['early_going_rule_code'])) {
                $row['code'] = $row['early_going_rule_code'];
            }
            $rows[$lineNumber] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
