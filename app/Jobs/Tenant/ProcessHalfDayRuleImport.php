<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\HalfDayRule;
use App\Models\Tenant\User;
use App\Notifications\Tenant\HalfDayRuleImportExportCompleted;
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

class ProcessHalfDayRuleImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the half-day rule import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the half-day rule import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new HalfDayRuleImportExportCompleted('import', 'The uploaded half-day rule import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new HalfDayRuleImportExportCompleted('import', 'The half-day rule import file is empty or could not be read.'));

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
            $row['single_punch_allowed'] = $this->normalizeBoolean($row['single_punch_allowed'] ?? '');
            $row['ignore_month_end_late'] = $this->normalizeBoolean($row['ignore_month_end_late'] ?? '');
            $row['ignore_month_end_early'] = $this->normalizeBoolean($row['ignore_month_end_early'] ?? '');
            $row['status'] = trim($row['status'] ?? '') ?: 'active';

            foreach ([
                'work_hr_less_than_minutes',
                'late_coming_minutes',
                'no_of_late',
                'consecutive_late_coming',
                'early_going_minutes',
                'no_of_early',
                'consecutive_early_going',
            ] as $numberField) {
                if (isset($row[$numberField]) && trim($row[$numberField]) === '') {
                    $row[$numberField] = null;
                }
            }

            $validator = Validator::make($row, [
                'code' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'single_punch_allowed' => ['required', 'boolean'],
                'work_hr_less_than_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                'late_coming_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                'no_of_late' => ['nullable', 'numeric', 'min:0'],
                'consecutive_late_coming' => ['nullable', 'integer', 'min:0'],
                'ignore_month_end_late' => ['required', 'boolean'],
                'early_going_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                'no_of_early' => ['nullable', 'numeric', 'min:0'],
                'consecutive_early_going' => ['nullable', 'numeric', 'min:0'],
                'ignore_month_end_early' => ['required', 'boolean'],
                'status' => ['nullable', 'in:active,inactive,1,0'],
            ]);

            if ($validator->fails()) {
                $errors[] = 'Row '.$rowNumber.': '.implode(', ', $validator->errors()->all());

                continue;
            }

            $existingRule = HalfDayRule::query()->where('code', $row['code'])->first();
            if ($existingRule !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Row '.$rowNumber.': Duplicate half-day rule code '.$row['code'].'. Enable "Update Duplicate Records" to update it.';

                continue;
            }

            $payload = [
                'code' => $row['code'],
                'description' => trim($row['description'] ?? '') ?: null,
                'single_punch_allowed' => (bool) $row['single_punch_allowed'],
                'work_hr_less_than_minutes' => $row['work_hr_less_than_minutes'],
                'late_coming_minutes' => $row['late_coming_minutes'],
                'no_of_late' => $row['no_of_late'],
                'consecutive_late_coming' => $row['consecutive_late_coming'],
                'ignore_month_end_late' => (bool) $row['ignore_month_end_late'],
                'early_going_minutes' => $row['early_going_minutes'],
                'no_of_early' => $row['no_of_early'],
                'consecutive_early_going' => $row['consecutive_early_going'],
                'ignore_month_end_early' => (bool) $row['ignore_month_end_early'],
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

            HalfDayRule::query()->create($payload);
            $created++;
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' new half-day rule(s) and updated '.$updated.' existing record(s).'
            : 'Import completed with errors: '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new HalfDayRuleImportExportCompleted('import', $message));
    }

    private function normalizeBoolean(string $value): string
    {
        return match (strtolower(trim($value))) {
            'true', 'yes', 'on' => '1',
            'false', 'no', 'off', '' => '0',
            default => $value,
        };
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
            if (! array_key_exists('code', $row) && isset($row['half_day_rule_code'])) {
                $row['code'] = $row['half_day_rule_code'];
            }
            $rows[$lineNumber] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
