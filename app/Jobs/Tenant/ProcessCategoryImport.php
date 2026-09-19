<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CategoryImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessCategoryImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }
    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) {
            return;
        }
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            return;
        }
        $headers = array_map(fn($header) => strtolower(trim((string) $header)), fgetcsv($handle) ?: []);
        $created = 0;
        $updated = 0;
        $errors = [];
        while (($values = fgetcsv($handle)) !== false) {
            $result = $this->processRow($headers, $values);
            $created += $result['created'];
            $updated += $result['updated'];
            if ($result['error'] !== null) {
                $errors[] = $result['error'];
            }
        }
        fclose($handle);
        Storage::disk('local')->delete($this->filePath);
        $message = $errors === [] ? "Imported {$created} new category record(s) and updated {$updated} existing record(s)." : 'Import completed with errors: ' . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new CategoryImportExportCompleted('import', $message));
    }

    private function processRow(array $headers, array $values): array
    {
        $result = ['created' => 0, 'updated' => 0, 'error' => null];
        $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
        if (is_array($row) && trim((string) ($row['code'] ?? '')) !== '') {
            $code = trim((string) $row['code']);
            $category = Category::query()->where('code', $code)->first();
            if ($category !== null && ! $this->updateDuplicateRecords) {
                $result['error'] = 'Duplicate code ' . $code;
            } else {
                $payload = $this->payloadFromRow($row, $code);
                if ($payload['name'] === '') {
                    $result['error'] = 'Missing name for code ' . $code;
                } elseif ($category !== null) {
                    $category->update($payload);
                    $result['updated'] = 1;
                } else {
                    Category::create($payload);
                    $result['created'] = 1;
                }
            }
        }
        return $result;
    }

    private function payloadFromRow(array $row, string $code): array
    {
        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'code' => $code,
            'email' => trim((string) ($row['email'] ?? '')) ?: null,
            'status' => $this->parseStatus($row['status'] ?? 'active'),
            'company_id' => $this->resolveRelatedIds(Company::class, $row['company'] ?? ''),
            'location_id' => $this->resolveRelatedIds(Location::class, $row['location'] ?? ''),
            'canteen_break_limit' => trim((string) ($row['canteen_break_limit'] ?? '')) ?: null,
            'need_approval_for_overtime' => $this->parseBooleanValue($row['need_approval_for_overtime'] ?? null),
            'regular_ot_on_wo' => $this->parseBooleanValue($row['regular_ot_on_wo'] ?? null),
            'bypass_timing_rule' => $this->parseBooleanValue($row['bypass_timing_rule'] ?? null),
            'ignore_before_after_shift_punch' => $this->parseBooleanValue($row['ignore_before_after_shift_punch'] ?? null),
            'fix_work_hours' => $this->parseBooleanValue($row['fix_work_hours'] ?? null),
            'fix_work_hours_as_per_shift' => $this->parseBooleanValue($row['fix_work_hours_as_per_shift'] ?? null),
            'fix_work_hours_value' => trim((string) ($row['fix_work_hours_value'] ?? '')) ?: null,
            'ignore_break_in_attendance' => $this->parseBooleanValue($row['ignore_break_in_attendance'] ?? null),
            'reset_halfday_rule_cycle' => $this->parseBooleanValue($row['reset_halfday_rule_cycle'] ?? null),
            'give_double_ot_in_public_holiday' => $this->parseBooleanValue($row['give_double_ot_in_public_holiday'] ?? null),
            'give_double_coff_in_public_holiday' => $this->parseBooleanValue($row['give_double_coff_in_public_holiday'] ?? null),
            'is_week_off_paid' => $this->parseBooleanValue($row['is_week_off_paid'] ?? null),
            'is_holiday_paid' => $this->parseBooleanValue($row['is_holiday_paid'] ?? null),
            'single_punch_allowed_present' => $this->parseBooleanValue($row['single_punch_allowed_present'] ?? null),
            'single_punch_allowed_half_day' => $this->parseBooleanValue($row['single_punch_allowed_half_day'] ?? null),
            'max_short_leave_minutes_per_month' => $this->parseNullableInteger($row['max_short_leave_minutes_per_month'] ?? null),
            'max_short_leave_minutes_per_application' => $this->parseNullableInteger($row['max_short_leave_minutes_per_application'] ?? null),
            'max_occurance_of_short_leave_in_month' => $this->parseNullableInteger($row['max_occurance_of_short_leave_in_month'] ?? null),
            'advance_short_leave_application' => $this->parseNullableInteger($row['advance_short_leave_application'] ?? null),
            'is_eligible_for_c_off' => $this->parseBooleanValue($row['is_eligible_for_c_off'] ?? null),
            'c_off_lapse_in_days' => $this->parseNullableInteger($row['c_off_lapse_in_days'] ?? null),
            'allow_halfday_c_off' => $this->parseBooleanValue($row['allow_halfday_c_off'] ?? null),
            'allow_backdated_leave' => $this->parseBooleanValue($row['allow_backdated_leave'] ?? null),
            'backdated_day_limit' => $this->parseNullableInteger($row['backdated_day_limit'] ?? null),
            'advance_day_limit' => $this->parseNullableInteger($row['advance_day_limit'] ?? null),
            'maximum_accumulation' => $this->parseNullableInteger($row['maximum_accumulation'] ?? null),
            'maximum_request_in_a_month' => $this->parseNullableInteger($row['maximum_request_in_a_month'] ?? null),
            'maximum_request_in_a_year' => $this->parseNullableInteger($row['maximum_request_in_a_year'] ?? null),
            'leave_type_id' => $this->resolveRelatedIds(LeaveType::class, $row['leave_type'] ?? ''),
            'min_avail' => $this->parseNullableDecimal($row['min_avail'] ?? null),
            'max_avail' => $this->parseNullableDecimal($row['max_avail'] ?? null),
            'skip_overtime' => trim((string) ($row['skip_overtime'] ?? '')) ?: null,
        ];
    }

    private function parseStatus(mixed $value): int
    {
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['inactive', '0', 'no', 'false'], true) ? 0 : 1;
    }

    private function parseBooleanValue(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'y', 'active'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'n', 'inactive'], true)) {
            return false;
        }

        return null;
    }

    private function parseNullableInteger(mixed $value): ?int
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : (int) $trimmed;
    }

    private function parseNullableDecimal(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : (string) $trimmed;
    }

    private function resolveRelatedIds(string $modelClass, mixed $value): array
    {
        $ids = [];
        foreach (array_values(array_filter(array_map('trim', explode(',', (string) $value)))) as $item) {
            $record = ctype_digit($item) ? $modelClass::query()->find((int) $item) : ($modelClass::query()->where('name', $item)->first() ?? $modelClass::query()->where('code', $item)->first());
            if ($record !== null) {
                $ids[] = $record->getKey();
            }
        }
        return array_values(array_unique($ids));
    }
}
