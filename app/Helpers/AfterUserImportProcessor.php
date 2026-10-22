<?php

namespace App\Helpers;

use App\Jobs\Tenant\UserStatusMuster;
use App\Support\ActivityLogger;
use App\Dms\EmpDms;
use App\Models\Tenant\Category;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\DmsSetting;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\RotationMuster;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffChangeDetail;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Jenssegers\Agent\Agent;

class AfterUserImportProcessor
{
    public $users;

    public bool $options = false;

    private bool $dispatchStatusMuster = true;

    protected $is_created = false;

    private ?array $configurationValues = null;

    private bool $financialYearLoaded = false;

    private ?Carbon $financialYearEndDate = null;

    public static function processAfterImportUser($record): void
    {
        LegacyAfterUserImportProcessor::processAfterImportUser($record);
    }

    public function __construct($users)
    {
        $this->users = $users;
    }

    public static function processAfterImportUsers($users, bool $options = false, bool $dispatchStatusMuster = true): void
    {
        $processor = new self(Arr::wrap($users));
        $processor->options = $options;
        $processor->dispatchStatusMuster = $dispatchStatusMuster;
        $processor->handle();
    }

    public static function processAfterImportUsersSynchronously($users): void
    {
        self::processAfterImportUsers($users, false, false);
    }

    public function handle(): void
    {
        $authUser = User::where('id', '1')->first();

        foreach ($this->users as $user_code) {
            $this->is_created = false;
            $user = User::where('code', $user_code)->first();
            if (! $user) {
                continue;
            }

            $joinDate = $this->getJoinDate($user);
            $alreadyShiftMuster = ShiftMuster::where('user_id', $user->id)->where('date', '>=', Carbon::parse($joinDate))->first();
            $alreadyWeekOffMuster = WeekOffMuster::where('user_id', $user->id)->where('date', '>=', Carbon::parse($joinDate))->first();

            if ($alreadyShiftMuster && $alreadyWeekOffMuster) {
                if ($user->week_off_change_id) {
                    $weekOffChange = WeekOffChange::whereKey($user->week_off_change_id)->first();
                    if ($weekOffChange) {
                        $this->syncWeekOffMustersToChange($user, $weekOffChange);
                    }
                }

                $this->processUserStatusMuster($user, $joinDate);

                continue;
            }

            if (! $alreadyShiftMuster) {
                $this->createShiftMuster($user, $authUser);
            }

            if (! $alreadyWeekOffMuster) {
                $this->createWeekOffMuster($user);
            }

            // Add user to dms
            if ($this->options ?? false) {
                (new EmpDms)->updateEmployeeToDms($user);
            } else {
                if (DmsSetting::isDmsEmployeeSyncRequires() === true) {
                    (new EmpDms)->uploadNewEmployeeToDms($user);
                }
            }

            $this->processUserStatusMuster($user, $joinDate);
            // (new UserDms)->updateUsersToDms($user);
        }
    }

    public function createShiftMuster($user, $authUser)
    {
        $joinDate = $this->getJoinDate($user);
        $endDate = $this->getFinancialYearEndDate($joinDate);

        // For create shift change
        if ((int) $this->getConfigurationValue('create_new_shift_change_in_user_edit') === 1) {
            $shift_change = ShiftChange::create(
                [
                    'from_date' => $joinDate->copy(),
                    'shift_rotation_id' => $user?->shift_rotation_id,
                    'to_date' => $endDate->copy(),
                    'shift_type' => $user?->shift_type,
                    'is_forever' => true,
                ]
            );
            $this->is_created = true;
        } else {
            $shift_change = ShiftChange::where('id', $user?->shift_change_id)->first();
            if (! $shift_change) {
                $shift_change = ShiftChange::create(
                    [
                        'from_date' => $joinDate->copy(),
                        'shift_rotation_id' => $user?->shift_rotation_id,
                        'to_date' => $endDate->copy(),
                        'shift_type' => $user?->shift_type,
                        'is_forever' => true,
                    ]
                );
                $this->is_created = true;
            } else {
                $shift_change_old_record = [
                    'ip' => '-',
                    'browser' => '-',
                    'from_date' => $shift_change->from_date,
                    'is_forever' => $shift_change->is_forever,
                    'to_date' => $shift_change->to_date,
                    'reason' => $shift_change->reason,
                    'shift_type' => $shift_change->shift_type,
                    'departments' => is_array($shift_change->department_id) ? collect(Department::whereIn('id', $shift_change->department_id)->get())->pluck('name')->implode(', ') : ($shift_change->department ? $shift_change->department->name : ''),
                    'sub_departments' => is_array($shift_change->sub_department_id) ? collect(SubDepartment::whereIn('id', $shift_change->sub_department_id)->get())->pluck('name')->implode(', ') : ($shift_change->sub_department ? $shift_change->sub_department->name : ''),
                    'categories' => is_array($shift_change->category_id) ? collect(Category::whereIn('id', $shift_change->category_id)->get())->pluck('name')->implode(', ') : ($shift_change->category ? $shift_change->category->name : ''),
                    'sub_categories' => is_array($shift_change->sub_category_id) ? collect(SubCategory::whereIn('id', $shift_change->sub_category_id)->get())->pluck('name')->implode(', ') : ($shift_change->sub_category ? $shift_change->category->name : ''),
                    'designations' => is_array($shift_change->designation_id) ? collect(Designation::whereIn('id', $shift_change->designation_id)->get())->pluck('name')->implode(', ') : ($shift_change->designation ? $shift_change->designation->name : ''),
                ];
                if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
                    $shift_change_old_record['shifts'] = $shift_change->shifts()?->pluck('name')->implode(',');
                } else {
                    $shift_change_old_record['shift_rotation'] = $shift_change->shift_rotation?->code;
                }
                if ($shift_change->Users()?->count() > 0) {
                    $shift_change_old_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
                }

                $shift_change->shift_type = $user?->shift_type;
                $shift_change->shift_rotation_id = $user?->shift_rotation_id;
                $shift_change->save();
                $this->is_created = false;
            }
        }

        $shiftIds = null;
        if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
            $shiftIds = $user->shifts()->pluck('shift_id')->map(fn ($id) => (int) $id)->all();
            $shift_change->shifts()->sync($shiftIds);
        } elseif ($shift_change->shift_type != 'rotational') {
            $shiftIds = $shift_change->shifts()->pluck('shift_id')->map(fn ($id) => (int) $id)->all();
        }

        $user_old_record = [
            'ip' => '-',
            'browser' => '-',
            'shift_change_id' => $user->shift_change_id ? $user->shift_change_id : '-',
        ];

        $user->shift_change_id = $shift_change->id;
        $user->save();

        $agent = new Agent;
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $os = $agent->platform();

        $user_new_record = [
            'ip' => request()->ip(),
            'browser' => $os . ' > ' . $browser,
            'shift_change_id' => $user->shift_change_id ? $user->shift_change_id : '-',
        ];
        ActivityLogger::log($authUser, $user, $user_old_record, $user_new_record, 'updated');

        $shift_change->Users()->sync($user->id);

        if ($this->is_created == true) {
            $shift_change_old_record = [];

            $agent = new Agent;
            $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            $os = $agent->platform();

            $shift_change_new_record = [
                'ip' => request()->ip(),
                'browser' => $os . ' > ' . $browser,
                'from_date' => $shift_change->from_date,
                'is_forever' => $shift_change->is_forever,
                'to_date' => $shift_change->to_date,
                'reason' => $shift_change->reason,
                'shift_type' => $shift_change->shift_type,
                'departments' => is_array($shift_change->department_id) ? collect(Department::whereIn('id', $shift_change->department_id)->get())->pluck('name')->implode(', ') : ($shift_change->department ? $shift_change->department->name : ''),
                'sub_departments' => is_array($shift_change->sub_department_id) ? collect(SubDepartment::whereIn('id', $shift_change->sub_department_id)->get())->pluck('name')->implode(', ') : ($shift_change->sub_department ? $shift_change->sub_department->name : ''),
                'categories' => is_array($shift_change->category_id) ? collect(Category::whereIn('id', $shift_change->category_id)->get())->pluck('name')->implode(', ') : ($shift_change->category ? $shift_change->category->name : ''),
                'sub_categories' => is_array($shift_change->sub_category_id) ? collect(SubCategory::whereIn('id', $shift_change->sub_category_id)->get())->pluck('name')->implode(', ') : ($shift_change->sub_category ? $shift_change->category->name : ''),
                'designations' => is_array($shift_change->designation_id) ? collect(Designation::whereIn('id', $shift_change->designation_id)->get())->pluck('name')->implode(', ') : ($shift_change->designation ? $shift_change->designation->name : ''),
            ];
            if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
                $shift_change_new_record['shifts'] = $shift_change->shifts()?->pluck('name')->implode(',');
            } else {
                $shift_change_new_record['shift_rotation'] = $shift_change->shift_rotation?->code;
            }
            if ($shift_change->Users()?->count() > 0) {
                $shift_change_new_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
            }
            ActivityLogger::log($authUser, $shift_change, $shift_change_old_record, $shift_change_new_record, 'created');
        } else {
            $agent = new Agent;
            $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            $os = $agent->platform();

            $shift_change_new_record = [
                'ip' => request()->ip(),
                'browser' => $os . ' > ' . $browser,
                'from_date' => $shift_change->from_date,
                'is_forever' => $shift_change->is_forever,
                'to_date' => $shift_change->to_date,
                'reason' => $shift_change->reason,
                'shift_type' => $shift_change->shift_type,
                'departments' => is_array($shift_change->department_id) ? collect(Department::whereIn('id', $shift_change->department_id)->get())->pluck('name')->implode(', ') : ($shift_change->department ? $shift_change->department->name : ''),
                'sub_departments' => is_array($shift_change->sub_department_id) ? collect(SubDepartment::whereIn('id', $shift_change->sub_department_id)->get())->pluck('name')->implode(', ') : ($shift_change->sub_department ? $shift_change->sub_department->name : ''),
                'categories' => is_array($shift_change->category_id) ? collect(Category::whereIn('id', $shift_change->category_id)->get())->pluck('name')->implode(', ') : ($shift_change->category ? $shift_change->category->name : ''),
                'sub_categories' => is_array($shift_change->sub_category_id) ? collect(SubCategory::whereIn('id', $shift_change->sub_category_id)->get())->pluck('name')->implode(', ') : ($shift_change->sub_category ? $shift_change->category->name : ''),
                'designations' => is_array($shift_change->designation_id) ? collect(Designation::whereIn('id', $shift_change->designation_id)->get())->pluck('name')->implode(', ') : ($shift_change->designation ? $shift_change->designation->name : ''),
            ];
            if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
                $shift_change_new_record['shifts'] = $shift_change->shifts()?->pluck('name')->implode(',');
            } else {
                $shift_change_new_record['shift_rotation'] = $shift_change->shift_rotation?->code;
            }
            if ($shift_change->Users()?->count() > 0) {
                $shift_change_new_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
            }
            ActivityLogger::log($authUser, $shift_change, $shift_change_old_record, $shift_change_new_record, 'updated');
        }

        $fromDate = $shift_change->from_date->copy();
        $toDate = $shift_change->is_forever
            ? $this->getFinancialYearEndDate($fromDate)
            : $shift_change->to_date->copy();
        $dateFrom = $fromDate->toDateString();
        $dateTo = $toDate->toDateString();
        $existingMusters = ShiftMuster::where('user_id', $user->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->where('is_locked', false)
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($muster) => $muster->date->toDateString())
            ->map(fn ($musters) => $musters->first());
        $rotationMusters = $shift_change->shift_type == 'rotational'
            ? RotationMuster::where('rotation_id', $shift_change->shift_rotation_id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('id')
                ->get()
                ->groupBy(fn ($muster) => $muster->date->toDateString())
                ->map(fn ($musters) => $musters->first())
            : collect();
        $shiftValues = $shiftIds;
        $insertRows = [];
        $updateRows = [];
        $updateColumns = ['shift', 'shiftchangeable_type', 'shiftchangeable_id', 'updated_at'];
        if ($shift_change->shift_type == 'auto') {
            $updateColumns[] = 'is_auto';
        }

        while ($fromDate->lte($toDate)) {
            $date = $fromDate->toDateString();
            if ($shift_change->shift_type == 'auto') {
                $values = [
                    'is_auto' => true,
                    'shift' => json_encode($shiftValues, JSON_THROW_ON_ERROR),
                    'shiftchangeable_type' => $shift_change::class,
                    'shiftchangeable_id' => $shift_change->id,
                ];
            } elseif ($shift_change->shift_type == 'rotational') {
                $rotationMuster = $rotationMusters->get($date);
                if (! $rotationMuster) {
                    throw new \RuntimeException("Missing rotation muster for rotation {$shift_change->shift_rotation_id} on {$date}.");
                }
                $values = [
                    'shift' => $rotationMuster->shift_id === null
                        ? null
                        : json_encode($rotationMuster->shift_id, JSON_THROW_ON_ERROR),
                    'shiftchangeable_type' => $shift_change::class,
                    'shiftchangeable_id' => $shift_change->id,
                ];
            } else {
                $values = [
                    'shift' => json_encode($shiftValues, JSON_THROW_ON_ERROR),
                    'shiftchangeable_type' => $shift_change::class,
                    'shiftchangeable_id' => $shift_change->id,
                ];
            }

            $existingMuster = $existingMusters->get($date);
            if ($existingMuster) {
                $updateRows[] = ['id' => $existingMuster->id] + $values + [
                    'updated_at' => now()->toDateTimeString(),
                ];
            } else {
                $insertValues = [
                    'date' => $date,
                    'user_id' => $user->id,
                    'is_locked' => false,
                ] + $values;
                if ($shift_change->shift_type != 'auto') {
                    $insertValues['is_auto'] = false;
                }
                $insertRows[] = $insertValues + [
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            }

            $fromDate->addDay();
        }

        $this->persistMusterRows(ShiftMuster::class, $insertRows, $updateRows, $updateColumns);
        $user->shift_status = true;
        $user->save();
    }

    public function createWeekOffMuster($user)
    {
        $currentDate = $this->getJoinDate($user);
        $toDate = $this->getFinancialYearEndDate($currentDate);
        $dateFrom = $currentDate->toDateString();
        $dateTo = $toDate->toDateString();
        $existingMusters = WeekOffMuster::where('user_id', $user->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($muster) => $muster->date->toDateString())
            ->map(fn ($musters) => $musters->first());
        $insertRows = [];
        $updateRows = [];

        while ($currentDate->lte($toDate)) {
            $date = $currentDate->toDateString();
            $existingMuster = $existingMusters->get($date);
            if ($existingMuster) {
                $updateRows[] = [
                    'id' => $existingMuster->id,
                    'is_locked' => false,
                    'wo_type' => 0,
                    'updated_at' => now()->toDateTimeString(),
                ];
            } else {
                $now = now()->toDateTimeString();
                $insertRows[] = [
                    'user_id' => $user->id,
                    'date' => $date,
                    'is_locked' => false,
                    'wo_type' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $currentDate->addDay();
        }

        $this->persistMusterRows(WeekOffMuster::class, $insertRows, $updateRows, ['is_locked', 'wo_type', 'updated_at']);
        $fromDate = $this->getJoinDate($user);

        if ((int) $this->getConfigurationValue('create_new_week_of_change_in_user_edit') === 1) {
            $week_off_change = WeekOffChange::create([
                'from_date' => $fromDate->copy(),
                'is_forever' => true,
                'to_date' => $toDate->copy(),
            ]);

            foreach ($user->user_week_offs as $user_week_off) {
                WeekOffChangeDetail::create([
                    'week_off_change_id' => $week_off_change->id,
                    'week_days' => $user_week_off->week_days,
                    'wo_type' => $user_week_off->wo_type,
                    'first_week' => $user_week_off->first_week,
                    'second_week' => $user_week_off->second_week,
                    'third_week' => $user_week_off->third_week,
                    'fourth_week' => $user_week_off->fourth_week,
                    'fifth_week' => $user_week_off->fifth_week,
                ]);
            }
        } else {
            $week_off_change = WeekOffChange::where('id', $user?->week_off_change_id)->first();
            if (! $week_off_change) {
                $week_off_change = WeekOffChange::create([
                    'from_date' => $fromDate->copy(),
                    'is_forever' => true,
                    'to_date' => $toDate->copy(),
                ]);

                foreach ($user->user_week_offs as $user_week_off) {
                    WeekOffChangeDetail::create([
                        'week_off_change_id' => $week_off_change->id,
                        'week_days' => $user_week_off->week_days,
                        'wo_type' => $user_week_off->wo_type,
                        'first_week' => $user_week_off->first_week,
                        'second_week' => $user_week_off->second_week,
                        'third_week' => $user_week_off->third_week,
                        'fourth_week' => $user_week_off->fourth_week,
                        'fifth_week' => $user_week_off->fifth_week,
                    ]);
                }
            } else {
                $week_off_change->WeekOffChangeDetails()->delete();
                foreach ($user->user_week_offs as $user_week_off) {
                    $week_off_change->WeekOffChangeDetails()->create([
                        'week_off_change_id' => $week_off_change->id,
                        'week_days' => $user_week_off->week_days,
                        'wo_type' => $user_week_off->wo_type,
                        'first_week' => $user_week_off->first_week,
                        'second_week' => $user_week_off->second_week,
                        'third_week' => $user_week_off->third_week,
                        'fourth_week' => $user_week_off->fourth_week,
                        'fifth_week' => $user_week_off->fifth_week,
                    ]);
                }
            }
        }

        $week_off_change->Users()->sync($user->id);
        $week_off_change->areas()->sync($user?->areas()->pluck('area_id')->toArray());

        $week_off_change->departments()->sync(array_values(array_filter(Arr::wrap($user->department_id))));
        $week_off_change->sub_departments()->sync(array_values(array_filter(Arr::wrap($user->sub_department_id))));
        $week_off_change->categories()->sync(array_values(array_filter(Arr::wrap($user->category_id))));
        $week_off_change->sub_categories()->sync(array_values(array_filter(Arr::wrap($user->sub_category_id))));
        $week_off_change->designations()->sync(array_values(array_filter(Arr::wrap($user->designation_id))));

        // $week_off_change->departments()->sync(Arr::wrap($user->department_id));
        // $week_off_change->sub_departments()->sync(Arr::wrap($user->sub_department_id));
        // $week_off_change->categories()->sync(Arr::wrap($user->category_id));
        // $week_off_change->sub_categories()->sync(Arr::wrap($user->sub_category_id));
        // $week_off_change->designations()->sync(Arr::wrap($user->designation_id));

        $user->week_off_change_id = $week_off_change->id;
        $user->save();

        $this->syncWeekOffMustersToChange($user, $week_off_change);
    }

    private function syncWeekOffMustersToChange(User $user, WeekOffChange $week_off_change): void
    {
        $from_date = $week_off_change->from_date->copy();
        $toDate = $week_off_change->is_forever
            ? $this->getFinancialYearEndDate($from_date)
            : $week_off_change->to_date->copy();
        $weekOffMusters = WeekOffMuster::where('user_id', $user->id)
            ->whereBetween('date', [$from_date->toDateString(), $toDate->toDateString()])
            ->where('is_locked', '!=', 1)
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($muster) => $muster->date->toDateString())
            ->map(fn ($musters) => $musters->first());

        foreach ($weekOffMusters as $weekOffMuster) {
            $weekOffMuster->weekoffable_type = $week_off_change::class;
            $weekOffMuster->weekoffable_id = $week_off_change->id;
            $weekOffMuster->wo_type = 0;
        }

        $currentDate = $week_off_change->from_date->copy();
        while ($currentDate->lte($toDate)) {
            $this->updateMuster($currentDate, $toDate, $user, $week_off_change, $weekOffMusters);
            $currentDate->addDays(6);
        }

        $updateRows = [];
        foreach ($weekOffMusters as $weekOffMuster) {
            $dirty = $weekOffMuster->getDirty();
            if ($dirty === []) {
                continue;
            }

            $updateRows[] = [
                'id' => $weekOffMuster->id,
                'weekoffable_type' => $weekOffMuster->weekoffable_type,
                'weekoffable_id' => $weekOffMuster->weekoffable_id,
                'wo_type' => $weekOffMuster->wo_type,
                'updated_at' => now()->toDateTimeString(),
            ];
        }
        $this->persistMusterRows(WeekOffMuster::class, [], $updateRows, [
            'weekoffable_type',
            'weekoffable_id',
            'wo_type',
            'updated_at',
        ]);
    }

    public function updateMuster($date, $toDate, $user, $week_off_change, $weekOffMusters = null)
    {
        foreach ($week_off_change->WeekOffChangeDetails as $detail) {
            $wd = $detail->week_days;
            if ($date->dayOfWeek == $wd) {
                $wo = $date;
            } else {
                $wo = $date->next($wd);
            }
            if ($wo->gt($toDate)) {
                continue; // skip if date is greater than to_date
            }
            $weekOfMonth = $wo->weekOfMonth;
            $valid = false;
            switch ($weekOfMonth) {
                case 1:
                    $valid = $detail->first_week;
                    break;
                case 2:
                    $valid = $detail->second_week;
                    break;
                case 3:
                    $valid = $detail->third_week;
                    break;
                case 4:
                    $valid = $detail->fourth_week;
                    break;
                case 5:
                    $valid = $detail->fifth_week;
                    break;
                default:
                    $valid = false;
                    break;
            }

            if (! $valid) {
                continue; // Skip this day
            }

            $weekOffUpdate = $weekOffMusters
                ? $weekOffMusters->get($wo->toDateString())
                : WeekOffMuster::where('date', $wo)
                    ->where('user_id', $user->id)
                    ->where('is_locked', '!=', 1)
                    ->first();
            if ($weekOffUpdate != null) {
                $weekOffUpdate->wo_type = $detail->wo_type;
                $weekOffUpdate->weekoffable_type = $week_off_change::class;
                $weekOffUpdate->weekoffable_id = $week_off_change->id;
                if ($weekOffMusters === null) {
                    $weekOffUpdate->save();
                }
            }
        }
    }

    private function processUserStatusMuster(User $user, Carbon $joinDate): void
    {
        if ($this->dispatchStatusMuster) {
            UserStatusMuster::dispatch($user, $joinDate)->onQueue('processing');

            return;
        }

        (new UserStatusMuster($user, $joinDate))->handle();
    }

    private function getConfigurationValue(string $key): ?string
    {
        if ($this->configurationValues === null) {
            $this->configurationValues = GeneralConfiguration::whereIn('key', [
                'first_time_muster_date',
                'create_new_shift_change_in_user_edit',
                'create_new_week_of_change_in_user_edit',
            ])->pluck('value', 'key')->all();
        }

        return $this->configurationValues[$key] ?? null;
    }

    private function getJoinDate(User $user): Carbon
    {
        $firstTimeMusterDate = $this->getConfigurationValue('first_time_muster_date');

        return $firstTimeMusterDate
            ? Carbon::parse($firstTimeMusterDate)
            : Carbon::parse($user->join_date);
    }

    private function getFinancialYearEndDate(Carbon $startDate): Carbon
    {
        if (! $this->financialYearLoaded) {
            $financialYear = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            $this->financialYearEndDate = $financialYear?->end_date;
            $this->financialYearLoaded = true;
        }

        return $this->financialYearEndDate?->copy() ?? $startDate->copy()->endOfYear();
    }

    private function persistMusterRows(string $modelClass, array $insertRows, array $updateRows, array $updateColumns): void
    {
        foreach (array_chunk($insertRows, 100) as $rows) {
            $modelClass::insert($rows);
        }

        foreach (array_chunk($updateRows, 100) as $rows) {
            foreach ($rows as $row) {
                $modelClass::query()
                    ->whereKey($row['id'])
                    ->update(Arr::only($row, $updateColumns));
            }
        }
    }
}
