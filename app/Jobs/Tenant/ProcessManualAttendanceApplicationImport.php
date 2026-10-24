<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use App\Services\Tenant\ManualAttendanceApplicationWorkflow;
use App\Support\CsvImportTemplate;
use Carbon\Carbon;
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

class ProcessManualAttendanceApplicationImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the manual attendance application import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the manual attendance application import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ManualPunchImportExportCompleted(
                'import',
                'The uploaded manual attendance application import file could not be found.',
                url: '/time-management/manual-attendance-applications',
            ));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ManualPunchImportExportCompleted(
                'import',
                'The manual attendance application import file is empty, invalid, or missing required columns.',
                url: '/time-management/manual-attendance-applications',
            ));

            return;
        }

        $created = 0;
        $errors = [];
        foreach ($rows as $lineNumber => $row) {
            if (collect(array_values($row))->every(static fn (string $value): bool => trim($value) === '')) {
                continue;
            }

            [$payload, $userIds, $validationErrors] = $this->resolveRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row '.$lineNumber.': '.implode(', ', $validationErrors);

                continue;
            }

            try {
                app(ManualAttendanceApplicationWorkflow::class)->create(
                    $payload,
                    $userIds,
                    (int) $this->userId,
                );
                $created++;
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$lineNumber.': '.collect($exception->errors())->flatten()->implode(', ');
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' manual attendance application(s) submitted for approval.'
            : 'Import completed with '.count($errors).' error(s). '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new ManualPunchImportExportCompleted(
            'import',
            $message,
            url: '/time-management/manual-attendance-applications',
        ));
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

        $headers = array_map(static fn (mixed $header): string => CsvImportTemplate::normalizeHeader((string) $header), $headers);
        if (array_diff(['employee_codes', 'in_time', 'out_time'], $headers) !== []) {
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
     * @param  array<string, string>  $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, string>}
     */
    private function resolveRow(array $row): array
    {
        $errors = [];
        $employeeCodes = array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[;,]/', $row['employee_codes'] ?? '') ?: []),
            static fn (string $code): bool => $code !== '',
        )));
        if ($employeeCodes === []) {
            $errors[] = 'employee_codes is required';
        }
        $users = User::query()->whereIn('code', $employeeCodes)->get(['id', 'code'])->keyBy('code');
        if ($users->count() !== count($employeeCodes)) {
            $errors[] = 'one or more employee codes could not be found';
        }

        $values = [
            'in_time' => trim($row['in_time'] ?? ''),
            'out_time' => trim($row['out_time'] ?? ''),
            'reason' => $row['reason'] ?? '',
        ];
        $validator = Validator::make($values, [
            'in_time' => ['required', 'date'],
            'out_time' => [
                'required',
                'date',
                'after_or_equal:in_time',
                static function (string $attribute, mixed $value, \Closure $fail) use ($values): void {
                    $start = strtotime($values['in_time']);
                    $end = strtotime((string) $value);
                    if ($start !== false && $end !== false && $end - $start > 24 * 60 * 60) {
                        $fail('The out time must be within 24 hours of the in time.');
                    }
                },
            ],
            'reason' => ['nullable', 'string'],
        ]);
        $errors = array_merge($errors, $validator->errors()->all());

        $inTime = $values['in_time'];
        $outTime = $values['out_time'];
        if (! $validator->errors()->has('in_time') && ! $validator->errors()->has('out_time')) {
            $inTime = Carbon::parse($inTime)->format('Y-m-d H:i:s');
            $outTime = Carbon::parse($outTime)->format('Y-m-d H:i:s');
        }

        return [[
            'in_time' => $inTime,
            'out_time' => $outTime,
            'reason' => $values['reason'] !== '' ? $values['reason'] : null,
        ], $users->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(), $errors];
    }
}
