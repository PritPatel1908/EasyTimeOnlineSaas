<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftRotation;
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

class ProcessManualShiftChangeImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the manual shift change import.');
        }

        $user = User::query()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the manual shift change import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        if (! Storage::disk('local')->exists($this->filePath)) {
            $user->notify(new ManualPunchImportExportCompleted(
                'import',
                'The uploaded manual shift change import file could not be found.',
                url: '/time-management/manual-shift-changes',
            ));

            return;
        }

        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded manual shift change CSV.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            $this->finishImport($user, 'The manual shift change CSV is empty.');

            return;
        }

        $headers = array_map(
            static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            $headers,
        );
        if (array_diff(['employee_codes', 'shift_type', 'from_date'], $headers) !== []) {
            fclose($handle);
            $this->finishImport($user, 'The CSV must include employee_codes, shift_type, and from_date columns.');

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

            [$payload, $userIds, $shiftIds, $validationErrors] = $this->resolveImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row ' . $line . ': ' . implode(', ', $validationErrors);

                continue;
            }

            $shiftChange = DB::transaction(function () use ($payload, $userIds, $shiftIds): ShiftChange {
                $shiftChange = ShiftChange::query()->create($payload);
                $shiftChange->Users()->sync($userIds);
                $shiftChange->shifts()->sync($shiftIds);

                return $shiftChange;
            });

            CreateShiftMuster::dispatch($shiftChange)
                ->onConnection('database_tenant')
                ->onQueue('processing')
                ->afterCommit();
            $created++;
        }
        fclose($handle);

        $message = $errors === []
            ? "Imported {$created} manual shift change record(s)."
            : 'Imported ' . $created . ' manual shift change record(s) with ' . count($errors)
                . ' error(s): ' . implode(' | ', array_slice($errors, 0, 3));
        $this->finishImport($user, $message);
    }

    /**
     * @param array<string, string> $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, int>, 3: array<int, string>}
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

        $type = strtolower(trim($row['shift_type'] ?? ''));
        $shiftValues = $this->splitCsvValues($row['shifts'] ?? '');
        $shiftIds = [];
        $rotationId = null;
        if (in_array($type, ['auto', 'fixed'], true)) {
            $shifts = collect();
            foreach ($shiftValues as $shiftValue) {
                $shift = Shift::query()
                    ->where(function ($query) use ($shiftValue): void {
                        $query->where('code', $shiftValue)->orWhere('name', $shiftValue);
                    })
                    ->first(['id']);
                if ($shift === null) {
                    $errors[] = 'shift "' . $shiftValue . '" could not be found';
                    continue;
                }
                $shifts->push($shift);
            }
            if ($shiftValues === []) {
                $errors[] = 'at least one shift code or name is required';
            }
            if ($type === 'fixed' && count($shiftValues) !== 1) {
                $errors[] = 'fixed shift type requires exactly one shift';
            }
            $shiftIds = $shifts->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        } elseif ($type === 'rotational') {
            $rotation = ShiftRotation::query()
                ->where('code', trim($row['shift_rotation_code'] ?? ''))
                ->first(['id']);
            if ($rotation === null) {
                $errors[] = 'shift_rotation_code must match an existing rotation';
            } else {
                $rotationId = (int) $rotation->id;
            }
        } else {
            $errors[] = 'shift_type must be auto, fixed, or rotational';
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
            'shift_type' => $type,
            'shift_rotation_id' => $rotationId,
            'from_date' => $values['from_date'],
            'is_forever' => $isForever,
            'to_date' => $values['to_date'],
            'reason' => ($values['reason'] ?? '') !== '' ? $values['reason'] : null,
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ], $userIds, $shiftIds, $errors];
    }

    /**
     * @return array<int, string>
     */
    private function splitCsvValues(string $value): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[,;]/', $value) ?: []),
            static fn (string $item): bool => $item !== '',
        )));
    }

    private function importBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
    }

    private function finishImport(User $user, string $message): void
    {
        Storage::disk('local')->delete($this->filePath);
        $user->notify(new ManualPunchImportExportCompleted(
            'import',
            $message,
            url: '/time-management/manual-shift-changes',
        ));
    }
}
