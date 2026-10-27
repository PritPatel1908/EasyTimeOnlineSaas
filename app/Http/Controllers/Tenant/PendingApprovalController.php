<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\WOTypeEnumn;
use App\Helpers\ApprovalProcessor;
use App\Http\Controllers\Controller;
use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\Coff;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Support\TenantPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PendingApprovalController extends Controller
{
    private const APPROVABLE_TYPES = [
        LeaveApplication::class,
        Coff::class,
        ManualAttendanceApplication::class,
        ManualPunchApplication::class,
        OutDuty::class,
        ShortLeaveApplication::class,
        ShiftChangeApplication::class,
        WeekOffChangeApplication::class,
        WeekOffSwapApplication::class,
    ];

    public function index(): View
    {
        TenantPermissions::authorize('PendingRequest', 'read');
        $userId = (int) Auth::guard('tenant')->id();
        $pendingEmployeeUsers = $this->pendingEmployeeUsers();

        $approvals = ApprovalStatusDetails::query()
            ->with([
                'main_status.approvable' => function (MorphTo $morphTo) use ($pendingEmployeeUsers): void {
                    $morphTo->morphWith([
                        LeaveApplication::class => ['users', 'leave_type', 'leave_reason'],
                        Coff::class => ['user'],
                        ManualAttendanceApplication::class => ['Users'],
                        ManualPunchApplication::class => ['Users', 'user'],
                        OutDuty::class => ['user'],
                        ShortLeaveApplication::class => ['user'],
                        ShiftChangeApplication::class => ['Users' => $pendingEmployeeUsers],
                        WeekOffChangeApplication::class => ['Users'],
                        WeekOffSwapApplication::class => ['Users'],
                    ]);
                },
                'main_status.approval_flow',
                'approval_flow_detail',
            ])
            ->where('approval_status', 'pending')
            ->whereHas('main_status', function ($query): void {
                $query->where('approval_status', 'pending')
                    ->whereIn('approvable_type', self::APPROVABLE_TYPES);
            })
            ->whereHas('approval_flow_detail.users', fn ($query) => $query->whereKey($userId))
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('approval_status_details as previous_details')
                    ->whereColumn('previous_details.approval_status_id', 'approval_status_details.approval_status_id')
                    ->whereColumn('previous_details.level', '<', 'approval_status_details.level')
                    ->where('previous_details.approval_status', '!=', 'approved');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('approval_statuses as current_status')
                    ->join('approval_statuses as rejected_statuses', function ($join): void {
                        $join->on('rejected_statuses.approvable_id', '=', 'current_status.approvable_id')
                            ->on('rejected_statuses.approvable_type', '=', 'current_status.approvable_type');
                    })
                    ->whereColumn('current_status.id', 'approval_status_details.approval_status_id')
                    ->where('rejected_statuses.approval_status', 'rejected');
            })
            ->latest('approval_status_details.created_at')
            ->get();

        $rows = $approvals->map(function (ApprovalStatusDetails $approval): array {
            $record = $approval->main_status?->approvable;

            return [
                'approval' => $approval,
                'requestType' => $record === null ? 'Request' : Str::headline(class_basename($record)),
                'employees' => $this->employeeNames($record),
                'requestDetails' => $this->requestSummary($record),
            ];
        });

        return view('requests.pending.index', [
            'approvals' => $rows,
            'canApprove' => TenantPermissions::userCan('PendingRequest', 'approve'),
            'canReject' => TenantPermissions::userCan('PendingRequest', 'reject'),
        ]);
    }

    public function show(Request $request): View
    {
        TenantPermissions::authorize('PendingRequest', 'read');
        $userId = (int) Auth::guard('tenant')->id();
        $approval = ApprovalStatusDetails::query()
            ->with([
                'main_status.approvable',
                'main_status.approval_flow',
                'main_status.approval_status_details.user',
                'main_status.approval_status_details.approval_flow_detail.users',
                'approval_flow_detail.users',
            ])
            ->whereHas('approval_flow_detail.users', fn ($query) => $query->whereKey($userId))
            ->findOrFail($request->route('detail'));

        $approvalStatus = $approval->main_status;
        $record = $approvalStatus?->approvable;
        abort_unless($approvalStatus !== null && $record !== null, 404);
        abort_unless(in_array($record::class, self::APPROVABLE_TYPES, true), 404);

        $relations = match ($record::class) {
            LeaveApplication::class => ['users', 'leave_type', 'leave_reason', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'],
            Coff::class => ['user', 'location', 'company', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'],
            ManualAttendanceApplication::class, ManualPunchApplication::class, WeekOffSwapApplication::class => ['Users', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'],
            OutDuty::class => ['user', 'location', 'company', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'],
            ShortLeaveApplication::class => ['user', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'],
            ShiftChangeApplication::class => [
                'Users' => $this->pendingEmployeeUsers(),
                'shifts',
                'shift_rotation',
                'approval_statuses.approval_flow',
                'approval_statuses.approval_status_details.user',
            ],
            WeekOffChangeApplication::class => ['Users', 'WeekOffChangeDetails', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'],
            default => [],
        };
        $record->loadMissing($relations);

        $employeeRelation = method_exists($record, 'Users')
            ? 'Users'
            : (method_exists($record, 'users') ? 'users' : 'user');
        $employees = method_exists($record, $employeeRelation)
            ? $record->{$employeeRelation}
            : collect();
        if ($employees !== null && ! $employees instanceof \Illuminate\Database\Eloquent\Collection) {
            $employees = collect([$employees]);
        }

        $requestFields = collect($record->getAttributes())
            ->reject(fn ($value, string $key): bool => $key === 'id'
                || str_ends_with($key, '_id')
                || in_array($key, ['created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'], true))
            ->mapWithKeys(function ($value, string $key) use ($record): array {
                $state = $record->getAttribute($key);
                if ($state instanceof \DateTimeInterface) {
                    $state = $state->format('Y-m-d H:i:s');
                } elseif (is_bool($state)) {
                    $state = $state ? 'Yes' : 'No';
                } elseif (is_array($state)) {
                    $state = implode(', ', $state);
                } elseif ($state === null || $state === '') {
                    $state = '—';
                }

                return [Str::headline($key) => (string) $state];
            });

        $relatedDetails = $this->relatedDetails($record);
        $weekOffDetails = $record instanceof WeekOffChangeApplication
            ? $this->weekOffDetails($record)
            : [];
        $isActionable = $this->isActionable($approval, $userId);

        return view('requests.pending.show', [
            'approval' => $approval,
            'approvalStatus' => $approvalStatus,
            'record' => $record,
            'employees' => $employees,
            'requestFields' => $requestFields,
            'relatedDetails' => $relatedDetails,
            'weekOffDetails' => $weekOffDetails,
            'isActionable' => $isActionable,
        ]);
    }

    public function decide(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => [
                Rule::requiredIf($request->input('decision') === 'rejected'),
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        if ($validated['decision'] === 'rejected' && trim((string) $validated['rejection_reason']) === '') {
            throw ValidationException::withMessages([
                'rejection_reason' => 'A rejection reason is required.',
            ]);
        }

        TenantPermissions::authorize('PendingRequest', $validated['decision'] === 'approved' ? 'approve' : 'reject');

        $userId = (int) Auth::guard('tenant')->id();
        $approval = ApprovalStatusDetails::query()
            ->with('main_status.approvable')
            ->whereHas('approval_flow_detail.users', fn ($query) => $query->whereKey($userId))
            ->findOrFail($request->route('detail'));
        if (! $this->isActionable($approval, $userId)) {
            throw ValidationException::withMessages([
                'decision' => 'This approval level is no longer active.',
            ]);
        }

        ApprovalProcessor::processDecision(
            (int) $request->route('detail'),
            $userId,
            $validated['decision'],
            $validated['rejection_reason'] ?? null,
        );

        return redirect()
            ->route('tenant.requests.pending.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Your approval response has been processed successfully.');
    }

    public function bulkDecide(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'approval_details' => ['required', 'array', 'min:1', 'max:500'],
            'approval_details.*' => ['required', 'integer', 'distinct'],
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => [
                Rule::requiredIf($request->input('decision') === 'rejected'),
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        if ($validated['decision'] === 'rejected' && trim((string) $validated['rejection_reason']) === '') {
            throw ValidationException::withMessages([
                'rejection_reason' => 'A rejection reason is required.',
            ]);
        }

        TenantPermissions::authorize('PendingRequest', $validated['decision'] === 'approved' ? 'approve' : 'reject');

        $userId = (int) Auth::guard('tenant')->id();
        $approvalIds = array_map('intval', $validated['approval_details']);
        $approvals = ApprovalStatusDetails::query()
            ->with('main_status.approvable')
            ->whereIn('id', $approvalIds)
            ->whereHas('approval_flow_detail.users', fn ($query) => $query->whereKey($userId))
            ->get()
            ->keyBy('id');

        if ($approvals->count() !== count($approvalIds)) {
            throw ValidationException::withMessages([
                'approval_details' => 'One or more selected requests are not available for your approval.',
            ]);
        }

        foreach ($approvalIds as $approvalId) {
            $approval = $approvals->get($approvalId);
            if ($approval === null || ! $this->isActionable($approval, $userId)) {
                throw ValidationException::withMessages([
                    'approval_details' => 'One or more selected requests are no longer actionable.',
                ]);
            }
        }

        foreach ($approvalIds as $approvalId) {
            ApprovalProcessor::processDecision(
                $approvalId,
                $userId,
                $validated['decision'],
                $validated['rejection_reason'] ?? null,
            );
        }

        return redirect()
            ->route('tenant.requests.pending.index', ['tenant' => $request->route('tenant')])
            ->with('success', count($approvalIds).' request decisions have been processed successfully.');
    }

    private function relatedDetails(object $record): array
    {
        $details = [];

        foreach ([
            'leave_type' => 'Leave Type',
            'leave_reason' => 'Leave Reason',
            'shift_rotation' => 'Shift Rotation',
            'location' => 'Location',
            'company' => 'Company',
        ] as $relation => $label) {
            if ($record->relationLoaded($relation) && $record->getRelation($relation) !== null) {
                $related = $record->getRelation($relation);
                $details[$label] = $related->name ?? $related->leave_reason ?? $related->code ?? (string) $related->getKey();
            }
        }

        if ($record->relationLoaded('shifts')) {
            $details['Shifts'] = $record->getRelation('shifts')->pluck('name')->filter()->implode(', ') ?: '—';
        }

        return $details;
    }

    /**
     * @return array<int, array{day: string, type: string, weeks: string}>
     */
    private function weekOffDetails(WeekOffChangeApplication $record): array
    {
        $weekDays = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
        $weekLabels = [
            'first_week' => '1st Week',
            'second_week' => '2nd Week',
            'third_week' => '3rd Week',
            'fourth_week' => '4th Week',
            'fifth_week' => '5th Week',
        ];

        return $record->getRelation('WeekOffChangeDetails')
            ->map(static function ($detail) use ($weekDays, $weekLabels): array {
                $type = $detail->wo_type;
                $typeValue = $type instanceof WOTypeEnumn ? $type->value : (int) $type;

                return [
                    'day' => $weekDays[$detail->week_days] ?? '—',
                    'type' => match ($typeValue) {
                        WOTypeEnumn::FullDay->value => 'Full Day',
                        WOTypeEnumn::FirstHalf->value => 'First Half',
                        WOTypeEnumn::SecondHalf->value => 'Second Half',
                        default => '—',
                    },
                    'weeks' => collect($weekLabels)
                        ->filter(static fn (string $label, string $field): bool => (bool) $detail->$field)
                        ->values()
                        ->implode(', ') ?: '—',
                ];
            })
            ->all();
    }

    private function pendingEmployeeUsers(): \Closure
    {
        return static fn ($relation) => $relation->withoutGlobalScope(DataPolicyFilter::class);
    }

    private function employeeNames(?object $record): string
    {
        if ($record === null) {
            return '—';
        }

        $employees = method_exists($record, 'Users')
            ? $record->Users
            : (method_exists($record, 'users')
                ? $record->users
                : (method_exists($record, 'user') ? $record->user : null));

        if ($employees instanceof EloquentCollection) {
            return $employees->map(fn ($user): string => $user->name . ($user->code ? ' ('.$user->code.')' : ''))
                ->join(', ') ?: '—';
        }

        return $employees?->name
            ? $employees->name . ($employees->code ? ' ('.$employees->code.')' : '')
            : '—';
    }

    private function requestSummary(?object $record): string
    {
        if ($record === null) {
            return '—';
        }

        $summary = match (true) {
            $record instanceof LeaveApplication => collect([
                $record->leave_type?->code,
                $record->from_date?->format('Y-m-d'),
                $record->to_date?->format('Y-m-d'),
                $record->leave_count === null ? null : $record->leave_count.' days',
            ]),
            $record instanceof ManualAttendanceApplication => collect([
                'In: '.$record->in_time?->format('Y-m-d H:i'),
                'Out: '.$record->out_time?->format('Y-m-d H:i'),
            ]),
            $record instanceof ManualPunchApplication => collect([
                \App\Models\Tenant\ManualPunch::punchTypeLabel($record->punch_type),
                $record->punch_date?->format('Y-m-d'),
                $record->punch_time?->format('H:i:s'),
            ]),
            $record instanceof ShiftChangeApplication => collect([
                Str::headline((string) $record->shift_type),
                $record->from_date?->format('Y-m-d'),
                $record->is_forever ? 'No end date' : $record->to_date?->format('Y-m-d'),
            ]),
            $record instanceof WeekOffChangeApplication => collect([
                $record->from_date?->format('Y-m-d'),
                $record->is_forever ? 'No end date' : $record->to_date?->format('Y-m-d'),
            ]),
            $record instanceof WeekOffSwapApplication => collect([
                'Week: '.$record->week_date?->format('Y-m-d'),
                'Swap: '.$record->swap_date?->format('Y-m-d'),
            ]),
            $record instanceof ShortLeaveApplication => collect([
                $record->date?->format('Y-m-d'),
                $record->from_time?->format('H:i'),
                $record->to_time?->format('H:i'),
            ]),
            $record instanceof Coff => collect([
                'Against: '.$record->coff_against_date?->format('Y-m-d'),
                $record->from_date?->format('Y-m-d'),
                $record->to_date?->format('Y-m-d'),
            ]),
            $record instanceof OutDuty => collect([
                $record->from_date_time?->format('Y-m-d H:i'),
                $record->to_date_time?->format('Y-m-d H:i'),
            ]),
            default => collect(),
        };

        return $summary->filter()->implode(' · ') ?: '—';
    }

    private function isActionable(ApprovalStatusDetails $approval, int $userId): bool
    {
        $approvalStatus = $approval->main_status;
        $record = $approvalStatus?->approvable;

        return $approval->approval_status === 'pending'
            && $approvalStatus?->approval_status === 'pending'
            && $record !== null
            && $approval->approval_flow_detail()->whereHas('users', fn ($query) => $query->whereKey($userId))->exists()
            && ! $approvalStatus->approval_status_details()
                ->where('level', '<', $approval->level)
                ->where('approval_status', '!=', 'approved')
                ->exists()
            && ! $record->approval_statuses()
                ->where('approval_status', 'rejected')
                ->exists();
    }
}
