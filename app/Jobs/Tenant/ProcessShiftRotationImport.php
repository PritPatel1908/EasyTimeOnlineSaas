<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\AfterShiftRotationProcessor;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ShiftRotationImportExportCompleted;
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

class ProcessShiftRotationImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the shift rotation import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the shift rotation import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ShiftRotationImportExportCompleted('import', 'The uploaded shift rotation import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ShiftRotationImportExportCompleted('import', 'The shift rotation import file is empty or could not be read.'));

            return;
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $groups = [];
        $invalidCodes = [];

        foreach ($rows as $line => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            $validationErrors = $this->validateImportRow($row);
            $code = trim((string) ($row['code'] ?? ''));
            if ($validationErrors !== []) {
                $errors[] = 'Row '.$line.': '.implode(', ', $validationErrors);
                if ($code !== '') {
                    $invalidCodes[$code] = true;
                }

                continue;
            }

            $attributes = $this->rotationAttributes($row);
            if (isset($groups[$code]) && $groups[$code]['attributes'] !== $attributes) {
                $errors[] = 'Row '.$line.': Repeated rows for rotation code '.$code.' contain conflicting rotation settings.';
                $invalidCodes[$code] = true;

                continue;
            }

            $groups[$code] ??= ['attributes' => $attributes, 'values' => [], 'lines' => []];
            $groups[$code]['lines'][] = $line;

            $shiftCodes = $this->splitList((string) ($row['shift_code'] ?? ''));
            $daysValues = $this->splitList((string) ($row['days'] ?? ''));

            foreach ($shiftCodes as $index => $shiftCode) {
                $shift = Shift::query()->where('code', $shiftCode)->first();
                if ($shift === null) {
                    $errors[] = 'Row '.$line.': Shift code '.$shiftCode.' could not be found.';
                    $invalidCodes[$code] = true;

                    continue;
                }

                if (in_array((int) $shift->id, array_column($groups[$code]['values'], 'shift_id'), true)) {
                    $errors[] = 'Row '.$line.': Shift '.$shiftCode.' is listed more than once for rotation '.$code.'.';
                    $invalidCodes[$code] = true;

                    continue;
                }

                $groups[$code]['values'][] = [
                    'shift_id' => (int) $shift->id,
                    'days' => (int) $daysValues[$index],
                ];
            }
        }

        foreach ($groups as $code => $group) {
            if (isset($invalidCodes[$code])) {
                continue;
            }

            $existingRotation = ShiftRotation::withTrashed()->where('code', $code)->first();
            if ($existingRotation !== null && ! $this->updateDuplicateRecords) {
                $errors[] = 'Rotation code '.$code.' already exists. Enable "Update Duplicate Records" to update it.';

                continue;
            }

            $rotationId = DB::transaction(function () use ($existingRotation, $group, &$created, &$updated): int {
                if ($existingRotation !== null) {
                    if ($existingRotation->trashed()) {
                        $existingRotation->restore();
                    }
                    $existingRotation->fill($group['attributes'])->save();
                    $existingRotation->rotation_values()->delete();
                    $existingRotation->rotation_values()->createMany($group['values']);
                    $updated++;

                    return $existingRotation->id;
                }

                $rotation = ShiftRotation::query()->create($group['attributes']);
                $rotation->rotation_values()->createMany($group['values']);
                $created++;

                return $rotation->id;
            });
            AfterShiftRotationProcessor::processAfterSave($rotationId);
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' new shift rotation(s) and updated '.$updated.' existing shift rotation(s).'
            : 'Import completed with errors: '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new ShiftRotationImportExportCompleted('import', $message));
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

        $mappedHeaders = [];
        foreach ($headerRow as $index => $header) {
            $mappedHeaders[$index] = $this->normalizeHeader((string) $header);
        }

        $rows = [];
        $lineNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if (CsvImportTemplate::isHintRow($row)) {
                continue;
            }

            $normalizedRow = [];
            foreach ($mappedHeaders as $index => $header) {
                $normalizedRow[$header] = trim((string) ($row[$index] ?? ''));
            }

            $rows[$lineNumber] = $normalizedRow;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', $header), '_'));

        return match ($normalized) {
            'rotation_code', 'shift_rotation_code' => 'code',
            'shift', 'shift_name', 'shift_id' => 'shift_code',
            'rotation_days' => 'days',
            default => $normalized,
        };
    }

    /**
     * @param  array<string, string>  $row
     * @return array<int, string>
     */
    private function validateImportRow(array $row): array
    {
        $errors = Validator::make($row, [
            'code' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ])->errors()->all();

        foreach (['is_forever', 'skip_days_on_month_end', 'skip_days_on_year_end', 'shift_status', 'status'] as $field) {
            if ($this->normalizedBoolean((string) ($row[$field] ?? ''), $field === 'status' ? 1 : 0) === null) {
                $errors[] = $field.' must be Active, Inactive, Yes, No, 1, or 0';
            }
        }

        $isForever = $this->normalizedBoolean((string) ($row['is_forever'] ?? ''), 0) === 1;
        if (! $isForever && trim((string) ($row['end_date'] ?? '')) === '') {
            $errors[] = 'end_date is required unless is_forever is Yes';
        }

        $shiftCodes = $this->splitList((string) ($row['shift_code'] ?? ''));
        $daysValues = $this->splitList((string) ($row['days'] ?? ''));
        if (($shiftCodes === []) !== ($daysValues === [])) {
            $errors[] = 'shift_code and days must both be provided for a rotation step';
        } elseif (count($shiftCodes) !== count($daysValues)) {
            $errors[] = 'shift_code and days must contain the same number of comma-separated values';
        } else {
            foreach ($shiftCodes as $index => $shiftCode) {
                if ($shiftCode === '') {
                    $errors[] = 'shift_code values must not be empty';
                    break;
                }

                if (Validator::make(['days' => $daysValues[$index]], [
                    'days' => ['required', 'integer', 'min:1'],
                ])->fails()) {
                    $errors[] = 'each days value must be a positive integer';
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    private function splitList(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, mixed>
     */
    private function rotationAttributes(array $row): array
    {
        return [
            'code' => trim($row['code']),
            'start_date' => trim((string) ($row['start_date'] ?? '')) ?: null,
            'end_date' => $this->normalizedBoolean((string) ($row['is_forever'] ?? ''), 0) === 1
                ? null
                : (trim((string) ($row['end_date'] ?? '')) ?: null),
            'is_forever' => $this->normalizedBoolean((string) ($row['is_forever'] ?? ''), 0),
            'skip_days_on_month_end' => $this->normalizedBoolean((string) ($row['skip_days_on_month_end'] ?? ''), 0),
            'skip_days_on_year_end' => $this->normalizedBoolean((string) ($row['skip_days_on_year_end'] ?? ''), 0),
            'shift_status' => $this->normalizedBoolean((string) ($row['shift_status'] ?? ''), 0),
            'status' => $this->normalizedBoolean((string) ($row['status'] ?? ''), 1),
        ];
    }

    private function normalizedBoolean(string $value, int $default): ?int
    {
        return match (strtolower(trim($value))) {
            '' => $default,
            '1', 'yes', 'active', 'true' => 1,
            '0', 'no', 'inactive', 'false' => 0,
            default => null,
        };
    }

    /**
     * @param  array<string, string>  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn (string $value): bool => trim($value) === '');
    }
}
