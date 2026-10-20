<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\User;
use App\Notifications\Tenant\OvertimeRuleImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProcessOvertimeRuleImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the overtime rule import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the overtime rule import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new OvertimeRuleImportExportCompleted('import', 'The uploaded overtime rule import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new OvertimeRuleImportExportCompleted('import', 'The overtime rule import file is empty or could not be read.'));

            return;
        }

        $groups = [];
        $invalidCodes = [];
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if (collect($row)->every(static fn (string $value): bool => trim($value) === '')) {
                continue;
            }

            $row['code'] = trim($row['code'] ?? '');
            foreach (['ignore_early_come_minutes', 'ignore_late_come_minutes'] as $timeField) {
                $row[$timeField] = trim($row[$timeField] ?? '') ?: null;
            }
            $row['ignore_late_going'] = $this->normalizeBoolean($row['ignore_late_going'] ?? '');
            $row['ignore_overtime'] = $this->normalizeDurationMinutes(trim($row['ignore_overtime'] ?? '') ?: '0');
            $row['ignore_late_going_overtime'] = $this->normalizeDurationMinutes(trim($row['ignore_late_going_overtime'] ?? '') ?: '0');
            $row['status'] = trim($row['status'] ?? '') ?: 'active';
            $slabFields = [
                'slab_value_from',
                'slab_value_to',
                'slab_head_value',
                'slab_decimal_value',
            ];
            $slabColumns = [];
            foreach ($slabFields as $field) {
                $slabColumns[$field] = array_map('trim', explode(',', $row[$field] ?? ''));
            }
            $slabValue = static fn (array $column, int $index): ?string => ($column[$index] ?? '') === ''
                ? null
                : $column[$index];
            $slabs = [];
            $slabCount = max(array_map('count', $slabColumns));
            for ($index = 0; $index < $slabCount; $index++) {
                $slabs[] = [
                    'value_from' => $slabValue($slabColumns['slab_value_from'], $index),
                    'value_to' => $slabValue($slabColumns['slab_value_to'], $index),
                    'head_value' => $slabValue($slabColumns['slab_head_value'], $index),
                    'decimal_value' => $slabValue($slabColumns['slab_decimal_value'], $index),
                ];
            }
            if ($slabs === []) {
                $slabs[] = [
                    'value_from' => null,
                    'value_to' => null,
                    'head_value' => null,
                    'decimal_value' => null,
                ];
            }

            $validationRow = $row;
            $validationRow['slabs'] = $slabs;
            foreach ($slabFields as $field) {
                unset($validationRow[$field]);
            }

            $validator = Validator::make($validationRow, [
                'code' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'ignore_early_come_minutes' => ['nullable', 'date_format:H:i,H:i:s'],
                'ignore_late_come_minutes' => ['nullable', 'date_format:H:i,H:i:s'],
                'ignore_late_going' => ['required', 'boolean'],
                'ignore_overtime' => ['required', 'numeric', 'min:0', 'max:999999.99'],
                'ignore_late_going_overtime' => ['required', 'numeric', 'min:0', 'max:999999.99'],
                'status' => ['nullable', 'in:active,inactive,1,0'],
                'slabs' => ['array'],
                'slabs.*.value_from' => ['nullable', 'date_format:H:i,H:i:s'],
                'slabs.*.value_to' => ['nullable', 'date_format:H:i,H:i:s'],
                'slabs.*.head_value' => ['nullable', 'date_format:H:i,H:i:s'],
                'slabs.*.decimal_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            ]);

            if ($validator->fails()) {
                $errors[] = 'Row '.$rowNumber.': '.implode(', ', $validator->errors()->all());
                if ($row['code'] !== '') {
                    $invalidCodes[$row['code']] = true;
                }

                continue;
            }

            $parent = [
                'code' => $row['code'],
                'description' => trim($row['description'] ?? '') ?: null,
                'ignore_early_come_minutes' => $row['ignore_early_come_minutes'],
                'ignore_late_come_minutes' => $row['ignore_late_come_minutes'],
                'ignore_late_going' => (bool) $row['ignore_late_going'],
                'ignore_overtime' => $row['ignore_overtime'],
                'ignore_late_going_overtime' => $row['ignore_late_going_overtime'],
                'status' => match (strtolower(trim($row['status'] ?? 'active'))) {
                    'inactive', '0' => 0,
                    default => 1,
                },
            ];
            if (! isset($groups[$parent['code']])) {
                $groups[$parent['code']] = [
                    'parent' => $parent,
                    'slabs' => [],
                    'conflict' => false,
                ];
            } elseif ($groups[$parent['code']]['parent'] !== $parent) {
                $errors[] = 'Row '.$rowNumber.': Rule fields must be identical on every row for overtime rule '.$parent['code'].'.';
                $groups[$parent['code']]['conflict'] = true;
            }

            foreach ($slabs as $slab) {
                $hasSlab = collect($slab)->contains(
                    static fn (mixed $value): bool => $value !== null && trim((string) $value) !== '',
                );
                if ($hasSlab) {
                    $groups[$parent['code']]['slabs'][] = $slab;
                }
            }
        }

        $created = 0;
        $updated = 0;
        foreach ($groups as $code => $group) {
            if ($group['conflict'] || isset($invalidCodes[$code])) {
                continue;
            }

            $existingRule = OvertimeRule::withTrashed()->where('code', $code)->first();
            if ($existingRule !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Duplicate overtime rule code '.$code.'. Enable "Update Duplicate Records" to update it.';

                continue;
            }

            DB::transaction(function () use ($existingRule, $group, &$created, &$updated): void {
                if ($existingRule !== null) {
                    if ($existingRule->trashed()) {
                        $existingRule->restore();
                    }
                    $existingRule->fill($group['parent'])->save();
                    $existingRule->overtime_slabs()->delete();
                    $existingRule->overtime_slabs()->createMany($group['slabs']);
                    $updated++;

                    return;
                }

                $overtimeRule = OvertimeRule::query()->create($group['parent']);
                $overtimeRule->overtime_slabs()->createMany($group['slabs']);
                $created++;
            });
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' new overtime rule(s) and updated '.$updated.' existing record(s).'
            : 'Import completed with errors: '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new OvertimeRuleImportExportCompleted('import', $message));
    }

    private function normalizeBoolean(string $value): string
    {
        return match (strtolower(trim($value))) {
            'true', 'yes', 'on' => '1',
            'false', 'no', 'off', '' => '0',
            default => $value,
        };
    }

    private function normalizeDurationMinutes(string $value): string
    {
        if (preg_match('/\A(\d+):([0-5]\d)\z/', $value, $matches) !== 1) {
            return $value;
        }

        return (string) ((int) $matches[1] * 60 + (int) $matches[2]);
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
            if (! array_key_exists('code', $row) && isset($row['overtime_rule_code'])) {
                $row['code'] = $row['overtime_rule_code'];
            }
            $rows[$lineNumber] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
