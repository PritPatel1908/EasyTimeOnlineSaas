<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\WeekOffChangeApplicationImportExportCompleted;
use App\Services\Tenant\WeekOffChangeApplicationWorkflow;
use App\Support\CsvImportTemplate;
use App\Support\WeekOffChangeCsv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProcessWeekOffChangeApplicationImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the week-off change application import.');
        }
        $user = User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the week-off change application import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        if (! Storage::disk('local')->exists($this->filePath)) {
            $this->finish($user, 'The uploaded week-off change application import file could not be found.');

            return;
        }

        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded week-off change application CSV.');
        }
        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            $this->finish($user, 'The week-off change application CSV is empty.');

            return;
        }

        $headers = array_map(
            static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            $headers,
        );
        if (array_diff(\App\Models\Tenant\WeekOffChangeApplication::IMPORT_EXPORT_COLUMNS, $headers) !== []) {
            fclose($handle);
            $this->finish($user, 'The CSV is missing one or more required week-off change application columns.');

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
            [$payload, $userIds, $rowErrors] = $this->resolveRow($row);
            if ($rowErrors !== []) {
                $errors[] = 'Row '.$line.': '.implode(', ', $rowErrors);

                continue;
            }

            try {
                app(WeekOffChangeApplicationWorkflow::class)->create($payload, $userIds, (int) $this->userId);
                $created++;
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$line.': '.collect($exception->errors())->flatten()->implode(', ');
            }
        }
        fclose($handle);
        $message = $errors === []
            ? "Imported {$created} week-off change application(s) submitted for approval."
            : 'Imported '.$created.' week-off change application(s) with '.count($errors)
                .' error(s): '.implode(' | ', array_slice($errors, 0, 5));
        $this->finish($user, $message);
    }

    /**
     * @param  array<string, string>  $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, string>}
     */
    private function resolveRow(array $row): array
    {
        $errors = [];
        $employeeCodes = $this->splitValues($row['employee_codes'] ?? '');
        $users = User::query()->whereIn('code', $employeeCodes)->get(['id', 'code'])->keyBy('code');
        if ($employeeCodes === [] || $users->count() !== count(array_unique($employeeCodes))) {
            $errors[] = 'employee_codes is required and every employee code must exist';
        }
        $userIds = $users->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();

        $days = $this->splitValues($row['week_days'] ?? '');
        $types = $this->splitValues($row['wo_types'] ?? '');
        $weekFields = ['first_week', 'second_week', 'third_week', 'fourth_week', 'fifth_week'];
        $weekValues = [];
        foreach ($weekFields as $field) {
            $weekValues[$field] = $this->splitValues($row[$field] ?? '');
        }
        $count = count($days);
        if ($count === 0 || count($types) !== $count) {
            $errors[] = 'week_days and wo_types must contain the same non-zero number of values';
        }
        foreach ($weekValues as $field => $values) {
            if (count($values) !== $count) {
                $errors[] = $field.' must contain one value per week-off detail';
            }
        }

        $details = [];
        foreach ($days as $index => $day) {
            $dayNumber = WeekOffChangeCsv::parseWeekDay($day);
            $typeNumber = WeekOffChangeCsv::parseWeekOffType($types[$index] ?? '');
            if ($dayNumber === null || $typeNumber === null) {
                $errors[] = 'week_days and wo_types must contain valid week day names/numbers and week-off types';

                continue;
            }
            $detail = ['week_days' => $dayNumber, 'wo_type' => $typeNumber];
            $hasWeek = false;
            foreach ($weekFields as $field) {
                $flag = WeekOffChangeCsv::parseWeekFlag($weekValues[$field][$index] ?? '');
                if ($flag === null) {
                    $errors[] = $field.' values must be Yes or No';
                    $flag = false;
                }
                $detail[$field] = (int) $flag;
                $hasWeek = $hasWeek || $flag;
            }
            if (! $hasWeek) {
                $errors[] = 'each weekly-off detail must apply to at least one week';
            }
            $details[] = $detail;
        }

        $isForeverValue = strtolower(trim($row['is_forever'] ?? ''));
        if ($isForeverValue !== '' && ! in_array($isForeverValue, ['0', '1', 'true', 'false', 'yes', 'no'], true)) {
            $errors[] = 'is_forever must be 1 or 0';
        }
        $isForever = in_array($isForeverValue, ['1', 'true', 'yes'], true);
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
            'details' => $details,
        ], $userIds, array_values(array_unique($errors))];
    }

    /**
     * @return array<int, string>
     */
    private function splitValues(string $value): array
    {
        return array_values(array_map('trim', preg_split('/[,;]/', $value) ?: []));
    }

    private function finish(User $user, string $message): void
    {
        Storage::disk('local')->delete($this->filePath);
        $user->notify(new WeekOffChangeApplicationImportExportCompleted('import', $message));
    }
}
