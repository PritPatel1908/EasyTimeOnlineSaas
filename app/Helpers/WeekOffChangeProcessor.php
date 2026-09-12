<?php

namespace App\Helpers;

use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffChangeUser;
use App\Models\Tenant\WeekOffMuster;

class WeekOffChangeProcessor
{
    public static function processWeekOffChangeCreateEdit(WeekOffChange $weekOffChange)
    {
        $week_off_change = $weekOffChange;

        $from_date = $week_off_change->from_date;
        if ($week_off_change->is_forever == 0) {
            $to_date = $week_off_change->to_date;
        } else {
            $to_date = now()->endOfYear();
        }

        $week_off_change_user_ids = WeekOffChangeUser::where('week_off_change_id', $week_off_change->id)->get();

        foreach ($week_off_change_user_ids as $user) {
            $recordsToUpdate = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->where('from_date', '<', $week_off_change->from_date)
                ->where('is_forever', true)
                ->orderByDesc('from_date')
                ->get();

            if (! $recordsToUpdate->isEmpty()) {
                foreach ($recordsToUpdate as $recordToUpdate) {
                    $new_to_date = now()->endOfYear();
                    $week_off_musters = WeekOffMuster::whereBetween('date', [$recordToUpdate->from_date, $new_to_date])
                        ->where('is_locked', false)
                        ->whereIn('user_id', $week_off_change_user_ids->pluck('user_id'))->delete();

                    foreach ($recordToUpdate->WeekOffChangeDetail as $week_day) {
                        $currentDate = clone $recordToUpdate->from_date;
                        $currentDate->modify('this '.$week_day->week_days);

                        while ($currentDate <= $new_to_date) {
                            WeekOffMuster::create([
                                'date' => $currentDate,
                                'user_id' => $user->user_id,
                                'week_off_change_id' => $week_day->id,
                                'week_days' => $week_day->week_days,
                                'is_forever' => true,
                                'week_off_type' => $week_day->week_off_type,
                                'wo_type' => $week_day->wo_type,
                                'first_week' => $week_day->first_week,
                                'second_week' => $week_day->second_week,
                                'third_week' => $week_day->third_week,
                                'fourth_week' => $week_day->fourth_week,
                                'fifth_week' => $week_day->fifth_week,
                            ]);
                            $currentDate->addWeek();
                        }
                    }

                    $overlappingRecords = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                        $query->where('user_id', $user->user_id);
                    })->where('from_date', '>', $recordToUpdate->from_date)
                        ->where('to_date', '<', $week_off_change->to_date)
                        // ->where('is_forever', false)
                        ->orderBy('from_date', 'asc')
                        ->get();

                    if ($overlappingRecords->count() > 0) {
                        $nextRecords = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                            $query->where('user_id', $user->user_id);
                        })->orderBy('from_date', 'asc')
                            ->where('from_date', '>', $recordToUpdate->from_date)
                            ->where('to_date', '<', $recordToUpdate->to_date)
                            // ->where('is_forever', false)
                            ->get();

                        foreach ($nextRecords as $nextRecord) {
                            foreach ($nextRecord->WeekOffChangeDetail as $day) {
                                $week_off_musters = WeekOffMuster::whereBetween('date', [$nextRecord->from_date, $nextRecord->to_date])
                                    ->where('is_locked', false)
                                    ->whereIn('user_id', $week_off_change_user_ids->pluck('user_id'))->delete();

                                $currentDate = clone $nextRecord->from_date;
                                $currentDate->modify('this '.$day->week_days);
                                while ($currentDate <= $nextRecord->to_date) {
                                    WeekOffMuster::create([
                                        'date' => $currentDate,
                                        'user_id' => $user->user_id,
                                        'week_off_change_id' => $day->week_off_change_id,
                                        'week_days' => $day->week_days,
                                        'is_forever' => $nextRecord->is_forever,
                                        'week_off_type' => $day->week_off_type,
                                        'wo_type' => $day->wo_type,
                                        'first_week' => $day->first_week,
                                        'second_week' => $day->second_week,
                                        'third_week' => $day->third_week,
                                        'fourth_week' => $day->fourth_week,
                                        'fifth_week' => $day->fifth_week,
                                    ]);
                                    $currentDate->addWeek();
                                }
                            }
                        }
                    } else {
                        $nextRecords = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                            $query->where('user_id', $user->user_id);
                        })->where('from_date', '>=', $week_off_change->from_date)
                            ->where('from_date', '>', $recordToUpdate->from_date)
                            ->where('to_date', '<', $recordToUpdate->to_date)
                            // ->where('is_forever', false)
                            ->orderBy('from_date', 'asc')
                            ->get();

                        foreach ($nextRecords as $nextRecord) {
                            foreach ($nextRecord->WeekOffChangeDetail as $day) {
                                $week_off_musters = WeekOffMuster::whereBetween('date', [$nextRecord->from_date, $nextRecord->to_date])
                                    ->where('is_locked', false)
                                    ->whereIn('user_id', $week_off_change_user_ids->pluck('user_id'))->delete();

                                $currentDate = clone $nextRecord->from_date;
                                $currentDate->modify('this '.$day->week_days);

                                while ($currentDate <= $nextRecord->to_date) {
                                    WeekOffMuster::create([
                                        'date' => $currentDate,
                                        'user_id' => $user->user_id,
                                        'week_off_change_id' => $day->week_off_change_id,
                                        'week_days' => $day->week_days,
                                        'is_forever' => $nextRecord->is_forever,
                                        'week_off_type' => $day->week_off_type,
                                        'wo_type' => $day->wo_type,
                                        'first_week' => $day->first_week,
                                        'second_week' => $day->second_week,
                                        'third_week' => $day->third_week,
                                        'fourth_week' => $day->fourth_week,
                                        'fifth_week' => $day->fifth_week,
                                    ]);

                                    $currentDate->addWeek();
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($week_off_change->WeekOffChangeDetail as $day) {
                    $currentDate = clone $week_off_change->from_date;
                    $currentDate->modify('this '.$day->week_days);
                    if ($week_off_change->to_date == null) {
                        $end_date = now()->endOfYear();
                        WeekOffMuster::whereBetween('date', [$week_off_change->from_date, $end_date])
                            ->where('is_locked', false)
                            ->whereIn('user_id', $week_off_change_user_ids->pluck('user_id'))->delete();
                    } else {
                        $end_date = $week_off_change->to_date;
                        WeekOffMuster::whereBetween('date', [$week_off_change->from_date, $end_date])
                            ->where('is_locked', false)
                            ->whereIn('user_id', $week_off_change_user_ids->pluck('user_id'))->delete();
                    }

                    while ($currentDate <= $end_date) {
                        WeekOffMuster::create([
                            'date' => $currentDate,
                            'user_id' => $user->user_id,
                            'week_off_change_id' => $day->week_off_change_id,
                            'week_days' => $day->week_days,
                            'is_forever' => $week_off_change->is_forever,
                            'week_off_type' => $day->week_off_type,
                            'wo_type' => $day->wo_type,
                            'first_week' => $day->first_week,
                            'second_week' => $day->second_week,
                            'third_week' => $day->third_week,
                            'fourth_week' => $day->fourth_week,
                            'fifth_week' => $day->fifth_week,
                        ]);

                        $currentDate->addWeek();
                    }
                }
            }
        }
    }

    public static function processWeekOffChangeDelete(WeekOffChange $weekOffChange, $user_get)
    {
        $week_off_change = $weekOffChange;

        $from_date = $week_off_change->from_date;
        if ($week_off_change->is_forever == 0) {
            $to_date = $week_off_change->to_date;
        } else {
            $to_date = now()->endOfYear();
        }

        foreach ($user_get as $user) {
            $recordsToUpdate = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->where('from_date', '<', $week_off_change->from_date)
                ->where('is_forever', true)
                ->orderByDesc('from_date')
                ->get();

            if (! $recordsToUpdate->isEmpty()) {
                foreach ($recordsToUpdate as $recordToUpdate) {
                    $new_to_date = now()->endOfYear();
                    $week_off_musters = WeekOffMuster::whereBetween('date', [$recordToUpdate->from_date, $new_to_date])
                        ->where('is_locked', false)
                        ->whereIn('user_id', $user_get->pluck('user_id'))->delete();

                    foreach ($recordToUpdate->WeekOffChangeDetail as $week_day) {
                        $currentDate = clone $recordToUpdate->from_date;
                        $currentDate->modify('this '.$week_day->week_days);

                        while ($currentDate <= $new_to_date) {
                            WeekOffMuster::create([
                                'date' => $currentDate,
                                'user_id' => $user->user_id,
                                'week_off_change_id' => $week_day->id,
                                'week_days' => $week_day->week_days,
                                'is_forever' => true,
                                'week_off_type' => $week_day->week_off_type,
                                'wo_type' => $week_day->wo_type,
                                'first_week' => $week_day->first_week,
                                'second_week' => $week_day->second_week,
                                'third_week' => $week_day->third_week,
                                'fourth_week' => $week_day->fourth_week,
                                'fifth_week' => $week_day->fifth_week,
                            ]);
                            $currentDate->addWeek();
                        }
                    }

                    $overlappingRecords = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                        $query->where('user_id', $user->user_id);
                    })->where('from_date', '>', $recordToUpdate->from_date)
                        ->where('to_date', '<', $week_off_change->to_date)
                        // ->where('is_forever', false)
                        ->orderBy('from_date', 'asc')
                        ->get();

                    if ($overlappingRecords->count() > 0) {
                        $nextRecords = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                            $query->where('user_id', $user->user_id);
                        })->orderBy('from_date', 'asc')
                            ->where('from_date', '>', $recordToUpdate->from_date)
                            ->where('to_date', '<', $recordToUpdate->to_date)
                            // ->where('is_forever', false)
                            ->get();

                        foreach ($nextRecords as $nextRecord) {
                            foreach ($nextRecord->WeekOffChangeDetail as $day) {
                                $week_off_musters = WeekOffMuster::whereBetween('date', [$nextRecord->from_date, $nextRecord->to_date])
                                    ->where('is_locked', false)
                                    ->whereIn('user_id', $user_get->pluck('user_id'))->delete();

                                $currentDate = clone $nextRecord->from_date;
                                $currentDate->modify('this '.$day->week_days);
                                while ($currentDate <= $nextRecord->to_date) {
                                    WeekOffMuster::create([
                                        'date' => $currentDate,
                                        'user_id' => $user->user_id,
                                        'week_off_change_id' => $day->week_off_change_id,
                                        'week_days' => $day->week_days,
                                        'is_forever' => $nextRecord->is_forever,
                                        'week_off_type' => $day->week_off_type,
                                        'wo_type' => $day->wo_type,
                                        'first_week' => $day->first_week,
                                        'second_week' => $day->second_week,
                                        'third_week' => $day->third_week,
                                        'fourth_week' => $day->fourth_week,
                                        'fifth_week' => $day->fifth_week,
                                    ]);
                                    $currentDate->addWeek();
                                }
                            }
                        }
                    } else {
                        $nextRecords = WeekOffChange::whereHas('Users', function ($query) use ($user) {
                            $query->where('user_id', $user->user_id);
                        })->where('from_date', '>=', $week_off_change->from_date)
                            ->where('from_date', '>', $recordToUpdate->from_date)
                            ->where('to_date', '<', $recordToUpdate->to_date)
                            // ->where('is_forever', false)
                            ->orderBy('from_date', 'asc')
                            ->get();

                        foreach ($nextRecords as $nextRecord) {
                            foreach ($nextRecord->WeekOffChangeDetail as $day) {
                                $week_off_musters = WeekOffMuster::whereBetween('date', [$nextRecord->from_date, $nextRecord->to_date])
                                    ->where('is_locked', false)
                                    ->whereIn('user_id', $user_get->pluck('user_id'))->delete();

                                $currentDate = clone $nextRecord->from_date;
                                $currentDate->modify('this '.$day->week_days);

                                while ($currentDate <= $nextRecord->to_date) {
                                    WeekOffMuster::create([
                                        'date' => $currentDate,
                                        'user_id' => $user->user_id,
                                        'week_off_change_id' => $day->week_off_change_id,
                                        'week_days' => $day->week_days,
                                        'is_forever' => $nextRecord->is_forever,
                                        'week_off_type' => $day->week_off_type,
                                        'wo_type' => $day->wo_type,
                                        'first_week' => $day->first_week,
                                        'second_week' => $day->second_week,
                                        'third_week' => $day->third_week,
                                        'fourth_week' => $day->fourth_week,
                                        'fifth_week' => $day->fifth_week,
                                    ]);

                                    $currentDate->addWeek();
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($week_off_change->WeekOffChangeDetail as $day) {
                    $currentDate = clone $week_off_change->from_date;
                    $currentDate->modify('this '.$day->week_days);
                    if ($week_off_change->to_date == null) {
                        $end_date = now()->endOfYear();
                    } else {
                        $end_date = $week_off_change->to_date;
                    }

                    while ($currentDate <= $end_date) {
                        WeekOffMuster::create([
                            'date' => $currentDate,
                            'user_id' => $user->user_id,
                            'week_off_change_id' => $day->week_off_change_id,
                            'week_days' => $day->week_days,
                            'is_forever' => $week_off_change->is_forever,
                            'week_off_type' => $day->week_off_type,
                            'wo_type' => $day->wo_type,
                            'first_week' => $day->first_week,
                            'second_week' => $day->second_week,
                            'third_week' => $day->third_week,
                            'fourth_week' => $day->fourth_week,
                            'fifth_week' => $day->fifth_week,
                        ]);

                        $currentDate->addWeek();
                    }
                }
            }
        }
    }
}
