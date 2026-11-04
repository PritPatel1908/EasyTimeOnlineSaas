<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Notifications\Tenant\OvertimeApplicationImportExportCompleted;
use App\Services\Tenant\OvertimeApplicationWorkflow;
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
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProcessOvertimeApplicationImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the overtime application import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the overtime application import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new OvertimeApplicationImportExportCompleted(
                'import',
                'The uploaded overtime application import file could not be found.',
                url: $this->indexUrl(),
            ));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new OvertimeApplicationImportExportCompleted(
                'import',
                'The overtime application import file is empty, invalid, or missing required columns.',
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
                $created += $this->resolveEmployeeAndCreate($payload);
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$lineNumber.': '.collect($exception->errors())->flatten()->implode(', ');
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' overtime application(s) submitted for approval.'
            : 'Import completed with '.count($errors).' error(s). '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new OvertimeApplicationImportExportCompleted(
            'import',
            $message,
            url: $this->indexUrl(),
        ));
    }

    private function resolveEmployeeAndCreate(array $payload): int
    {
        $codes = array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[;,]/', (string) $payload['employee_codes']) ?: []),
            static fn (string $code): bool => $code !== '',
        )));
        $employees = User::query()->whereIn('code', $codes)->get(['id', 'code'])->keyBy('code');
        $missingCodes = array_values(array_diff($codes, $employees->keys()->all()));
        if ($codes === [] || $missingCodes !== []) {
            throw ValidationException::withMessages([
                'employee_codes' => $codes === []
                    ? 'At least one employee code is required.'
                    : 'Employee code(s) could not be found: '.implode(', ', $missingCodes).'.',
            ]);
        }

        unset($payload['employee_codes']);

        return DB::transaction(function () use ($payload, $codes, $employees): int {
            foreach ($codes as $code) {
                app(OvertimeApplicationWorkflow::class)->create(
                    $payload + ['user_id' => (int) $employees->get($code)->id],
                    (int) $this->userId,
                );
            }

            return count($codes);
        });
    }

    private function indexUrl(): string
    {
        return '/time-management/overtime-applications';
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
        if (array_diff(['employee_codes', 'date', 'overtime_minutes'], $headers) !== []) {
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
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    private function resolveRow(array $row): array
    {
        $payload = [
            'employee_codes' => trim($row['employee_codes'] ?? ''),
            'date' => trim($row['date'] ?? ''),
            'overtime_minutes' => trim($row['overtime_minutes'] ?? ''),
            'reason' => trim($row['reason'] ?? '') ?: null,
        ];
        $validator = Validator::make($payload, [
            'employee_codes' => ['required', 'string', 'max:2000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'overtime_minutes' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        return [$payload, $validator->errors()->all()];
    }

    /**
     * @param  array<string, string>  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect(array_values($row))->every(static fn (string $value): bool => trim($value) === '');
    }
}
