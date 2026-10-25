<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\LeaveReason;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveApplicationImportExportCompleted;
use App\Services\Tenant\LeaveApplicationApprovalWorkflow;
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

class ProcessLeaveApplicationImport implements ShouldQueue
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
            throw new RuntimeException('A user is required to process the leave application import.');
        }
        $user = User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the leave application import could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        if (! Storage::disk('local')->exists($this->filePath)) {
            $this->finish($user, 'The uploaded leave application import file could not be found.');

            return;
        }

        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded leave application CSV.');
        }
        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            $this->finish($user, 'The leave application CSV is empty.');

            return;
        }

        $headers = array_map(
            static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            $headers,
        );
        if (array_diff(LeaveApplication::IMPORT_EXPORT_COLUMNS, $headers) !== []) {
            fclose($handle);
            $this->finish($user, 'The CSV is missing one or more required leave application columns.');

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
                $approvalWorkflow = app(LeaveApplicationApprovalWorkflow::class);
                $approvalFlowIds = $approvalWorkflow->approvalFlowIds($userIds);
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$line.': '.implode(', ', $exception->errors()['users'] ?? ['Invalid leave approval flow']);

                continue;
            }

            DB::transaction(function () use ($payload, $userIds, $approvalFlowIds, $approvalWorkflow): void {
                $application = LeaveApplication::query()->create($payload);
                $application->users()->sync($userIds);
                $approvalWorkflow->dispatch($application, $approvalFlowIds, (int) $this->userId);
            });
            $created++;
        }
        fclose($handle);

        $message = $errors === []
            ? "Imported {$created} leave application(s)."
            : 'Imported '.$created.' leave application(s) with '.count($errors)
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

        $leaveTypeCode = $row['leave_type_code'] ?? '';
        $leaveType = $leaveTypeCode === '' ? null : LeaveType::query()->where('code', $leaveTypeCode)->first();
        if ($leaveTypeCode !== '' && $leaveType === null) {
            $errors[] = 'leave_type_code must match an existing leave type';
        }

        $leaveReasonText = $row['leave_reason'] ?? '';
        $leaveReason = $leaveReasonText === ''
            ? null
            : LeaveReason::query()->where('leave_reason', $leaveReasonText)->first();
        if ($leaveReasonText !== '' && $leaveReason === null) {
            $errors[] = 'leave_reason must match an existing leave reason';
        }

        $flags = [];
        foreach (['is_only_half', 'is_second_half', 'is_first_half'] as $field) {
            $value = strtolower(trim($row[$field] ?? ''));
            if ($value === '') {
                $flags[$field] = false;
            } elseif (in_array($value, ['1', 'true', 'yes'], true)) {
                $flags[$field] = true;
            } elseif (in_array($value, ['0', 'false', 'no'], true)) {
                $flags[$field] = false;
            } else {
                $errors[] = $field.' must be Yes/No or 1/0';
                $flags[$field] = false;
            }
        }

        $statusValue = strtolower(trim($row['status'] ?? '1'));
        $status = match ($statusValue) {
            '', '1', 'active' => 1,
            '0', 'inactive' => 0,
            default => null,
        };
        if ($status === null) {
            $errors[] = 'status must be Active/Inactive or 1/0';
        }

        $values = [
            'from_date' => $row['from_date'] ?? '',
            'to_date' => $row['to_date'] ?? '',
            'application_date' => $row['application_date'] ?? '',
            'reason_explanation' => $row['reason_explanation'] ?? '',
            'leave_count' => ($row['leave_count'] ?? '') === '' ? null : $row['leave_count'],
        ];
        $validator = Validator::make($values, [
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'application_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'reason_explanation' => ['nullable', 'string', 'max:255'],
            'leave_count' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);
        $errors = array_merge($errors, $validator->errors()->all());

        return [[
            'leave_type_id' => $leaveType?->id,
            'leave_reason_id' => $leaveReason?->id,
            'reason_explanation' => $values['reason_explanation'] !== '' ? $values['reason_explanation'] : null,
            ...$flags,
            'from_date' => $values['from_date'],
            'application_date' => $values['application_date'],
            'to_date' => $values['to_date'],
            'leave_count' => $values['leave_count'],
            'status' => $status ?? 1,
            'created_by' => $this->userId,
        ], $userIds, array_values(array_unique($errors))];
    }

    /**
     * @return array<int, string>
     */
    private function splitValues(string $value): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[,;]/', $value) ?: []),
            static fn (string $item): bool => $item !== '',
        )));
    }

    private function finish(User $user, string $message): void
    {
        Storage::disk('local')->delete($this->filePath);
        $user->notify(new LeaveApplicationImportExportCompleted('import', $message));
    }
}
