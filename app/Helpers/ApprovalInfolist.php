<?php

namespace App\Helpers;

use App\Enums\WOTypeEnumn;
use Carbon\Carbon;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;

class ApprovalInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        $record = $infolist->getRecord();
        if ($record::class === "App\Models\Tenant\ShiftChange") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Shift Change Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('shift_type')->label('Shift Type'),
                            TextEntry::make('from_date')->label('From Date'),
                            TextEntry::make('is_forever')->formatStateUsing(fn ($state) => $state == 1 ? 'Yes' : 'No')->label('Is Forever'),
                            TextEntry::make('to_date')->hidden(fn ($record) => $record->is_forever == 1)->label('To Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('User Request Shift Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('shifts.code')->hidden(fn ($record) => $record->shift_type == 'rotational')->badge()->label('Sift Change Shifts'),
                                    TextEntry::make('shift_rotation.code')->hidden(fn ($record) => $record->shift_type == 'auto' || $record->shift_type == 'fixed')->badge()->label('Sift Change Shifts'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\ShiftChangeApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Shift Change Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('shift_type')->label('Shift Type'),
                            TextEntry::make('from_date')->label('From Date'),
                            TextEntry::make('is_forever')->formatStateUsing(fn ($state) => $state == 1 ? 'Yes' : 'No')->label('Is Forever'),
                            TextEntry::make('to_date')->hidden(fn ($record) => $record->is_forever == 1)->label('To Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('User Request Shift Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('shifts.code')->hidden(fn ($record) => $record->shift_type == 'rotational')->badge()->label('Sift Change Shifts'),
                                    TextEntry::make('shift_rotation.code')->hidden(fn ($record) => $record->shift_type == 'auto' || $record->shift_type == 'fixed')->badge()->label('Sift Change Shifts'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\WeekOffChangeApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Week Off Change Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('from_date')->label('From Date'),
                            TextEntry::make('is_forever')->formatStateUsing(fn ($state) => $state == 1 ? 'Yes' : 'No')->label('Is Forever'),
                            TextEntry::make('to_date')->hidden(fn ($record) => $record->is_forever == 1)->label('To Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('User Request Week Off Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('WeekOffChangeDetails')
                                        ->formatStateUsing(function ($state, $record) {
                                            $weekDetails = $record->WeekOffChangeDetail ?? $record->WeekOffChangeDetails ?? [];
                                            if (empty($weekDetails)) {
                                                return '-';
                                            }

                                            $weekDays = [
                                                0 => 'Sunday',
                                                1 => 'Monday',
                                                2 => 'Tuesday',
                                                3 => 'Wednesday',
                                                4 => 'Thursday',
                                                5 => 'Friday',
                                                6 => 'Saturday',
                                            ];

                                            $result = '';
                                            foreach ($weekDetails as $detail) {
                                                $dayName = $weekDays[$detail->week_days] ?? $detail->week_days;
                                                $weekNumbers = [];

                                                if ($detail->first_week) {
                                                    $weekNumbers[] = '1st';
                                                }
                                                if ($detail->second_week) {
                                                    $weekNumbers[] = '2nd';
                                                }
                                                if ($detail->third_week) {
                                                    $weekNumbers[] = '3rd';
                                                }
                                                if ($detail->fourth_week) {
                                                    $weekNumbers[] = '4th';
                                                }
                                                if ($detail->fifth_week) {
                                                    $weekNumbers[] = '5th';
                                                }

                                                // Properly handle WOTypeEnumn objects
                                                $weekType = match (true) {
                                                    $detail->wo_type instanceof WOTypeEnumn => $detail->wo_type->getLabel(),
                                                    is_numeric($detail->wo_type) => match ((int) $detail->wo_type) {
                                                        1 => 'Full Day',
                                                        2 => 'First Half',
                                                        3 => 'Second Half',
                                                        default => '-',
                                                    },
                                                    default => '-',
                                                };

                                                $result .= "<div class='mb-2'>";
                                                $result .= "<span class='font-medium'>$dayName</span>: ";
                                                $result .= '<span>Weeks ('.implode(', ', $weekNumbers).')</span>, ';
                                                $result .= "<span>Type: $weekType</span>";
                                                $result .= '</div>';
                                            }

                                            return new HtmlString($result);
                                        })
                                        ->label('Week Off Details')
                                        ->columnSpanFull(),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\WeekOffChange") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Week Off Change Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('from_date')->label('From Date'),
                            TextEntry::make('is_forever')->formatStateUsing(fn ($state) => $state == 1 ? 'Yes' : 'No')->label('Is Forever'),
                            TextEntry::make('to_date')->hidden(fn ($record) => $record->is_forever == 1)->label('To Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('User Request Week Off Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('WeekOffChangeDetails')
                                        ->formatStateUsing(function ($state, $record) {
                                            $weekDetails = $record->WeekOffChangeDetail ?? $record->WeekOffChangeDetails ?? [];
                                            if (empty($weekDetails)) {
                                                return '-';
                                            }

                                            $weekDays = [
                                                0 => 'Sunday',
                                                1 => 'Monday',
                                                2 => 'Tuesday',
                                                3 => 'Wednesday',
                                                4 => 'Thursday',
                                                5 => 'Friday',
                                                6 => 'Saturday',
                                            ];

                                            $result = '';
                                            foreach ($weekDetails as $detail) {
                                                $dayName = $weekDays[$detail->week_days] ?? $detail->week_days;
                                                $weekNumbers = [];

                                                if ($detail->is_first_week) {
                                                    $weekNumbers[] = '1st';
                                                }
                                                if ($detail->is_second_week) {
                                                    $weekNumbers[] = '2nd';
                                                }
                                                if ($detail->is_third_week) {
                                                    $weekNumbers[] = '3rd';
                                                }
                                                if ($detail->is_fourth_week) {
                                                    $weekNumbers[] = '4th';
                                                }
                                                if ($detail->is_fifth_week) {
                                                    $weekNumbers[] = '5th';
                                                }

                                                $weekType = match ((int) $detail->week_off_type) {
                                                    1 => 'Full Day',
                                                    2 => 'First Half',
                                                    3 => 'Second Half',
                                                    default => '-',
                                                };

                                                $result .= "<div class='mb-2'>";
                                                $result .= "<span class='font-medium'>$dayName</span>: ";
                                                $result .= '<span>Weeks ('.implode(', ', $weekNumbers).')</span>, ';
                                                $result .= "<span>Type: $weekType</span>";
                                                $result .= '</div>';
                                            }

                                            return new HtmlString($result);
                                        })
                                        ->label('Week Off Details')
                                        ->columnSpanFull(),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\ManualPunchApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Manual Punch Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('punch_date')->label('Punch Date'),
                            TextEntry::make('punch_time')->label('Punch Time'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Manual Punch Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('punch_type')->formatStateUsing(fn ($state) => $state == 1 ? 'In Punch' : 'Out Punch')->badge()->label('Punch Type'),
                                    TextEntry::make('reason')->badge()->label('Manual Punch Reason'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\ManualAttendanceApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Manual Attendance Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('created_at')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->label('Attendance Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Manual Attendance Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('shift_type')->badge()->label('Shift Type'),
                                    TextEntry::make('shift.name')->hidden(fn ($record) => $record->shift_type == 'rotational')->badge()->label('Shift'),
                                    TextEntry::make('shift_rotation.code')->hidden(fn ($record) => $record->shift_type == 'auto' || $record->shift_type == 'fixed')->badge()->label('Shift Rotation'),
                                    TextEntry::make('in_time')->badge()->label('In Time'),
                                    TextEntry::make('out_time')->badge()->label('Out Time'),
                                    TextEntry::make('reason')->badge()->label('Manual Attendance Application Reason'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\WeekOffSwapApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Week Off Swap Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('created_at')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->label('Application Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Week Off Swap Application Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('Users.name')->badge()->label('User Name'),
                                    TextEntry::make('Users.code')->badge()->label('User Code'),
                                    TextEntry::make('week_date')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->badge()->label('Week Off Date'),
                                    TextEntry::make('swap_date')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->badge()->label('Week Off Swap Date'),
                                    TextEntry::make('reason')->badge()->label('Week Off Swap Reason'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\LeaveApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Leave Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('created_at')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->label('Application Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Leave Application Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('user.name')->badge()->label('User Name'),
                                    TextEntry::make('user.code')->badge()->label('User Code'),
                                    TextEntry::make('leave_type.code')->badge()->label('Leave Type'),
                                    TextEntry::make('leave_reason.leave_reason')->badge()->label('Leave Reason'),
                                    TextEntry::make('is_only_second_half')->badge()->label('Is Only Second Half')->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                                    TextEntry::make('from_date')
                                        ->formatStateUsing(function ($state, $record) {
                                            if (! $record->is_only_second_half) {
                                                return Carbon::parse($state)
                                                    ->format('Y-m-d');
                                            }
                                        })->badge()->label('From Date'),
                                    TextEntry::make('is_second_half')->badge()->label('Is Second Half')
                                        ->formatStateUsing(function ($state, $record) {
                                            if (! $record->is_only_second_half) {
                                                if ($state) {
                                                    return 'Yes';
                                                } else {
                                                    return 'No';
                                                }
                                            } else {
                                                return '-';
                                            }
                                        }),
                                    TextEntry::make('to_date')
                                        ->formatStateUsing(function ($state, $record) {
                                            if (! $record->is_only_second_half) {
                                                return Carbon::parse($state)
                                                    ->format('Y-m-d');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('To Date'),
                                    TextEntry::make('is_first_half')->badge()->label('Is First Half')
                                        ->formatStateUsing(function ($state, $record) {
                                            if (! $record->is_only_second_half) {
                                                if ($state) {
                                                    return 'Yes';
                                                } else {
                                                    return 'No';
                                                }
                                            } else {
                                                return '-';
                                            }
                                        }),
                                    TextEntry::make('reason_explanation')->badge()->label('Reason explanation'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\ShortLeaveApplication") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Short Leave Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('created_at')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->label('Application Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Short Leave Application Details')
                                ->columns(2)
                                ->columnSpan(2)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('user.name')->badge()->label('User Name'),
                                    TextEntry::make('user.code')->badge()->label('User Code'),
                                    TextEntry::make('date')
                                        ->formatStateUsing(function ($state) {
                                            return Carbon::parse($state)
                                                ->format('d-m-Y');
                                        })->badge()->label('Date'),
                                    TextEntry::make('short_leave_type')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->short_leave_type == '1') {
                                                return 'Late Coming';
                                            } elseif ($record->short_leave_type == '2') {
                                                return 'Early Going';
                                            } elseif ($record->short_leave_type == '3') {
                                                return 'Break Between Working Hours';
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('Short Leave Type'),
                                    TextEntry::make('from_time')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->to_date) {
                                                return Carbon::parse($state)
                                                    ->format('H:i:s');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('From Time'),
                                    TextEntry::make('to_time')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->to_date) {
                                                return Carbon::parse($state)
                                                    ->format('H:i:s');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('To Time'),
                                    TextEntry::make('minutes')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->minutes) {
                                                return $record->minutes;
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('Minutes'),
                                    TextEntry::make('reason')->badge()->label('Reason'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\Coff") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Coff Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('created_at')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->label('Application Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Coff Application Details')
                                ->columns(3)
                                ->columnSpan(3)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('user.name')->badge()->label('User Name'),
                                    TextEntry::make('user.code')->badge()->label('User Code'),
                                    TextEntry::make('coff_against_date')
                                        ->formatStateUsing(function ($state) {
                                            return Carbon::parse($state)
                                                ->format('d-m-Y');
                                        })->badge()->label('Coff Against Date'),
                                    TextEntry::make('from_date')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->from_date) {
                                                return Carbon::parse($state)
                                                    ->format('d-m-Y');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('From Date'),
                                    TextEntry::make('to_date')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->to_date) {
                                                return Carbon::parse($state)
                                                    ->format('d-m-Y');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('To Date'),
                                    TextEntry::make('only_a_half_day')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->only_a_half_day == true) {
                                                return 'Yes';
                                            } else {
                                                return 'No';
                                            }
                                        })->badge()->label('Only A Half Day?'),
                                    TextEntry::make('is_this_second_half')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->is_this_second_half == true) {
                                                return 'Yes';
                                            } else {
                                                return 'No';
                                            }
                                        })->badge()->label('Is This Second Half?'),
                                    TextEntry::make('is_first_half')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->is_first_half == true) {
                                                return 'Yes';
                                            } else {
                                                return 'No';
                                            }
                                        })->badge()->label('Is First Half?'),
                                    TextEntry::make('is_second_half')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->is_second_half == true) {
                                                return 'Yes';
                                            } else {
                                                return 'No';
                                            }
                                        })->badge()->label('Is Second Half?'),
                                ]),

                        ]),
                ]);
        } elseif ($record::class === "App\Models\Tenant\OutDuty") {
            return $infolist
                ->columns(2)
                ->schema([
                    Fieldset::make('Out Duty Application Info')
                        ->columns(1)
                        ->columnSpan(1)
                        ->id('main-section')
                        ->schema([
                            TextEntry::make('created_at')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('Y-m-d'))->label('Application Date'),
                            TextEntry::make('approval_status.approval_status')->label('Approval Status')->badge()->color(function (string $state): string {
                                $approval_status = strtolower($state ?? 'unknown');

                                return match ($approval_status) {
                                    'draft' => 'gray',
                                    'pending' => 'primary',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'info',
                                };
                            }),
                        ]),
                    Group::make()
                        ->columns(1)
                        ->schema([
                            Fieldset::make('Out Duty Application Details')
                                ->columns(3)
                                ->columnSpan(3)
                                ->id('filter-options')
                                ->schema([
                                    TextEntry::make('user.name')->badge()->label('User Name'),
                                    TextEntry::make('user.code')->badge()->label('User Code'),
                                    TextEntry::make('from_date_time')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->from_date_time) {
                                                return Carbon::parse($state)
                                                    ->format('d-m-Y H:i:s');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('From Date & Time'),
                                    TextEntry::make('to_date_time')
                                        ->formatStateUsing(function ($state, $record) {
                                            if ($record->to_date_time) {
                                                return Carbon::parse($state)
                                                    ->format('d-m-Y H:i:s');
                                            } else {
                                                return '-';
                                            }
                                        })->badge()->label('To Date & Time'),
                                    TextEntry::make('reason')->badge()->label('Reason'),
                                ]),

                        ]),
                ]);
        }
    }
}
