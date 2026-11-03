<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Notifications\Tenant\ShortLeaveApplicationImportExportCompleted;
use App\Services\Tenant\ShortLeaveApplicationWorkflow;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProcessShortLeaveApplicationImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the short leave application import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the short leave application import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ShortLeaveApplicationImportExportCompleted(
                'import',
                'The uploaded short leave application import file could not be found.',
                url: $this->indexUrl(),
            ));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ShortLeaveApplicationImportExportCompleted(
                'import',
                'The short leave application import file is empty, invalid, or missing required columns.',
                url: $this->indexUrl(),
            ));

            return;
        }

        $created = 0;
        $errors = [];
        foreach ($rows as $lineNumber => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            [$payload, $validationErrors] = $this->resolveRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row '.$lineNumber.': '.implode(', ', $validationErrors);

                continue;
            }

            try {
                app(ShortLeaveApplicationWorkflow::class)->create($payload, (int) $this->userId);
                $created++;
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$lineNumber.': '.collect($exception->errors())->flatten()->implode(', ');
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' short leave application(s) submitted for approval.'
            : 'Import completed with '.count($errors).' error(s). '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new ShortLeaveApplicationImportExportCompleted(
            'import',
            $message,
            url: $this->indexUrl(),
        ));
    }

    private function indexUrl(): string
    {
        return '/time-management/short-leave-applications';
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

        $headers = array_map(
            static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            $headers,
        );
        if (array_diff(['employee_code', 'date', 'short_leave_type'], $headers) !== []) {
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
     */
    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn (string $value): bool => trim($value) === '');
    }

    /**
     * @param  array<string, string>  $row
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    private function resolveRow(array $row): array
    {
        $errors = [];
        $employeeCode = trim($row['employee_code'] ?? '');
        $employee = $employeeCode === ''
            ? null
            : User::query()->where('code', $employeeCode)->first(['id']);
        if ($employee === null) {
            $errors[] = $employeeCode === '' ? 'employee_code is required' : 'employee_code could not be found';
        }

        $type = trim($row['short_leave_type'] ?? '');
        $fromTime = $this->normalizeTime(trim($row['from_time'] ?? ''));
        $toTime = $this->normalizeTime(trim($row['to_time'] ?? ''));
        $validator = Validator::make([
            'reason' => trim($row['reason'] ?? ''),
            'date' => trim($row['date'] ?? ''),
            'from_time' => $fromTime,
            'to_time' => $toTime,
            'minutes' => trim($row['minutes'] ?? ''),
            'short_leave_type' => $type,
        ], [
            'reason' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'from_time' => ['nullable', 'date_format:H:i:s'],
            'to_time' => ['nullable', 'date_format:H:i:s'],
            'minutes' => ['required_if:short_leave_type,1,2', 'nullable', 'integer', 'min:1', 'max:1440'],
            'short_leave_type' => ['required', 'integer', 'in:1,2,3'],
        ]);
        if ((int) $type === 3 && ($fromTime === null || $toTime === null || $toTime <= $fromTime)) {
            $validator->errors()->add('from_time', 'Type 3 requires a valid from_time and a later to_time.');
        }
        $errors = array_merge($errors, $validator->errors()->all());

        if ($errors !== [] || $employee === null) {
            return [[], $errors];
        }

        $minutes = (int) $type === 3
            ? (int) Carbon::createFromFormat('H:i:s', (string) $fromTime)
                ->diffInMinutes(Carbon::createFromFormat('H:i:s', (string) $toTime))
            : (trim($row['minutes'] ?? '') !== '' ? (int) $row['minutes'] : null);

        return [[
            'user_id' => (int) $employee->id,
            'reason' => trim($row['reason'] ?? '') !== '' ? trim($row['reason']) : null,
            'date' => trim($row['date']),
            'from_time' => $fromTime,
            'to_time' => $toTime,
            'minutes' => $minutes,
            'short_leave_type' => (int) $type,
        ], []];
    }

    private function normalizeTime(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        foreach (['!H:i', '!H:i:s'] as $format) {
            $time = \DateTimeImmutable::createFromFormat($format, $value);
            if ($time !== false && $time->format(substr($format, 1)) === $value) {
                return $time->format('H:i:s');
            }
        }

        return $value;
    }
}
