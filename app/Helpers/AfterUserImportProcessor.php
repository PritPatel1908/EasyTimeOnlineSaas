<?php

namespace App\Helpers;

use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\User;
use App\Models\Tenant\UserShift;
use App\Models\Tenant\UserWeekOff;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffChangeDetail;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;

class AfterUserImportProcessor
{
    public static function processAfterImportUser($record)
    {
        // get user
        $user = User::where('code', $record['code'])->first();

        // create user_shifts
        foreach ($record->data['shift_code'] as $shift) {
            $shift = Shift::where('code', $shift)->first();
            $user_shift = UserShift::create([
                'user_id' => $user->id,
                'shift_id' => $shift->id,
            ]);
        }

        $joinDate = $user->join_date;
        $endDate = now()->endOfYear();

        // Create shift change
        $shift_change = ShiftChange::create([
            'shift_type' => $user->shift_type,
            'shift_rotation_id' => $user->shift_rotation_id,
            'from_date' => $user->join_date,
            'is_forever' => true,
            'to_date' => $endDate,
        ]);

        // assign shift_change_shifts
        if ($user->shifts) {
            $user_shifts = $user->shifts;
            $shift_change->shifts()->sync($user_shifts->pluck('id'));
        }

        // Assign shift_change_user
        $shift_change->Users()->sync($user->id);

        // Create Shift muster
        while ($joinDate <= $endDate) {
            $shift_muster = ShiftMuster::create([
                'date' => $joinDate,
                'user_id' => $user->id,
                'is_auto' => $shift_change->shift_type == 'auto' ? true : false,
                'is_fixed' => $shift_change->shift_type == 'fixed' ? true : false,
                'is_rotational' => $shift_change->shift_type == 'rotational' ? true : false,
                'shift_change_id' => $shift_change->id,
            ]);

            if ($shift_change->shift_type == 'fixed') {
                $shift_muster->shift = $shift_change->shift_id;
            }

            if ($shift_change->shift_type == 'rotational') {
                $shift_muster->shift = $shift_change->shift_rotation_id;
            }
            $joinDate->addDay();
        }

        // Create user_week_offs
        foreach ($record->data['week_days'] as $index => $week_day) {
            $user_week_off = UserWeekOff::create([
                'user_id' => $user->id,
                'week_days' => $week_day,
                'wo_type' => $record->wo_type,
                'first_week' => $record->data['first_week'][$index],
                'second_week' => $record->data['second_week'][$index],
                'third_week' => $record->data['third_week'][$index],
                'fourth_week' => $record->data['fourth_week'][$index],
                'fifth_week' => $record->data['fifth_week'][$index],
            ]);
        }

        // For create week change and muster
        $week_days = [];
        foreach ($user->user_week_offs as $week) {
            $week_days[] = $week->week_days;
        }

        $fromDate = Carbon::parse($user->join_date);
        $toDate = now()->endOfYear();

        foreach ($week_days as $week_day) {
            if (! $fromDate->is($week_day)) {
                $nearestWeekOffDay = $fromDate->next($week_day);
            } else {
                $nearestWeekOffDay = $fromDate;
            }

            if (! $toDate->is($week_day)) {
                $nearestEndYeayOffDay = $toDate->endOfYear()->previous($week_day);
            } else {
                $nearestEndYeayOffDay = $toDate;
            }
        }

        // $nearestWeekOffDay = Carbon::parse($user->join_date)->next($week_day);
        // $nearestEndYeayOffDay = Carbon::parse($user->join_date)->endOfYear()->previous($week_day)->modify('+1 day');
        $week_off_change = WeekOffChange::create([
            'from_date' => $nearestWeekOffDay,
            'is_forever' => true,
            'to_date' => $nearestEndYeayOffDay,
        ]);

        $week_off_change->Users()->sync($user->id);

        foreach ($week_days as $week_day) {
            $user_week_offs = UserWeekOff::where('user_id', $user->id)->where('week_days', $week_day)->get();
            foreach ($user_week_offs as $user_week_off) {
                $weekOffChangeDetail = new WeekOffChangeDetail;
                $weekOffChangeDetail->week_off_change_id = $week_off_change->id;
                $weekOffChangeDetail->week_days = $week_day;
                $weekOffChangeDetail->wo_type = $user_week_off->wo_type;
                $weekOffChangeDetail->first_week = $user_week_off->first_week;
                $weekOffChangeDetail->second_week = $user_week_off->second_week;
                $weekOffChangeDetail->third_week = $user_week_off->third_week;
                $weekOffChangeDetail->fourth_week = $user_week_off->fourth_week;
                $weekOffChangeDetail->fifth_week = $user_week_off->fifth_week;
                $week_off_change->WeekOffChangeDetails()->save($weekOffChangeDetail);
            }

            foreach ($user_week_offs as $user_week_off) {
                $cloanNearestWeekOffDay = clone $nearestWeekOffDay;
                while ($cloanNearestWeekOffDay <= $nearestEndYeayOffDay) {
                    $date = clone $cloanNearestWeekOffDay;
                    switch (strtolower($week_day)) {
                        case 'monday':
                            $date->modify('monday this week');
                            break;
                        case 'tuesday':
                            $date->modify('tuesday this week');
                            break;
                        case 'wednesday':
                            $date->modify('wednesday this week');
                            break;
                        case 'thursday':
                            $date->modify('thursday this week');
                            break;
                        case 'friday':
                            $date->modify('friday this week');
                            break;
                        case 'saturday':
                            $date->modify('saturday this week');
                            break;
                        case 'sunday':
                            $date->modify('sunday this week');
                            break;
                    }
                    WeekOffMuster::create([
                        'date' => $date,
                        'user_id' => $user->id,
                        // 'week_off_change_id' => $week_off_change->id,
                        'week_days' => $week_day,
                        'wo_type' => $user_week_off->wo_type,
                        'is_forever' => true,
                        'first_week' => $user_week_off->first_week,
                        'second_week' => $user_week_off->second_week,
                        'third_week' => $user_week_off->third_week,
                        'fourth_week' => $user_week_off->fourth_week,
                        'fifth_week' => $user_week_off->fifth_week,
                    ]);
                    $cloanNearestWeekOffDay->addWeek();
                }
            }
        }
    }
}
