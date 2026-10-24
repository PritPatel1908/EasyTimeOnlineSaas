<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChange;
use App\Notifications\Tenant\WeekOffChangeImportExportCompleted;
use App\Support\CsvImportTemplate;
use App\Support\WeekOffChangeCsv;
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

class ProcessWeekOffChangeImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the week-off change import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the week-off change import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $this->finishImport($user, 'The uploaded week-off change import file could not be found.');

            return;
        }

        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded week-off change CSV.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            $this->finishImport($user, 'The week-off change CSV is empty.');

            return;
        }

        $headers = array_map(
            static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            $headers,
        );
        if (array_diff(WeekOffChange::IMPORT_EXPORT_COLUMNS, $headers) !== []) {
            fclose($handle);
            $this->finishImport($user, 'The CSV is missing one or more required week-off change columns.');

            return;
        }

        $created = 0;
        $errors = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (CsvImportTemplate::isHintRow($values) || collect($values)->every(static fn ($value): bool => trim((string) $value) === '')) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }

            [$payload, $userIds, $details, $validationErrors] = $this->resolveImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $line . ': ' . implode(', ', $validationErrors);

                continue;
            }

            $weekOffChange = DB::transaction(function () use ($payload, $userIds, $details): WeekOffChange {
                $change = WeekOffChange::query()->create($payload);
                $change->Users()->sync($userIds);
                $change->WeekOffChangeDetails()->createMany($details);

                return $change;
            });

            CreateWeekOffMuster::dispatch($weekOffChange)
                ->onConnection('database_tenant')
                ->onQueue('processing')
                ->afterCommit();
            $created++;
        }
        fclose($handle);

        $message = $errors === []
            ? "Imported {$created} week-off change record(s)."
            : 'Imported ' . $created . ' week-off change record(s) with ' . count($errors)
                . ' error(s): ' . implode(' | ', array_slice($errors, 0, 3));
        $this->finishImport($user, $message);
    }

    /**
     * @param array<string, string> $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, array<string, int>>, 3: array<int, string>}
     */
    private function resolveImportRow(array $row): array
    {
        $errors = [];
        $employeeCodes = $this->splitCsvValues($row['employee_codes'] ?? '');
        $users = User::query()->whereIn('code', $employeeCodes)->get(['id', 'code'])->keyBy('code');
        if ($employeeCodes === [] || $users->count() !== count($employeeCodes)) {
            $errors[] = 'employee_codes is required and every employee code must exist';
        }
        $userIds = $users->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $days = $this->splitCsvValues($row['week_days'] ?? '');
        $types = $this->splitCsvValues($row['wo_types'] ?? '');
        $weekFields = ['first_week', 'second_week', 'third_week', 'fourth_week', 'fifth_week'];
        $weekValues = [];
        foreach ($weekFields as $field) {
            $weekValues[$field] = $this->splitCsvValues($row[$field] ?? '');
        }
        $count = count($days);
        if ($count === 0 || count($types) !== $count) {
            $errors[] = 'week_days and wo_types must contain the same non-zero number of values';
        }
        foreach ($weekValues as $field => $values) {
            if (count($values) !== $count) {
                $errors[] = $field . ' must contain one value per week-off detail';
            }
        }

        $details = [];
        for ($index = 0; $index < $count; $index++) {
            $day = $days[$index] ?? '';
            $type = $types[$index] ?? '';
            $dayNumber = WeekOffChangeCsv::parseWeekDay($day);
            if ($dayNumber === null) {
                $errors[] = 'week_days values must be day numbers 0 through 6 or weekday names';
                continue;
            }
            $typeNumber = WeekOffChangeCsv::parseWeekOffType($type);
            if ($typeNumber === null) {
                $errors[] = 'wo_types values must be Full Day, First Half, Second Half, or 1, 2, or 3';
                continue;
            }

            $detail = [
                'week_days' => $dayNumber,
                'wo_type' => $typeNumber,
            ];
            $hasWeek = false;
            foreach ($weekFields as $field) {
                $weekFlag = WeekOffChangeCsv::parseWeekFlag($weekValues[$field][$index] ?? '');
                if ($weekFlag === null) {
                    $errors[] = $field . ' values must be Yes or No';
                    $detail[$field] = 0;
                    continue;
                }
                $detail[$field] = (int) $weekFlag;
                $hasWeek = $hasWeek || $weekFlag;
            }
            if (! $hasWeek) {
                $errors[] = 'each weekly-off detail must apply to at least one week';
            }
            $details[] = $detail;
        }

        $isForever = $this->importBoolean($row['is_forever'] ?? '');
        if (($row['is_forever'] ?? '') !== '' && ! in_array(strtolower($row['is_forever']), ['0', '1', 'true', 'false', 'yes', 'no'], true)) {
            $errors[] = 'is_forever must be 1 or 0';
        }
        $values = [
            'from_date' => $row['from_date'] ?? '',
            'to_date' => $isForever ? null : ($row['to_date'] ?? ''),
            'reason' => $row['reason'] ?? '',
        ];
        $validator = Validator::make($values, [
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => [$isForever ? 'nullable' : 'required', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $errors = array_merge($errors, $validator->errors()->all());

        return [[
            'from_date' => $values['from_date'],
            'is_forever' => $isForever,
            'to_date' => $values['to_date'],
            'reason' => ($values['reason'] ?? '') !== '' ? $values['reason'] : null,
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ], $userIds, $details, array_values(array_unique($errors))];
    }

    /**
     * @return array<int, string>
     */
    private function splitCsvValues(string $value): array
    {
        return array_values(array_map('trim', preg_split('/[,;]/', $value) ?: []));
    }

    private function importBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
    }

    private function finishImport(User $user, string $message): void
    {
        Storage::disk('local')->delete($this->filePath);
        $user->notify(new WeekOffChangeImportExportCompleted('import', $message));
    }
}
