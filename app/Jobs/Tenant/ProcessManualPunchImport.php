<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ManualPunchProcessor;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
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

class ProcessManualPunchImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ?int $userId, public string $filePath)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        if ($this->userId === null) {
            throw new RuntimeException('A user is required to process the manual punch import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the manual punch import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ManualPunchImportExportCompleted('import', 'The uploaded manual punch import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ManualPunchImportExportCompleted('import', 'The manual punch import file is empty, invalid, or missing required columns.'));

            return;
        }

        $created = 0;
        $errors = [];

        foreach ($rows as $lineNumber => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            [$payload, $userIds, $validationErrors] = $this->resolveRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $lineNumber . ': ' . implode(', ', $validationErrors);

                continue;
            }

            DB::transaction(function () use ($payload, $userIds): void {
                $punch = ManualPunch::query()->create($payload + [
                    'created_by' => $this->userId,
                    'updated_by' => $this->userId,
                ]);
                $punch->Users()->sync($userIds);
                ManualPunchProcessor::process($punch);
            });
            $created++;
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' manual punch record(s).'
            : 'Import completed with ' . count($errors) . ' error(s). ' . implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new ManualPunchImportExportCompleted('import', $message));
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

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);

            return null;
        }

        $headers = array_map(static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header), $headers);
        if (array_diff(['employee_codes', 'punch_type', 'punch_date', 'punch_time'], $headers) !== []) {
            fclose($handle);

            return null;
        }

        $rows = [];
        $lineNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if (CsvImportTemplate::isHintRow($row)) {
                continue;
            }

            $normalized = [];
            foreach ($headers as $index => $header) {
                $normalized[$header] = trim((string) ($row[$index] ?? ''));
            }
            $rows[$lineNumber] = $normalized;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param array<string, string> $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn (string $value): bool => trim($value) === '');
    }

    /**
     * @param array<string, string> $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, string>}
     */
    private function resolveRow(array $row): array
    {
        $errors = [];
        $employeeCodes = $this->splitValues($row['employee_codes'] ?? '');
        $userIds = $this->resolveEmployeeIds($employeeCodes, $errors);
        $locationIds = $this->resolveIds(Location::class, $this->splitValues($row['location'] ?? ''), 'location', $errors);
        $companyIds = $this->resolveIds(Company::class, $this->splitValues($row['company'] ?? ''), 'company', $errors);
        $punchDate = trim((string) ($row['punch_date'] ?? ''));
        $punchTime = $this->normalizeTime(trim((string) ($row['punch_time'] ?? '')));

        $validator = Validator::make([
            'punch_type' => trim((string) ($row['punch_type'] ?? '')),
            'punch_date' => $punchDate,
            'punch_time' => $punchTime,
            'reason' => $row['reason'] ?? '',
        ], [
            'punch_type' => ['required', 'string', 'max:255'],
            'punch_date' => ['required', 'date_format:Y-m-d'],
            'punch_time' => ['required', 'date_format:H:i:s'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $errors = array_merge($errors, $validator->errors()->all());

        return [[
            'location_id' => $locationIds,
            'company_id' => $companyIds,
            'punch_type' => trim((string) ($row['punch_type'] ?? '')),
            'punch_date' => $punchDate,
            'punch_time' => $punchTime,
            'reason' => ($row['reason'] ?? '') !== '' ? $row['reason'] : null,
        ], $userIds, $errors];
    }

    /**
     * @param array<int, string> $codes
     * @param array<int, string> $errors
     * @return array<int, int>
     */
    private function resolveEmployeeIds(array $codes, array &$errors): array
    {
        if ($codes === []) {
            $errors[] = 'employee_codes is required';

            return [];
        }

        $users = User::query()->whereIn('code', $codes)->get(['id', 'code'])->keyBy('code');
        if ($users->count() !== count(array_unique($codes))) {
            $errors[] = 'one or more employee codes could not be found';
        }

        return $users->pluck('id')->map(static fn ($id): int => (int) $id)->all();
    }

    /**
     * @param array<int, string> $values
     * @param array<int, string> $errors
     * @return array<int, int>
     */
    private function resolveIds(string $model, array $values, string $field, array &$errors): array
    {
        $ids = [];
        foreach ($values as $value) {
            $query = $model::query();
            $record = ctype_digit($value) ? $query->find((int) $value) : null;
            $record ??= $model::query()->where('name', $value)->first();
            $record ??= $model::query()->where('code', $value)->first();

            if ($record === null) {
                $errors[] = 'one or more ' . $field . ' values could not be resolved';

                return [];
            }
            $ids[] = (int) $record->id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<int, string>
     */
    private function splitValues(string $values): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[;,]/', $values) ?: []),
            static fn (string $value): bool => $value !== '',
        )));
    }

    private function normalizeTime(string $value): string
    {
        foreach (['!H:i', '!H:i:s'] as $format) {
            $time = \DateTimeImmutable::createFromFormat($format, $value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($time !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $time->format('H:i:s');
            }
        }

        return $value;
    }

}
