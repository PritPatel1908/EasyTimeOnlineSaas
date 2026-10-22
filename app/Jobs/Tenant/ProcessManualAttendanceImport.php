<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualAttendance;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use App\Support\CsvImportTemplate;
use App\Jobs\Tenant\ProcessManualAttendancePunches;
use Carbon\Carbon;
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

class ProcessManualAttendanceImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the manual attendance import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the manual attendance import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ManualPunchImportExportCompleted('import', 'The uploaded manual attendance import file could not be found.'));

            return;
        }

        $rows = $this->parseCsv(Storage::disk('local')->path($this->filePath));
        if ($rows === null) {
            Storage::disk('local')->delete($this->filePath);
            $user->notify(new ManualPunchImportExportCompleted('import', 'The manual attendance import file is empty, invalid, or missing required columns.'));

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

            $attendance = DB::transaction(function () use ($payload, $userIds): ManualAttendance {
                $attendance = ManualAttendance::query()->create($payload + [
                    'created_by' => $this->userId,
                    'updated_by' => $this->userId,
                ]);
                $attendance->Users()->sync($userIds);

                return $attendance->load('Users');
            });

            ApproveUserManualAttendance::dispatch($attendance)
                ->onConnection('database_tenant')
                ->onQueue('processing');
            ProcessManualAttendancePunches::dispatch((int) $attendance->id)
                ->onConnection('database_tenant')
                ->onQueue('processing');
            $created++;
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? 'Imported ' . $created . ' manual attendance record(s).'
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
        $userIds = $this->resolveEmployeeIds($this->splitValues($row['employee_codes'] ?? ''), $errors);
        $locationIds = $this->resolveOptionalIds(Location::class, $row['location'] ?? '', 'location', $errors);
        $companyIds = $this->resolveOptionalIds(Company::class, $row['company'] ?? '', 'company', $errors);
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
                    $inTime = strtotime((string) $values['in_time']);
                    $outTime = strtotime((string) $value);

                    if ($inTime !== false && $outTime !== false && $outTime - $inTime > 24 * 60 * 60) {
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
            'location_ids' => $locationIds,
            'company_ids' => $companyIds,
            'location_id' => count($locationIds) === 1 ? $locationIds[0] : null,
            'company_id' => count($companyIds) === 1 ? $companyIds[0] : null,
            'in_time' => $inTime,
            'out_time' => $outTime,
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
     * @param array<int, string> $errors
     */
    private function resolveOptionalId(string $model, string $value, string $field, array &$errors): ?int
    {
        if ($value === '') {
            return null;
        }

        $record = ctype_digit($value) ? $model::query()->find((int) $value) : null;
        if ($model !== ShiftRotation::class) {
            $record ??= $model::query()->where('name', $value)->first();
        }
        $record ??= $model::query()->where('code', $value)->first();

        if ($record === null) {
            $errors[] = 'the ' . $field . ' value could not be resolved';

            return null;
        }

        return (int) $record->id;
    }

    /**
     * @param array<int, string> $errors
     * @return array<int, int>
     */
    private function resolveOptionalIds(string $model, string $values, string $field, array &$errors): array
    {
        $ids = [];
        foreach ($this->splitValues($values) as $value) {
            $id = $this->resolveOptionalId($model, $value, $field, $errors);
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
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
}
