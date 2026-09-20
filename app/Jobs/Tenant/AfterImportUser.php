<?php

namespace App\Jobs\Tenant;

use App\Support\ActivityLogger;
use App\Dms\EmpDms;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\DmsSetting;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\Location;
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
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Jenssegers\Agent\Agent;

class AfterImportUser implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $users;

    protected $is_created = false;

    /**
     * Create a new job instance.
     */
    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $authUser = User::where('id', '1')->first();

        foreach ($this->users as $user_code) {
            $this->is_created = false;
            $user = User::where('code', $user_code)->first();
            if (! $user) {
                continue;
            }

            $joinDate = GeneralConfiguration::where('key', 'first_time_muster_date')->first() ? Carbon::parse(GeneralConfiguration::where('key', 'first_time_muster_date')->first()->value) : Carbon::parse($user->join_date);
            $alreadyShiftMuster = ShiftMuster::where('user_id', $user->id)->where('date', '>=', Carbon::parse($joinDate))->first();
            $alreadyWeekOffMuster = WeekOffMuster::where('user_id', $user->id)->where('date', '>=', Carbon::parse($joinDate))->first();

            if ($alreadyShiftMuster || $alreadyWeekOffMuster) {
                continue;
            } else {
                $this->createShiftMuster($user, $authUser);
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

            UserStatusMuster::dispatch($user, $joinDate)->onQueue('processing');
            // (new UserDms)->updateUsersToDms($user);
        }
    }

    public function createShiftMuster($user, $authUser)
    {
        $joinDate = GeneralConfiguration::where('key', 'first_time_muster_date')->first() ? Carbon::parse(GeneralConfiguration::where('key', 'first_time_muster_date')->first()->value) : Carbon::parse($user->join_date);
        $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
        if ($financial_year) {
            $endDate = $financial_year->end_date;
        } else {
            $endDate = $joinDate?->copy()->endOfYear();
        }

        // For create shift change
        if (GeneralConfiguration::where('key', 'create_new_shift_change_in_user_edit') && GeneralConfiguration::where('key', 'create_new_shift_change_in_user_edit')->first()->value == 1) {
            $shift_change = ShiftChange::create(
                [
                    'from_date' => $joinDate->copy(),
                    'shift_rotation_id' => $user?->shift_rotation_id,
                    'to_date' => $endDate->copy(),
                    'shift_type' => $user?->shift_type,
                    'is_forever' => true,
                    'location_id' => $user?->location_id,
                    'company_id' => $user?->company_id,
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
                        'location_id' => $user?->location_id,
                        'company_id' => $user?->company_id,
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
                    'locations' => is_array($shift_change->location_id) ? collect(Location::whereIn('id', $shift_change->location_id)->get())->pluck('name')->implode(', ') : ($shift_change->location ? $shift_change->location->name : ''),
                    'companies' => is_array($shift_change->company_id) ? collect(Company::whereIn('id', $shift_change->company_id)->get())->pluck('name')->implode(', ') : ($shift_change->company ? $shift_change->company->name : ''),
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
                if ($shift_change->areas()?->count() > 0) {
                    $shift_change_old_record['areas'] = $shift_change->areas()?->pluck('name')->implode(',');
                }
                if ($shift_change->Users()?->count() > 0) {
                    $shift_change_old_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
                }

                $shift_change->shift_type = $user?->shift_type;
                $shift_change->shift_rotation_id = $user?->shift_rotation_id;
                $shift_change->location_id = $user?->location_id;
                $shift_change->company_id = $user?->company_id;
                $shift_change->save();
                $this->is_created = false;
            }
        }

        if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
            $shift_change->shifts()->sync($user->shifts()->pluck('shift_id'));
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
        $shift_change->areas()->sync($user?->areas()->pluck('area_id')->toArray());

        $shift_change->departments()->sync(is_array($user->department_id) ? $user->department_id : [$user->department_id]);
        $shift_change->sub_departments()->sync(is_array($user->sub_department_id) ? $user->sub_department_id : [$user->sub_department_id]);
        $shift_change->categories()->sync(is_array($user->category_id) ? $user->category_id : [$user->category_id]);
        $shift_change->sub_categories()->sync(is_array($user->sub_category_id) ? $user->sub_category_id : [$user->sub_category_id]);
        $shift_change->designations()->sync(is_array($user->designation_id) ? $user->designation_id : [$user->designation_id]);

        // $shift_change->departments()->sync(Arr::wrap($user->department_id));
        // $shift_change->sub_departments()->sync(Arr::wrap($user->sub_department_id));
        // $shift_change->categories()->sync(Arr::wrap($user->category_id));
        // $shift_change->sub_categories()->sync(Arr::wrap($user->sub_category_id));
        // $shift_change->designations()->sync(Arr::wrap($user->designation_id));

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
                'locations' => is_array($shift_change->location_id) ? collect(Location::whereIn('id', $shift_change->location_id)->get())->pluck('name')->implode(', ') : ($shift_change->location ? $shift_change->location->name : ''),
                'companies' => is_array($shift_change->company_id) ? collect(Company::whereIn('id', $shift_change->company_id)->get())->pluck('name')->implode(', ') : ($shift_change->company ? $shift_change->company->name : ''),
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
            if ($shift_change->areas()?->count() > 0) {
                $shift_change_new_record['areas'] = $shift_change->areas()?->pluck('name')->implode(',');
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
                'locations' => is_array($shift_change->location_id) ? collect(Location::whereIn('id', $shift_change->location_id)->get())->pluck('name')->implode(', ') : ($shift_change->location ? $shift_change->location->name : ''),
                'companies' => is_array($shift_change->company_id) ? collect(Company::whereIn('id', $shift_change->company_id)->get())->pluck('name')->implode(', ') : ($shift_change->company ? $shift_change->company->name : ''),
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
            if ($shift_change->areas()?->count() > 0) {
                $shift_change_new_record['areas'] = $shift_change->areas()?->pluck('name')->implode(',');
            }
            if ($shift_change->Users()?->count() > 0) {
                $shift_change_new_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
            }
            ActivityLogger::log($authUser, $shift_change, $shift_change_old_record, $shift_change_new_record, 'updated');
        }

        $from_date = $shift_change->from_date;
        $fromDate = $from_date->copy();
        if ($shift_change->is_forever) {
            $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($financial_year) {
                $toDate = $financial_year->end_date;
            } else {
                $toDate = $from_date?->copy()->endOfYear();
            }
        } else {
            $toDate = $shift_change->to_date;
        }

        while ($fromDate <= $toDate) {
            if ($shift_change->shift_type == 'auto') {
                ShiftMuster::updateOrCreate(
                    [
                        'date' => $fromDate,
                        'user_id' => $user->id,
                        'is_locked' => false,
                    ],
                    [
                        'is_auto' => $shift_change->shift_type == 'auto' ? true : false,
                        'shift' => $shift_change->shifts()->pluck('shift_id')->map(function ($id) {
                            return (int) $id;
                        })->toArray(),
                        'shiftchangeable_type' => $shift_change::class,
                        'shiftchangeable_id' => $shift_change->id,
                    ]
                );
                // $fromDate->addDay();
            } elseif ($shift_change->shift_type == 'rotational') {
                // $user_rotational_shift = $this->shift_change->shift_rotation_id;
                ShiftMuster::updateOrCreate(
                    [
                        'date' => $fromDate,
                        'user_id' => $user->id,
                        'is_locked' => false,
                    ],
                    [
                        'shift' => RotationMuster::where('rotation_id', $shift_change->shift_rotation_id)->where('date', $fromDate)->first()->shift_id,
                        'shiftchangeable_type' => $shift_change::class,
                        'shiftchangeable_id' => $shift_change->id,
                    ]
                );
                // $fromDate->addDay();
            } else {
                ShiftMuster::updateOrCreate(
                    [
                        'date' => $fromDate,
                        'user_id' => $user->id,
                        'is_locked' => false,
                    ],
                    [
                        'shift' => $shift_change->shifts()->pluck('shift_id')->map(function ($id) {
                            return (int) $id;
                        })->toArray(),
                        'shiftchangeable_type' => $shift_change::class,
                        'shiftchangeable_id' => $shift_change->id,
                    ]
                );
            }

            $user->shift_status = true;
            $user->save();

            $fromDate->addDay();
        }
    }

    public function createWeekOffMuster($user)
    {
        $currentDate = GeneralConfiguration::where('key', 'first_time_muster_date')->first() ? GeneralConfiguration::where('key', 'first_time_muster_date')->first()->value : Carbon::parse($user->join_date);
        $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
        if ($financial_year) {
            $toDate = $financial_year->end_date;
        } else {
            $toDate = $currentDate?->copy()->endOfYear();
        }

        while ($currentDate->lte($toDate)) {
            WeekOffMuster::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $currentDate,
                ],
                [
                    'is_locked' => 0,
                    'wo_type' => 0,
                ]
            );
            $currentDate->addDay();
        }

        $fromDate = GeneralConfiguration::where('key', 'first_time_muster_date')->first() ? Carbon::parse(GeneralConfiguration::where('key', 'first_time_muster_date')->first()->value) : Carbon::parse($user->join_date);
        $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
        if ($financial_year) {
            $toDate = $financial_year->end_date;
        } else {
            $toDate = $fromDate?->copy()->endOfYear();
        }

        if (GeneralConfiguration::where('key', 'create_new_week_of_change_in_user_edit') && GeneralConfiguration::where('key', 'create_new_week_of_change_in_user_edit')->first()->value == 1) {
            $week_off_change = WeekOffChange::create([
                'from_date' => $fromDate->copy(),
                'is_forever' => true,
                'to_date' => $toDate->copy(),
                'location_id' => $user?->location_id,
                'company_id' => $user?->company_id,
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
                    'location_id' => $user?->location_id,
                    'company_id' => $user?->company_id,
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
                $week_off_change->location_id = $user?->location_id;
                $week_off_change->company_id = $user?->company_id;
                $week_off_change->save();

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

        $week_off_change->departments()->sync(is_array($user->department_id) ? $user->department_id : [$user->department_id]);
        $week_off_change->sub_departments()->sync(is_array($user->sub_department_id) ? $user->sub_department_id : [$user->sub_department_id]);
        $week_off_change->categories()->sync(is_array($user->category_id) ? $user->category_id : [$user->category_id]);
        $week_off_change->sub_categories()->sync(is_array($user->sub_category_id) ? $user->sub_category_id : [$user->sub_category_id]);
        $week_off_change->designations()->sync(is_array($user->designation_id) ? $user->designation_id : [$user->designation_id]);

        // $week_off_change->departments()->sync(Arr::wrap($user->department_id));
        // $week_off_change->sub_departments()->sync(Arr::wrap($user->sub_department_id));
        // $week_off_change->categories()->sync(Arr::wrap($user->category_id));
        // $week_off_change->sub_categories()->sync(Arr::wrap($user->sub_category_id));
        // $week_off_change->designations()->sync(Arr::wrap($user->designation_id));

        $user->week_off_change_id = $week_off_change->id;
        $user->save();

        $from_date = $week_off_change->from_date->copy();
        if ($week_off_change->is_forever) {
            $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($financial_year) {
                $to_date = $financial_year->end_date;
            } else {
                $to_date = $from_date?->copy()->endOfYear();
            }
        } else {
            $to_date = $week_off_change->to_date->copy();
        }

        $users = $week_off_change->Users()->pluck('user_id')->toArray();
        foreach ($users as $user_id) {
            $fromDate = $from_date->copy();
            while ($fromDate->lte($to_date)) {
                $weekOffMuster = WeekOffMuster::where('date', $fromDate)
                    ->where('user_id', $user_id)
                    ->where('is_locked', '!=', 1)
                    ->first();
                if ($weekOffMuster != null) {
                    $weekOffMuster->weekoffable_type = $week_off_change::class;
                    $weekOffMuster->weekoffable_id = $week_off_change->id;
                    $weekOffMuster->wo_type = 0;
                    $weekOffMuster->save();
                }
                $fromDate->addDay();
            }
        }

        if ($week_off_change->is_forever) {
            $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($financial_year) {
                $toDate = $financial_year->end_date;
            } else {
                $toDate = $week_off_change->from_date->copy()->endOfYear();
            }
        } else {
            $toDate = $week_off_change->to_date;
        }

        $currentDate = $week_off_change->from_date->copy();
        while ($currentDate->lte($toDate)) {
            $this->updateMuster($currentDate, $toDate, $user, $week_off_change);
            $currentDate->addDays(6);
        }
    }

    public function updateMuster($date, $toDate, $user, $week_off_change)
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

            $weekOffUpdate = WeekOffMuster::where('date', $wo)
                ->where('user_id', $user->id)
                ->where('is_locked', '!=', 1)
                // ->where('weekoffable_type', $this->weekOffChange::class)
                // ->where('weekoffable_id', $this->weekOffChange->id)
                ->first();
            if ($weekOffUpdate != null) {
                $weekOffUpdate->wo_type = $detail->wo_type;
                $weekOffUpdate->weekoffable_type = $week_off_change::class;
                $weekOffUpdate->weekoffable_id = $week_off_change->id;
                $weekOffUpdate->save();
            }
        }
    }
}
