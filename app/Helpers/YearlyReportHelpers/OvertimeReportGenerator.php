<?php

namespace App\Helpers\YearlyReportHelpers;

use App\Enums\ReportColumns\OvertimeReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\YearlyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\YearlyReport;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class OvertimeReportGenerator
{
    public static $overtime_early = 0;

    public static $overtime_late = 0;

    public static $overtime = null;

    protected YearlyReport $yearlyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        OvertimeReportColumns::date->value => 1,
        OvertimeReportColumns::code->value => 2,
        OvertimeReportColumns::name->value => 3,
        OvertimeReportColumns::location_name->value => 4,
        OvertimeReportColumns::location_code->value => 5,
        OvertimeReportColumns::company_name->value => 6,
        OvertimeReportColumns::company_code->value => 7,
        OvertimeReportColumns::department_name->value => 8,
        OvertimeReportColumns::department_code->value => 9,
        OvertimeReportColumns::sub_department_name->value => 10,
        OvertimeReportColumns::sub_department_code->value => 11,
        OvertimeReportColumns::category_name->value => 12,
        OvertimeReportColumns::category_code->value => 13,
        OvertimeReportColumns::sub_category_name->value => 14,
        OvertimeReportColumns::sub_category_code->value => 15,
        OvertimeReportColumns::shift->value => 16,
        OvertimeReportColumns::shift_in_time->value => 17,
        OvertimeReportColumns::shift_out_time->value => 18,
        OvertimeReportColumns::night_shift->value => 19,
        OvertimeReportColumns::in_time->value => 20,
        OvertimeReportColumns::out_time->value => 21,
        OvertimeReportColumns::overtime->value => 22,
        OvertimeReportColumns::status->value => 23,
    ];

    public function __construct(YearlyReport $yearlyReport)
    {
        $this->yearlyReport = $yearlyReport;
        $this->year = $this->yearlyReport->from_datetime->year;
    }

    public function generateOvertimeReport(): void
    {
        $this->selected_columns = $this->yearlyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        // Generate attendance report
        $attendanceDetails = Attendance::Year($this->year)
            ->with('user');
        // $attendanceDetails->with('user.department');
        // $attendanceDetails->with('user.sub_department');
        // $attendanceDetails->with('user.company');
        // $attendanceDetails->with('user.location');
        // $attendanceDetails->with('user.category');
        // $attendanceDetails->with('user.sub_category');

        // Filter By Date
        $attendanceDetails->whereBetween(
            'date',
            [
                $this->yearlyReport->from_datetime,
                $this->yearlyReport->to_datetime,
            ]
        );

        // Get is over time records
        // $attendanceDetails->where('is_over_time', true);

        // Filter By Area
        // if ($this->yearlyReport->areas()->count() > 0) {
        //     $attendanceDetails->whereIn('canteen_id', $this->yearlyReport->canteens->pluck('id'));
        // }

        // Filter By User Type
        $attendanceDetails->whereHas('user', function ($query) {
            if ($this->yearlyReport->user_type == 1) {
                $query->whereIn('status', [1, 2]);
                $query->where('join_date', '<=', $this->yearlyReport->to_datetime);
                $query->where(function ($query) {
                    $query->where('left_date', '>=', $this->yearlyReport->from_datetime)
                        ->where('left_date', '<=', $this->yearlyReport->to_datetime)
                        ->orWhereNull('left_date');
                });
                $this->applyMasterFilter($query);
            } elseif ($this->yearlyReport->user_type == 2) {
                $query->whereIn('status', [1, 2]);
                $query->where('join_date', '<=', $this->yearlyReport->to_datetime);
                $query->where(function ($query) {
                    $query->where('left_date', '>=', $this->yearlyReport->from_datetime)
                        ->where('left_date', '<=', $this->yearlyReport->to_datetime)
                        ->orWhereNull('left_date');
                });
                $this->applyMasterFilter($query);
            }
        });
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($attendanceDetails));
        $result = $attendanceDetails->get();
        // dd("ReportingCSV", $result);
        if ($this->yearlyReport->report_format == ReportFormat::csv) {
            $this->generateDetailedCsvReport($result);
        }
    }

    public function applyMasterFilter($query)
    {
        // Filter By Location
        if ($this->yearlyReport->location_id) {
            $query->whereJsonContains('location_id', $this->yearlyReport->location_id);
        }
        // Filter By Companies
        if ($this->yearlyReport->companies()->count() > 0) {
            $companyIds = $this->yearlyReport->companies->pluck('id')->toArray();
            // dd($companyIds);
            $query->where(function ($q) use ($companyIds) {
                foreach ($companyIds as $companyId) {
                    $q->orWhereJsonContains('company_id', $companyId);
                }
            });
        }
        // Filter By Department
        if ($this->yearlyReport->departments()->count() > 0) {
            $departmentIds = $this->yearlyReport->departments->pluck('id')->toArray();
            $query->where(function ($q) use ($departmentIds) {
                foreach ($departmentIds as $departmentId) {
                    $q->orWhereJsonContains('department_id', $departmentId);
                }
            });
        }
        // Filter By SubDepartment
        if ($this->yearlyReport->sub_departments()->count() > 0) {
            $subDepartmentIds = $this->yearlyReport->sub_departments->pluck('id')->toArray();
            $query->where(function ($q) use ($subDepartmentIds) {
                foreach ($subDepartmentIds as $subDepartmentId) {
                    $q->orWhereJsonContains('sub_department_id', $subDepartmentId);
                }
            });
        }
        // Filter By Category
        if ($this->yearlyReport->categories()->count() > 0) {
            $categoryIds = $this->yearlyReport->categories->pluck('id')->toArray();
            $query->where(function ($q) use ($categoryIds) {
                foreach ($categoryIds as $categoryId) {
                    $q->orWhereJsonContains('category_id', $categoryId);
                }
            });
        }
        // Filter By SubCategory
        if ($this->yearlyReport->sub_categories()->count() > 0) {
            $subCategoryIds = $this->yearlyReport->sub_categories->pluck('id')->toArray();
            $query->where(function ($q) use ($subCategoryIds) {
                foreach ($subCategoryIds as $subCategoryId) {
                    $q->orWhereJsonContains('sub_category_id', $subCategoryId);
                }
            });
        }
        // Filter By Employees
        if ($this->yearlyReport->users()->count() > 0) {
            $query->whereIn('code', $this->yearlyReport->users->pluck('code'));
        }
    }

    public function generateDetailedCsvReport($result)
    {
        $name_to_column = [
            'company' => function (Attendance $attendance) {
                if ($attendance->user && is_array($attendance->user->company_id)) {
                    $companies = Company::whereIn('id', $attendance->user->company_id)->get();

                    $companyStrings = $companies->map(function ($company) {
                        return $company->name.' ('.$company->code.')';
                    });

                    return $companyStrings->implode(', ');
                } elseif ($attendance->user && $attendance->user->company) {
                    return $attendance->user->company->name.' ('.$attendance->user->compnay->code.')';
                }

                return '';
            },
            'department' => function (Attendance $attendance) {
                if ($attendance->user && is_array($attendance->user->department_id)) {
                    $departments = Department::whereIn('id', $attendance->user->department_id)->get();

                    $departmentStrings = $departments->map(function ($department) {
                        return $department->name.' ('.$department->code.')';
                    });

                    return $departmentStrings->implode(', ');
                } elseif ($attendance->user && $attendance->user->department) {
                    return $attendance->user->department->name.' ('.$attendance->user->department->code.')';
                }

                return '';
            },
            'sub_department' => function (Attendance $attendance) {
                if ($attendance->user && is_array($attendance->user->sub_department_id)) {
                    $sub_departments = SubDepartment::whereIn('id', $attendance->user->sub_department_id)->get();

                    $subDepartmentStrings = $sub_departments->map(function ($sub_department) {
                        return $sub_department->name.' ('.$sub_department->code.')';
                    });

                    return $subDepartmentStrings->implode(', ');
                } elseif ($attendance->user && $attendance->user->sub_department) {
                    return $attendance->user->sub_department->name.' ('.$attendance->user->sub_department->code.')';
                }

                return '';
            },
            'category' => function (Attendance $attendance) {
                if ($attendance->user && is_array($attendance->user->category_id)) {
                    $categories = Category::whereIn('id', $attendance->user->category_id)->get();

                    $categoryStrings = $categories->map(function ($category) {
                        return $category->name.' ('.$category->code.')';
                    });

                    return $categoryStrings->implode(', ');
                } elseif ($attendance->user && $attendance->user->category) {
                    return $attendance->user->category->name.' ('.$attendance->user->category->code.')';
                }

                return '';
            },
            'sub_category' => function (Attendance $attendance) {
                if ($attendance->user && is_array($attendance->user->sub_category_id)) {
                    $sub_categories = SubCategory::whereIn('id', $attendance->user->sub_category_id)->get();

                    $subCategoryStrings = $sub_categories->map(function ($sub_category) {
                        return $sub_category->name.' ('.$sub_category->code.')';
                    });

                    return $subCategoryStrings->implode(', ');
                } elseif ($attendance->user && $attendance->user->sub_category) {
                    return $attendance->user->sub_category->name.' ('.$attendance->user->sub_category->code.')';
                }

                return '';
            },
            'user' => function (Attendance $attendance) {
                return $attendance->user ? $attendance->user->code : '';
            },
        ];
        $group_by_array = [];
        foreach ($this->yearlyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }

        $user_group = fn (Attendance $attendance) => $attendance->user->code;
        $result = $result->groupBy($user_group);

        // $heading = [];
        // foreach ($this->selected_columns as $column) {
        //     //map with enum get lable
        //     $heading[] = OvertimeReportColumns::{$column}->getLabel();
        // }
        $datacsv = $this->renderCSVTree($result);

        $csv = $datacsv; // array_merge([$heading], $datacsv);

        // TODO:remove comment after generate report
        // write csv file
        // if (count($this->totalRow) > 0) {
        //     $totalRow = [];
        //     foreach ($this->selected_columns as $column) {
        //         switch ($column) {
        //             // case "quantity":
        //             //     $totalRow[] = $this->totalRow["quantity"];
        //             //     break;
        //             case "total":
        //                 $totalRow[] = $this->totalRow["total"];
        //                 break;
        //             case "company_contribution":
        //                 $totalRow[] = $this->totalRow["company_contribution"];
        //                 break;
        //             case "employee_contribution":
        //                 $totalRow[] = $this->totalRow["employee_contribution"];
        //                 break;
        //             default:
        //                 $totalRow[] = "";
        //                 break;
        //         }
        //     }
        //     $totalRow[0] = (empty($totalRow[0])) ? "Grand Total" : "Grand Total : " . $totalRow[0];
        //     $csv[] = $totalRow;
        // }

        $file_name = 'report_'.$this->yearlyReport->id.'.csv';
        $file_path = storage_path('app/public/reports/'.$file_name);
        $directory = dirname($file_path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! file_exists($file_path)) {
            $fp = fopen($file_path, 'w');
            fclose($fp);
        }

        $fp = fopen($file_path, 'w');
        foreach ($csv as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        // update report file path
        $this->yearlyReport->report_path = 'storage/reports/'.$file_name;
        $this->yearlyReport->report_status = ReportStatus::generated;
        $this->yearlyReport->save();

        YearlyReportGenerated::dispatch($this->yearlyReport->id);
        // Notify the user that the report has been generated
        $this->yearlyReport->created_by_user->notify(
            Notification::make()
                ->title('Report Generated')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->body('Your report has been generated successfully.')
                ->toDatabase(),
        );
        // dd("ReportingCSV", $result);
    }

    // public function renderCSVTree($result, $level = 1)
    // {
    //     $csv = [];
    //     $raw_number = 0;
    //     $columns = count($this->selected_columns);

    //     foreach ($result as $key => $value) {
    //         $row = [];
    //         //check value is collection of array or object
    //         if ($value instanceof \Illuminate\Support\Collection) {
    //             $row[] = $key;
    //             for ($i = 0; $i < $columns - $level; $i++) {
    //                 $row[] = "";
    //             }
    //             $csv[] = $row;
    //             $csv = array_merge($csv, $this->renderCSVTree($value, $level + 1));
    //         } elseif ($value instanceof \App\Models\Tenant\Attendance) {
    //             $csv[] = $this->renderDetailedCSVColumns($value);
    //         }
    //     }
    //     return $csv;
    // }

    // public function renderCSVTree($result, $level = 1)
    // {
    //     $csv = [];
    //     $raw_number = 0;
    //     $columns = count($this->selected_columns);
    //     $previousDate = null;

    //     foreach ($result as $key => $value) {
    //         $row = [];
    //         //check value is collection of array or object
    //         if ($value instanceof \Illuminate\Support\Collection) {
    //             $row[] = $key;
    //             for ($i = 0; $i < $columns - $level; $i++) {
    //                 $row[] = "";
    //             }
    //             $csv[] = $row;
    //             $csv = array_merge($csv, $this->renderCSVTree($value, $level + 1));
    //         } elseif ($value instanceof \App\Models\Tenant\Attendance) {
    //             $currentDate = $value->date;
    //             if ($previousDate !== null && $previousDate !== $currentDate) {
    //                 $csv[] = [""];
    //             }
    //             $csv[] = $this->renderDetailedCSVColumns($value);
    //             $previousDate = $currentDate;
    //         }
    //     }
    //     return $csv;
    // }

    public function renderCSVTree($result)
    {
        $csv = [];

        $startDate = $this->yearlyReport->from_datetime;
        $endDate = $this->yearlyReport->to_datetime;

        $all_dates = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $all_dates[] = $current->copy();
            $current->addDay();
        }

        foreach ($result as $employeeId => $records) {
            $csv[] = ['User Code : '.$employeeId, 'User Name : '.$records[0]->user->name];

            $heading = [];
            foreach ($this->selected_columns as $column) {
                $heading[] = OvertimeReportColumns::{$column}->getLabel();
            }
            $csv[] = $heading;

            foreach ($all_dates as $date) {
                $attendanceRecord = $records->firstWhere('date', $date);

                if ($attendanceRecord) {
                    $csv[] = $this->renderDetailedCSVColumns($attendanceRecord);
                } else {
                    $csv[] = [$date->format('d/m/Y')];
                }
            }
            $csv[] = [''];
        }

        return $csv;
    }

    public function renderDetailedCSVColumns($attendance)
    {
        $row = [];
        foreach ($this->selected_columns as $column) {
            switch ($column) {
                case 'date':
                    $row[] = $attendance->date->format('d/m/Y');
                    break;
                case 'code':
                    $row[] = $attendance->user->code ? $attendance->user->code : '';
                    break;
                case 'name':
                    $row[] = $attendance->user->name ? $attendance->user->name : '';
                    break;
                case 'location_name':
                    $row[] = is_array($attendance->location_id) ? collect(Location::whereIn('id', $attendance->location_id)->get())->pluck('name')->implode(', ') : ($attendance->location ? $attendance->location->name : '');
                    break;
                case 'location_code':
                    $row[] = is_array($attendance->location_id) ? collect(Location::whereIn('id', $attendance->location_id)->get())->pluck('code')->implode(', ') : ($attendance->location ? $attendance->location->name : '');
                    break;
                case 'company_name':
                    $row[] = is_array($attendance->company_id) ? collect(Company::whereIn('id', $attendance->company_id)->get())->pluck('name')->implode(', ') : ($attendance->company ? $attendance->company->name : '');
                    break;
                case 'company_code':
                    $row[] = is_array($attendance->company_id) ? collect(Company::whereIn('id', $attendance->company_id)->get())->pluck('code')->implode(', ') : ($attendance->company ? $attendance->company->code : '');
                    break;
                case 'department_name':
                    $row[] = is_array($attendance->user->department_id) ? collect(Department::whereIn('id', $attendance->user->department_id)->get())->pluck('name')->implode(', ') : ($attendance->user->department ? $attendance->user->department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($attendance->user->department_id) ? collect(Department::whereIn('id', $attendance->user->department_id)->get())->pluck('code')->implode(', ') : ($attendance->user->department ? $attendance->user->department->code : '');
                    break;
                case 'sub_department_name':
                    $row[] = is_array($attendance->user->sub_department_id) ? collect(SubDepartment::whereIn('id', $attendance->user->sub_department_id)->get())->pluck('name')->implode(', ') : ($attendance->user->sub_department ? $attendance->user->sub_department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($attendance->user->sub_department_id) ? collect(SubDepartment::whereIn('id', $attendance->user->sub_department_id)->get())->pluck('code')->implode(', ') : ($attendance->user->sub_department ? $attendance->user->sub_department->code : '');
                    break;
                case 'category_name':
                    $row[] = is_array($attendance->user->category_id) ? collect(Category::whereIn('id', $attendance->user->category_id)->get())->pluck('name')->implode(', ') : ($attendance->user->category ? $attendance->user->category->name : '');
                    break;
                case 'category_code':
                    $row[] = is_array($attendance->user->category_id) ? collect(Category::whereIn('id', $attendance->user->category_id)->get())->pluck('code')->implode(', ') : ($attendance->user->category ? $attendance->user->category->code : '');
                    break;
                case 'sub_category_name':
                    $row[] = is_array($attendance->user->sub_category_id) ? collect(SubCategory::whereIn('id', $attendance->user->sub_category_id)->get())->pluck('name')->implode(', ') : ($attendance->user->sub_category ? $attendance->user->sub_category->name : '');
                    break;
                case 'sub_category_code':
                    $row[] = is_array($attendance->user->sub_category_id) ? collect(SubCategory::whereIn('id', $attendance->user->sub_category_id)->get())->pluck('code')->implode(', ') : ($attendance->user->sub_category ? $attendance->user->sub_category->code : '');
                    break;
                case 'shift':
                    $row[] = $attendance->shift ? $attendance->shift->name : '';
                    break;
                case 'shift_in_time':
                    if ($attendance->shift != null) {
                        $row[] = Carbon::parse($attendance->shift_in_time)->format('H:i:s') ? Carbon::parse($attendance->shift_in_time)->format('H:i:s') : '';
                    } else {
                        $row[] = '';
                    }
                    break;
                case 'shift_out_time':
                    if ($attendance->shift != null) {
                        $row[] = Carbon::parse($attendance->shift_out_time)->format('H:i:s') ? Carbon::parse($attendance->shift_out_time)->format('H:i:s') : '';
                    } else {
                        $row[] = '';
                    }
                    break;
                case 'night_shift':
                    $row[] = $attendance->is_night_shift ? 'Yes' : 'No';
                    break;
                case 'in_time':
                    $row[] = Carbon::parse($attendance->in_time)->format('H:i:s') ? Carbon::parse($attendance->in_time)->format('H:i:s') : '';
                    break;
                case 'out_time':
                    $row[] = Carbon::parse($attendance->out_time)->format('H:i:s') ? Carbon::parse($attendance->out_time)->format('H:i:s') : '';
                    break;
                case 'overtime':
                    $overTime = '';
                    if ($attendance->shift != null) {
                        if ($attendance->is_over_time) {
                            if (GeneralHelper::checkSettings('half_day_rules') === true) {
                                if ($attendance->user->overtime_rule) {
                                    if ($attendance->out_time) {
                                        $overtimeRule = $attendance->user->overtime_rule;

                                        $shiftInTime = $attendance->shift_in_time ? Carbon::parse($attendance->shift_in_time) : null;
                                        $shiftOutTime = $attendance->shift_out_time ? Carbon::parse($attendance->shift_out_time) : null;
                                        $punchInTime = $attendance->in_time ? Carbon::parse($attendance->in_time) : null;
                                        $punchOutTime = $attendance->out_time ? Carbon::parse($attendance->out_time) : null;

                                        if ($punchOutTime > $shiftOutTime) {
                                            $out_diff = $shiftOutTime ? $shiftOutTime->diffInMinutes($punchOutTime) : 0;
                                        } else {
                                            $out_diff = 0;
                                        }

                                        if ($punchInTime < $shiftInTime) {
                                            $early_diff = $punchInTime ? $punchInTime->diffInMinutes($shiftInTime) : 0;
                                            $diffInHoursMinutes = sprintf('%02d:%02d', floor($early_diff / 60), $early_diff % 60);
                                            if ($overtimeRule->ignore_early_come) {
                                                if ($diffInHoursMinutes > $overtimeRule->ignore_early_come_minutes->format('H:i')) {
                                                    // self::$overtime_early = $diffInHoursMinutes;
                                                    self::$overtime_early = $early_diff;
                                                    $out_diff = $out_diff + self::$overtime_early;
                                                }
                                            } else {
                                                if ($punchInTime < $shiftInTime) {
                                                    self::$overtime_early = $early_diff;
                                                    $out_diff = $out_diff + self::$overtime_early;
                                                }
                                                if ($punchOutTime < $shiftOutTime) {
                                                    $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                                                }
                                            }
                                        }

                                        if ($punchInTime > $shiftInTime) {
                                            $late_diff = $shiftInTime ? $shiftInTime->diffInMinutes($punchInTime) : 0;
                                            $diffOutHoursMinutes = sprintf('%02d:%02d', floor($late_diff / 60), $late_diff % 60);
                                            if ($overtimeRule->ignore_late_come) {
                                                if ($diffOutHoursMinutes > $overtimeRule->ignore_late_come_minutes->format('H:i')) {
                                                    self::$overtime_late = $late_diff;
                                                    $out_diff = $out_diff - self::$overtime_late;
                                                }
                                            } else {
                                                if ($punchInTime > $shiftInTime) {
                                                    self::$overtime_late = $late_diff;
                                                    $out_diff = $out_diff - self::$overtime_late;
                                                }
                                                if ($punchOutTime < $shiftOutTime) {
                                                    $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                                                }
                                            }
                                        }

                                        if ($out_diff >= 0) {
                                            $totalDiff = $out_diff;
                                            $overtimerule_slabs = $overtimeRule->overtime_slabs()->get();
                                            foreach ($overtimerule_slabs as $overtimerule_slab) {
                                                if (sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) > '00:00' && sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) >= $overtimerule_slab->value_from->format('H:i') && sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) <= $overtimerule_slab->value_to->format('H:i')) {
                                                    $overTime = $overtimerule_slab->head_value->format('H:i');
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    if ($attendance->out_time) {
                                        $shiftInTime = $attendance->shift_in_time ? Carbon::parse($attendance->shift_in_time) : null;
                                        $shiftOutTime = $attendance->shift_out_time ? Carbon::parse($attendance->shift_out_time) : null;
                                        $punchInTime = $attendance->in_time ? Carbon::parse($attendance->in_time) : null;
                                        $punchOutTime = $attendance->out_time ? Carbon::parse($attendance->out_time) : null;

                                        $skipOvertimeMinutes = 0;
                                        if ($attendance->user->category->skip_overtime && $attendance->user->category->skip_overtime > '00:00' || $attendance->user->category->skip_overtime > '00:00:00') {
                                            $skipOvertimeTime = $attendance->user->category->skip_overtime;
                                            [$hours, $minutes] = explode(':', $skipOvertimeTime->format('H:i'));
                                            $skipOvertimeMinutes = ($hours * 60) + $minutes;
                                        }

                                        if ($punchOutTime > $shiftOutTime) {
                                            $out_diff = $shiftOutTime ? $shiftOutTime->diffInMinutes($punchOutTime) : 0;
                                        } else {
                                            $out_diff = 0;
                                        }

                                        if ($punchInTime < $shiftInTime) {
                                            $early_diff = $punchInTime ? $punchInTime->diffInMinutes($shiftInTime) : 0;
                                            $diffInHoursMinutes = sprintf('%02d:%02d', floor($early_diff / 60), $early_diff % 60);
                                            if ($punchInTime < $shiftInTime) {
                                                self::$overtime_early = $early_diff;
                                                $out_diff = $out_diff + self::$overtime_early;
                                            }
                                            if ($punchOutTime < $shiftOutTime) {
                                                $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                                            }
                                        }

                                        if ($punchInTime > $shiftInTime) {
                                            $late_diff = $shiftInTime ? $shiftInTime->diffInMinutes($punchInTime) : 0;
                                            $diffOutHoursMinutes = sprintf('%02d:%02d', floor($late_diff / 60), $late_diff % 60);
                                            if ($punchInTime > $shiftInTime) {
                                                self::$overtime_late = $late_diff;
                                                $out_diff = $out_diff - self::$overtime_late;
                                            }
                                            if ($punchOutTime < $shiftOutTime) {
                                                $out_diff = $out_diff - $punchOutTime->diffInMinutes($shiftOutTime);
                                            }
                                        }

                                        // Apply skip overtime - subtract the configured skip time from calculated overtime
                                        if ($skipOvertimeMinutes > 0 && $out_diff > 0) {
                                            $overtimeBeforeSkip = $out_diff;
                                            $out_diff = max(0, $out_diff - $skipOvertimeMinutes);
                                        }

                                        if ($out_diff >= 0) {
                                            $totalDiff = $out_diff;
                                            if (sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60) > '00:00' && $totalDiff > 0) {
                                                $overTime = sprintf('%02d:%02d', floor($totalDiff / 60), $totalDiff % 60);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $row[] = $overTime;
                    break;
                case 'status':
                    $row[] = $attendance->status_master ? $attendance->status_master->code : '';
                    break;
                    // TODO:Fix machine code in report
                    // case "machine_name":
                    //     $row[] = $orderDetail->order->punch_logs()
                    //         ->where('user_code', $orderDetail->order->user->user_code)
                    //         ->first()->machine?->machine_name;
                    //     break;
                    // case "machine_serial_number":
                    //     $row[] = $orderDetail->order->punch_logs()
                    //         ->where('user_code', $orderDetail->order->user->user_code)
                    //         ->first()->machine?->machine_serial_number;
                    //     break;
                default:
                    $row[] = 'Unknown Column';
                    break;
            }
        }

        return $row;
    }
}
