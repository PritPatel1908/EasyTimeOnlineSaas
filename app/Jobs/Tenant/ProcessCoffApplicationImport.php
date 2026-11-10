<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CoffApplicationImportExportCompleted;
use App\Services\Tenant\CoffApplicationWorkflow;
use App\Support\CsvImportTemplate;
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

class ProcessCoffApplicationImport implements ShouldQueue
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
        $user = $this->userId === null ? null : User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the COFF application import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new CoffApplicationImportExportCompleted(
                'import',
                'The uploaded COFF application import file could not be found.',
                url: $this->indexUrl(),
            ));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new CoffApplicationImportExportCompleted(
                'import',
                'The COFF application import file is empty, invalid, or missing required columns.',
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

            [$payload, $rowErrors] = $this->resolveRow($row);
            if ($rowErrors !== []) {
                $errors[] = 'Row '.$lineNumber.': '.implode(', ', $rowErrors);

                continue;
            }

            try {
                app(CoffApplicationWorkflow::class)->create($payload, (int) $user->id);
                $created++;
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$lineNumber.': '.collect($exception->errors())->flatten()->implode(', ');
            }
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported '.$created.' COFF application(s) submitted for approval.'
            : 'Import completed with '.count($errors).' error(s). '.implode(' | ', array_slice($errors, 0, 5));
        $user->notify(new CoffApplicationImportExportCompleted(
            'import',
            $message,
            url: $this->indexUrl(),
        ));
    }

    private function indexUrl(): string
    {
        return '/time-management/coff-applications';
    }

    /**
     * @return array<int, array<string, string>>|null
     */
    private function parseCsv(string $path): ?array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('The COFF application import file could not be opened.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);

            return null;
        }

        $headers = array_map(static function ($header): string {
            $rawHeader = strtolower(trim((string) preg_replace(
                '/[^a-z0-9]+/i',
                '_',
                trim((string) $header),
            ), '_'));

            return $rawHeader === 'employee_code'
                ? 'employee_code'
                : CsvImportTemplate::normalizeHeader((string) $header);
        }, $headers);
        if (array_diff(['employee_code', 'coff_against_date', 'coff_from_date', 'coff_to_date'], $headers) !== []) {
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
        $employeeCode = trim($row['employee_code'] ?? '');
        $employee = $employeeCode === ''
            ? null
            : User::query()->where('code', $employeeCode)->first(['id']);
        $errors = [];
        if ($employee === null) {
            $errors[] = $employeeCode === '' ? 'employee_code is required' : 'employee_code could not be found';
        }

        $values = [
            'coff_against_date' => trim($row['coff_against_date'] ?? ''),
            'coff_from_date' => trim($row['coff_from_date'] ?? ''),
            'coff_to_date' => trim($row['coff_to_date'] ?? ''),
            'is_half_day' => $this->parseBoolean($row['is_half_day'] ?? ''),
            'is_first_half' => $this->parseBoolean($row['is_first_half'] ?? ''),
            'is_second_half' => $this->parseBoolean($row['is_second_half'] ?? ''),
        ];
        $validator = Validator::make($values, [
            'coff_against_date' => ['required', 'date_format:Y-m-d'],
            'coff_from_date' => ['required', 'date_format:Y-m-d'],
            'coff_to_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:coff_from_date'],
            'is_half_day' => ['required', 'boolean'],
            'is_first_half' => ['required', 'boolean'],
            'is_second_half' => ['required', 'boolean'],
        ]);
        if ($validator->fails()) {
            $errors = array_merge($errors, $validator->errors()->all());
        }

        if ($errors !== [] || $employee === null) {
            return [[], $errors];
        }

        $sameDate = $values['coff_from_date'] === $values['coff_to_date'];
        if ($values['is_half_day'] && (! $sameDate || $values['is_first_half'] === $values['is_second_half'])) {
            $errors[] = 'A half-day COFF must cover one date and select exactly one half.';
        } elseif (! $values['is_half_day'] && $sameDate && ($values['is_first_half'] || $values['is_second_half'])) {
            $errors[] = 'For a single full day, leave both half selectors unchecked.';
        }
        if ($errors !== []) {
            return [[], $errors];
        }

        return [[
            'user_id' => (int) $employee->id,
            ...$values,
        ], []];
    }

    private function parseBoolean(string $value): ?bool
    {
        return match (strtolower(trim($value))) {
            '1', 'yes', 'true' => true,
            '0', 'no', 'false', '' => false,
            default => null,
        };
    }
}
