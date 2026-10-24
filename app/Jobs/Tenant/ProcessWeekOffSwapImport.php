<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwap;
use App\Notifications\Tenant\WeekOffSwapImportExportCompleted;
use App\Support\CsvImportTemplate;
use App\Support\WeekOffSwapMusterService;
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

class ProcessWeekOffSwapImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the week-off swap import.');
        }
        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the week-off swap import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $this->finishImport($user, 'The uploaded week-off swap import file could not be found.');

            return;
        }
        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded week-off swap CSV.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            $this->finishImport($user, 'The week-off swap CSV is empty.');

            return;
        }
        $headers = array_map(
            static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            $headers,
        );
        if (array_diff(WeekOffSwap::IMPORT_EXPORT_COLUMNS, $headers) !== []) {
            fclose($handle);
            $this->finishImport($user, 'The CSV is missing one or more required week-off swap columns.');

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

            [$payload, $userIds, $rowErrors] = $this->resolveImportRow($row);
            if ($rowErrors !== []) {
                $errors[] = 'Row '.$line.': '.implode(', ', $rowErrors);

                continue;
            }

            DB::transaction(function () use ($payload, $userIds): void {
                $swap = WeekOffSwap::query()->create($payload);
                $swap->Users()->sync($userIds);
                WeekOffSwapMusterService::applySwap($swap);
            });
            $created++;
        }
        fclose($handle);

        $message = $errors === []
            ? "Imported {$created} week-off swap record(s)."
            : 'Imported '.$created.' week-off swap record(s) with '.count($errors)
                .' error(s): '.implode(' | ', array_slice($errors, 0, 3));
        $this->finishImport($user, $message);
    }

    /**
     * @param array<string, string> $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, string>}
     */
    private function resolveImportRow(array $row): array
    {
        $errors = [];
        $employeeCodes = array_values(array_unique(array_filter(array_map('trim', explode(',', $row['employee_codes'] ?? '')))));
        $users = User::query()->whereIn('code', $employeeCodes)->get(['id', 'name', 'code'])->keyBy('code');
        if ($employeeCodes === [] || $users->count() !== count($employeeCodes)) {
            $errors[] = 'employee_codes is required and every employee code must exist';
        }

        $values = [
            'week_date' => $row['week_date'] ?? '',
            'swap_date' => $row['swap_date'] ?? '',
            'reason' => $row['reason'] ?? '',
        ];
        $validator = Validator::make($values, [
            'week_date' => ['required', 'date_format:Y-m-d'],
            'swap_date' => ['required', 'date_format:Y-m-d', 'different:week_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $errors = array_merge($errors, $validator->errors()->all());
        if (
            $employeeCodes !== []
            && $users->count() === count($employeeCodes)
            && ! $validator->errors()->has('week_date')
        ) {
            $withoutWeekOff = WeekOffSwapMusterService::usersWithoutWeekOff(
                $users->values(),
                $values['week_date'],
            );
            if ($withoutWeekOff->isNotEmpty()) {
                $employeeNames = $withoutWeekOff
                    ->map(static fn (User $employee): string => $employee->name.($employee->code ? ' ('.$employee->code.')' : ''))
                    ->implode(', ');
                $errors[] = 'week_date is not a week-off for: '.$employeeNames;
            }
        }

        return [[
            'week_date' => $values['week_date'],
            'swap_date' => $values['swap_date'],
            'reason' => ($values['reason'] ?? '') !== '' ? $values['reason'] : null,
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ], $users->pluck('id')->map(static fn ($id): int => (int) $id)->all(), $errors];
    }

    private function finishImport(User $user, string $message): void
    {
        $user->notify(new WeekOffSwapImportExportCompleted(
            'import',
            $message,
        ));
        Storage::disk('local')->delete($this->filePath);
    }
}
