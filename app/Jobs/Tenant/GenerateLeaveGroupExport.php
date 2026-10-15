<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Enums\BasedOnEnumn;
use App\Enums\StartBasedOnEnumn;
use App\Enums\YesNoEnumn;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveGroupImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateLeaveGroupExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the Leave Group export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $leaveGroups = LeaveGroup::query()
            ->with([
                'financial_year' => static fn ($query) => $query->withTrashed(),
                'grade_wise_monthly_leave_details' => static fn ($query) => $query
                    ->with(['leave_type', 'sandwich_leave'])
                    ->orderBy('id'),
                'grade_wise_yearly_leave_details' => static fn ($query) => $query
                    ->with(['leave_type', 'sandwich_leave'])
                    ->orderBy('id'),
            ])
            ->withCount('users')
            ->orderBy('name')
            ->get();
        $leaveTypes = LeaveType::withTrashed()->get()->keyBy('id');
        $ruleRows = $leaveGroups->flatMap(
            static fn (LeaveGroup $leaveGroup): Collection => $leaveGroup->grade_wise_monthly_leave_details
                ->concat($leaveGroup->grade_wise_yearly_leave_details),
        );
        $actorIds = $ruleRows
            ->flatMap(static fn (GradeWiseMonthlyLeaveDetail|GradeWiseYearlyLeaveDetail $rule): array => array_filter([
                $rule->created_by,
                $rule->updated_by,
                $rule->deleted_by,
            ]))
            ->unique()
            ->values();
        $actors = $actorIds->isEmpty()
            ? collect()
            : User::withoutGlobalScopes()->whereIn('id', $actorIds)->get()->keyBy('id');
        $contents = $this->buildExportCsv($leaveGroups, $leaveTypes, $actors);
        if (! Storage::disk('local')->put('leave-group-exports/'.$this->fileName, $contents)) {
            throw new RuntimeException('Unable to write the Leave Group export file.');
        }

        $user->notify(new LeaveGroupImportExportCompleted(
            'export',
            'Leave Group export is ready for download.',
            '/employee-structure/leave-groups/export/download/'.rawurlencode($this->fileName),
        ));
    }

    /**
     * @param  Collection<int, LeaveGroup>  $leaveGroups
     * @param  Collection<int|string, LeaveType>  $leaveTypes
     * @param  Collection<int|string, User>  $actors
     */
    private function buildExportCsv(Collection $leaveGroups, Collection $leaveTypes, Collection $actors): string
    {
        $monthlyRuleColumns = $this->ruleColumns(
            new GradeWiseMonthlyLeaveDetail(),
            $leaveGroups->flatMap(
                static fn (LeaveGroup $leaveGroup): Collection => $leaveGroup->grade_wise_monthly_leave_details,
            ),
        );
        $yearlyRuleColumns = $this->ruleColumns(
            new GradeWiseYearlyLeaveDetail(),
            $leaveGroups->flatMap(
                static fn (LeaveGroup $leaveGroup): Collection => $leaveGroup->grade_wise_yearly_leave_details,
            ),
        );
        $maxMonthlyRules = max(1, (int) ($leaveGroups->max(
            static fn (LeaveGroup $leaveGroup): int => $leaveGroup->grade_wise_monthly_leave_details->count(),
        ) ?? 0));
        $maxYearlyRules = max(1, (int) ($leaveGroups->max(
            static fn (LeaveGroup $leaveGroup): int => $leaveGroup->grade_wise_yearly_leave_details->count(),
        ) ?? 0));

        $headers = [
            'name',
            'financial_year',
            'financial_year_code',
            'is_monthly',
            'status',
            'assigned_employee_count',
            'deleted_at',
            'created_at',
            'updated_at',
        ];
        $headers = [
            ...$headers,
            ...$this->ruleHeaders('monthly', $maxMonthlyRules, $monthlyRuleColumns),
            ...$this->ruleHeaders('yearly', $maxYearlyRules, $yearlyRuleColumns),
        ];

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to prepare the Leave Group CSV contents.');
        }

        if (fputcsv($handle, $headers) === false) {
            fclose($handle);
            throw new RuntimeException('Unable to write the Leave Group CSV headers.');
        }

        foreach ($leaveGroups as $leaveGroup) {
            $row = [
                'name' => $leaveGroup->name,
                'financial_year' => $leaveGroup->financial_year?->name ?? '',
                'financial_year_code' => $leaveGroup->financial_year?->code ?? '',
                'is_monthly' => $leaveGroup->is_monthly ? 'Monthly' : 'Yearly',
                'status' => (int) $leaveGroup->status === 1 ? 'Active' : 'Inactive',
                'assigned_employee_count' => $leaveGroup->users_count ?? '',
                'deleted_at' => $leaveGroup->deleted_at?->toDateTimeString() ?? '',
                'created_at' => $leaveGroup->created_at?->toDateTimeString() ?? '',
                'updated_at' => $leaveGroup->updated_at?->toDateTimeString() ?? '',
            ];
            $row['assigned_employee_count'] = (string) ($row['assigned_employee_count'] ?? '');

            $this->appendRuleValues(
                $row,
                'monthly',
                $leaveGroup->grade_wise_monthly_leave_details,
                $monthlyRuleColumns,
                $leaveTypes,
                $actors,
            );
            $this->appendRuleValues(
                $row,
                'yearly',
                $leaveGroup->grade_wise_yearly_leave_details,
                $yearlyRuleColumns,
                $leaveTypes,
                $actors,
            );

            if (fputcsv($handle, array_map(
                static fn (string $header): string => (string) ($row[$header] ?? ''),
                $headers,
            )) === false) {
                fclose($handle);
                throw new RuntimeException('Unable to write a Leave Group CSV row.');
            }
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false) {
            throw new RuntimeException('Unable to read the generated Leave Group CSV contents.');
        }

        return $contents;
    }

    /**
     * @return array<int, string>
     */
    private function ruleColumns(
        GradeWiseMonthlyLeaveDetail|GradeWiseYearlyLeaveDetail $detail,
        Collection $rules,
    ): array {
        $columns = [
            ...$detail->getFillable(),
            ...$rules->flatMap(
                static fn (GradeWiseMonthlyLeaveDetail|GradeWiseYearlyLeaveDetail $rule): array => array_keys($rule->getAttributes()),
            )->all(),
            'created_at',
            'updated_at',
        ];
        $columns = array_filter($columns, static fn (string $column): bool => ! str_ends_with($column, '_id')
            && ! in_array($column, [
                'id',
                'leave_types_not_to_be_clubbed',
                'include_leaves',
                'created_by',
                'updated_by',
                'deleted_by',
            ], true));

        return array_values(array_unique([
            ...$columns,
            'created_by_name',
            'updated_by_name',
            'deleted_by_name',
            'leave_type',
            'sandwich_leave',
            'leave_types_not_to_be_clubbed',
            'include_leaves',
        ]));
    }

    /**
     * @param  array<int, string>  $ruleColumns
     * @return array<int, string>
     */
    private function ruleHeaders(string $frequency, int $ruleCount, array $ruleColumns): array
    {
        $headers = [];
        $labelColumns = [
            'leave_type_code',
            'leave_type_name',
            'sandwich_leave_code',
            'sandwich_leave_name',
        ];

        for ($index = 1; $index <= $ruleCount; $index++) {
            foreach ([...$labelColumns, ...$ruleColumns] as $column) {
                $headers[] = "{$frequency}_rule_{$index}_{$column}";
            }
        }

        return $headers;
    }

    /**
     * @param  Collection<int, GradeWiseMonthlyLeaveDetail|GradeWiseYearlyLeaveDetail>  $rules
     * @param  array<int, string>  $ruleColumns
     * @param  Collection<int|string, LeaveType>  $leaveTypes
     * @param  array<string, string|int|float>  $row
     */
    private function appendRuleValues(
        array &$row,
        string $frequency,
        Collection $rules,
        array $ruleColumns,
        Collection $leaveTypes,
        Collection $actors,
    ): void {
        $booleanColumns = [
            'allow_c_f',
            'allow_sandwich',
            'count_weekly_off_as_present',
            'count_holiday_as_present',
            'is_paid',
            'keeps_balance',
            'allow_negative_balance',
            'allow_backdate_leave',
            'allow_advance_leave',
            'allow_leave_encashment',
            'allow_leave_lapse',
        ];

        foreach ($rules as $index => $rule) {
            $prefix = "{$frequency}_rule_".($index + 1).'_';
            $leaveType = $rule->leave_type ?? $leaveTypes->get($rule->leave_type_id);
            $sandwichLeaveType = $rule->sandwich_leave ?? $leaveTypes->get($rule->sandwich_leave_id);

            $row[$prefix.'leave_type_code'] = $leaveType?->code ?? '';
            $row[$prefix.'leave_type_name'] = $leaveType?->description ?? $leaveType?->name ?? '';
            $row[$prefix.'leave_type'] = $this->leaveTypeLabel($leaveType);
            $row[$prefix.'sandwich_leave_code'] = $sandwichLeaveType?->code ?? '';
            $row[$prefix.'sandwich_leave_name'] = $sandwichLeaveType?->description ?? $sandwichLeaveType?->name ?? '';
            $row[$prefix.'sandwich_leave'] = $this->leaveTypeLabel($sandwichLeaveType);
            $row[$prefix.'leave_types_not_to_be_clubbed'] = $this->leaveTypeNames(
                $rule->getAttribute('leave_types_not_to_be_clubbed'),
                $leaveTypes,
            );
            $row[$prefix.'include_leaves'] = $this->leaveTypeNames(
                $rule->getAttribute('include_leaves'),
                $leaveTypes,
            );

            foreach ($ruleColumns as $column) {
                $actorColumn = match ($column) {
                    'created_by_name' => 'created_by',
                    'updated_by_name' => 'updated_by',
                    'deleted_by_name' => 'deleted_by',
                    default => null,
                };
                $value = $actorColumn === null
                    ? $rule->getAttribute($column)
                    : $this->actorLabel($actors->get($rule->getAttribute($actorColumn)));
                if ($value === null) {
                    $row[$prefix.$column] = '';
                } elseif (in_array($column, $booleanColumns, true)) {
                    $row[$prefix.$column] = $value ? 'Yes' : 'No';
                } elseif ($column === 'based_on') {
                    $row[$prefix.$column] = BasedOnEnumn::tryFrom((int) $value)?->getLabel() ?? (string) $value;
                } elseif (in_array($column, ['proportionate', 'start_credit'], true)) {
                    $row[$prefix.$column] = YesNoEnumn::tryFrom((int) $value)?->getLabel() ?? (string) $value;
                } elseif ($column === 'start_use_based_on') {
                    $row[$prefix.$column] = StartBasedOnEnumn::tryFrom((int) $value)?->getLabel() ?? (string) $value;
                } elseif (is_array($value)) {
                    $row[$prefix.$column] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } else {
                    $row[$prefix.$column] = (string) $value;
                }
            }
        }
    }

    /**
     * @param  mixed  $value
     * @param  Collection<int|string, LeaveType>  $leaveTypes
     */
    private function leaveTypeNames(mixed $value, Collection $leaveTypes): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return '';
        }

        $names = [];
        foreach ($value as $leaveTypeId) {
            $leaveType = $leaveTypes->get($leaveTypeId);
            if ($leaveType !== null) {
                $names[] = trim(($leaveType->code ? $leaveType->code.' - ' : '').($leaveType->description ?? $leaveType->name ?? ''));
            }
        }

        return implode('; ', $names);
    }

    private function leaveTypeLabel(?LeaveType $leaveType): string
    {
        if ($leaveType === null) {
            return '';
        }

        return trim(($leaveType->code ? $leaveType->code.' - ' : '').($leaveType->description ?? $leaveType->name ?? ''));
    }

    private function actorLabel(?User $user): string
    {
        if ($user === null) {
            return '';
        }

        $name = trim(implode(' ', array_filter([$user->fname, $user->mname, $user->lname])));
        $name = $name !== '' ? $name : (string) ($user->name ?? '');

        return trim(implode(' - ', array_filter([$user->code, $name, $user->email])));
    }
}
