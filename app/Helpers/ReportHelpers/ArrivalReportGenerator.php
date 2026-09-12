<?php

namespace App\Helpers\ReportHelpers;

use App\Enums\ReportColumns\ArrivalReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\DailyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DailyReport;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use LDAP\Result;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ArrivalReportGenerator
{
    protected DailyReport $dailyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        ArrivalReportColumns::date->value => 1,
        ArrivalReportColumns::code->value => 2,
        ArrivalReportColumns::name->value => 3,
        ArrivalReportColumns::location_name->value => 4,
        ArrivalReportColumns::location_code->value => 5,
        ArrivalReportColumns::company_name->value => 6,
        ArrivalReportColumns::company_code->value => 7,
        ArrivalReportColumns::department_name->value => 8,
        ArrivalReportColumns::department_code->value => 9,
        ArrivalReportColumns::sub_department_name->value => 10,
        ArrivalReportColumns::sub_department_code->value => 11,
        ArrivalReportColumns::category_name->value => 12,
        ArrivalReportColumns::category_code->value => 13,
        ArrivalReportColumns::sub_category_name->value => 14,
        ArrivalReportColumns::sub_category_code->value => 15,
        ArrivalReportColumns::shift->value => 16,
        ArrivalReportColumns::shift_in_time->value => 17,
        ArrivalReportColumns::night_shift->value => 18,
        ArrivalReportColumns::time->value => 19,
        ArrivalReportColumns::status->value => 20,
    ];

    public function __construct(DailyReport $dailyReport)
    {
        $this->dailyReport = $dailyReport;
        $this->year = $this->dailyReport->from_datetime->year;
    }

    public function generateArrivalReport(): void
    {
        $this->selected_columns = $this->dailyReport->selected_columns;
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
                $this->dailyReport->from_datetime,
                $this->dailyReport->to_datetime,
            ]
        );

        // Filter By Area
        // if ($this->dailyReport->areas()->count() > 0) {
        //     $attendanceDetails->whereIn('canteen_id', $this->dailyReport->canteens->pluck('id'));
        // }

        // Filter By User Type
        $attendanceDetails->whereHas('user', function ($query) {
            if ($this->dailyReport->user_type == 1) {
                $query->whereIn('status', [1, 2]);
                $query->where('join_date', '<=', $this->dailyReport->to_datetime);
                $query->where(function ($query) {
                    $query->where('left_date', '>=', $this->dailyReport->from_datetime)
                        ->where('left_date', '<=', $this->dailyReport->to_datetime)
                        ->orWhereNull('left_date');
                });
                $this->applyMasterFilter($query);
            } elseif ($this->dailyReport->user_type == 2) {
                $query->whereIn('status', [1, 2]);
                $query->where('join_date', '<=', $this->dailyReport->to_datetime);
                $query->where(function ($query) {
                    $query->where('left_date', '>=', $this->dailyReport->from_datetime)
                        ->where('left_date', '<=', $this->dailyReport->to_datetime)
                        ->orWhereNull('left_date');
                });
                $this->applyMasterFilter($query);
            }
        });
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($attendanceDetails));
        $result = $attendanceDetails->get();
        // dd("ReportingCSV", $result);
        if ($this->dailyReport->report_format == ReportFormat::csv) {
            $this->generateDetailedCsvReport($result);
        } elseif ($this->dailyReport->report_format == ReportFormat::xlsx) {
            $this->generateDetailedXlsxReport($result);
        }
    }

    public function applyMasterFilter($query)
    {
        // Filter By Location
        if ($this->dailyReport->location_id) {
            $query->whereJsonContains('location_id', $this->dailyReport->location_id);
        }
        // Filter By Companies
        if ($this->dailyReport->companies()->count() > 0) {
            $companyIds = $this->dailyReport->companies->pluck('id')->toArray();
            // dd($companyIds);
            $query->where(function ($q) use ($companyIds) {
                foreach ($companyIds as $companyId) {
                    $q->orWhereJsonContains('company_id', $companyId);
                }
            });
        }
        // Filter By Department
        if ($this->dailyReport->departments()->count() > 0) {
            $departmentIds = $this->dailyReport->departments->pluck('id')->toArray();
            $query->where(function ($q) use ($departmentIds) {
                foreach ($departmentIds as $departmentId) {
                    $q->orWhereJsonContains('department_id', $departmentId);
                }
            });
        }
        // Filter By SubDepartment
        if ($this->dailyReport->sub_departments()->count() > 0) {
            $subDepartmentIds = $this->dailyReport->sub_departments->pluck('id')->toArray();
            $query->where(function ($q) use ($subDepartmentIds) {
                foreach ($subDepartmentIds as $subDepartmentId) {
                    $q->orWhereJsonContains('sub_department_id', $subDepartmentId);
                }
            });
        }
        // Filter By Category
        if ($this->dailyReport->categories()->count() > 0) {
            $categoryIds = $this->dailyReport->categories->pluck('id')->toArray();
            $query->where(function ($q) use ($categoryIds) {
                foreach ($categoryIds as $categoryId) {
                    $q->orWhereJsonContains('category_id', $categoryId);
                }
            });
        }
        // Filter By SubCategory
        if ($this->dailyReport->sub_categories()->count() > 0) {
            $subCategoryIds = $this->dailyReport->sub_categories->pluck('id')->toArray();
            $query->where(function ($q) use ($subCategoryIds) {
                foreach ($subCategoryIds as $subCategoryId) {
                    $q->orWhereJsonContains('sub_category_id', $subCategoryId);
                }
            });
        }
        // Filter By Employees
        if ($this->dailyReport->users()->count() > 0) {
            $query->whereIn('code', $this->dailyReport->users->pluck('code'));
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
        foreach ($this->dailyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }

        // Check data format and generate appropriate CSV
        if (isset($this->dailyReport->data_format) && $this->dailyReport->data_format->value == 'horizontal') {
            $csv = $this->generateHorizontalCSV($result);
        } else {
            // Default vertical format
            $heading = [];
            foreach ($this->selected_columns as $column) {
                // map with enum get lable
                $heading[] = ArrivalReportColumns::{$column}->getLabel();
            }
            $datacsv = $this->renderCSVTree($result);
            $csv = array_merge([$heading], $datacsv);
        }

        $file_name = 'report_'.$this->dailyReport->id.'.csv';
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
        $this->dailyReport->report_path = 'storage/reports/'.$file_name;
        $this->dailyReport->report_status = ReportStatus::generated;
        $this->dailyReport->save();

        DailyReportGenerated::dispatch($this->dailyReport->id);
        // Notify the user that the report has been generated
        $this->dailyReport->created_by_user->notify(
            Notification::make()
                ->title('Report Generated')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->body('Your report has been generated successfully.')
                ->toDatabase(),
        );
    }

    /**
     * Generate horizontal format CSV
     *
     * @param  mixed  $result
     * @return array
     */
    public function generateHorizontalCSV($result)
    {
        // Get all attendance records
        $allAttendances = $this->flattenAttendances($result);

        // Get all unique dates from the attendance records
        $uniqueDates = $allAttendances->pluck('date')->unique()->sort();

        // Get all unique user codes
        $userCodes = $allAttendances->pluck('user.code')->unique();

        // Prepare header row - first add all selected columns except date
        $headerRow = [];
        $identityColumns = [];

        foreach ($this->selected_columns as $column) {
            if ($column != 'date') {
                $headerRow[] = ArrivalReportColumns::{$column}->getLabel();
                $identityColumns[] = $column;
            }
        }

        // Add date columns to header
        foreach ($uniqueDates as $date) {
            $headerRow[] = $date->format('d/m/Y');
        }

        $csv = [$headerRow];

        // Group attendances by user
        $userAttendances = [];
        foreach ($allAttendances as $attendance) {
            $userCode = $attendance->user->code;
            if (! isset($userAttendances[$userCode])) {
                $userAttendances[$userCode] = [
                    'user' => $attendance->user,
                    'dates' => [],
                ];
            }

            $userAttendances[$userCode]['dates'][$attendance->date->format('Y-m-d')] = $attendance;
        }

        // Create rows for each user
        foreach ($userAttendances as $userCode => $userData) {
            $user = $userData['user'];
            $row = [];

            // Add user identity columns
            foreach ($identityColumns as $column) {
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
                        $row[] = $attendance->shift_in_time ? Carbon::parse($attendance->shift_in_time)->format('H:i:s') : '';
                        break;
                    case 'night_shift':
                        $row[] = $attendance->is_night_shift ? 'Yes' : 'No';
                        break;
                    case 'time':
                        $in_punch = AttendanceLog::where('user_id', $attendance->user->id)
                            ->whereDate('datetime', $attendance->date->format('Y-m-d'))
                            ->where('is_out', false)
                            ->orderBy('datetime')
                            ->first();
                        if ($in_punch != null && $in_punch != '') {
                            $row[] = Carbon::parse($in_punch->datetime)->format('H:i:s');
                        } else {
                            $row[] = '';
                        }
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

            // Add time for each date
            foreach ($uniqueDates as $date) {
                $dateStr = $date->format('Y-m-d');
                if (isset($userData['dates'][$dateStr])) {
                    $attendance = $userData['dates'][$dateStr];
                    $in_punch = AttendanceLog::where('user_id', $attendance->user->id)
                        ->whereDate('datetime', $attendance->date->format('Y-m-d'))
                        ->where('is_out', false)
                        ->orderBy('datetime')
                        ->first();
                    if ($in_punch != null && $in_punch != '') {
                        $row[] = Carbon::parse($in_punch->datetime)->format('H:i:s');
                    } else {
                        $row[] = '';
                    }
                } else {
                    $row[] = '-';
                }
            }

            $csv[] = $row;
        }

        return $csv;
    }

    /**
     * Flatten the nested result structure to get all attendance records
     *
     * @param  mixed  $result
     * @return \Illuminate\Support\Collection
     */
    private function flattenAttendances($result)
    {
        $attendances = collect();

        if ($result instanceof \Illuminate\Support\Collection) {
            foreach ($result as $key => $value) {
                if ($value instanceof \Illuminate\Support\Collection) {
                    $attendances = $attendances->merge($this->flattenAttendances($value));
                } elseif ($value instanceof Attendance) {
                    $attendances->push($value);
                }
            }
        } elseif ($result instanceof Attendance) {
            $attendances->push($result);
        }

        return $attendances;
    }

    public function renderCSVTree($result, $level = 1)
    {
        $csv = [];
        $raw_number = 0;
        $columns = count($this->selected_columns);

        foreach ($result as $key => $value) {
            $row = [];
            // check value is collection of array or object
            if ($value instanceof \Illuminate\Support\Collection) {
                $row[] = $key;
                for ($i = 0; $i < $columns - $level; $i++) {
                    $row[] = '';
                }
                $csv[] = $row;
                $csv = array_merge($csv, $this->renderCSVTree($value, $level + 1));
            } elseif ($value instanceof Attendance) {
                $csv[] = $this->renderDetailedCSVColumns($value);
            }
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
                    $row[] = $attendance->user->location->name ? $attendance->user->location->name : '';
                    break;
                case 'location_code':
                    $row[] = $attendance->user->location->code ? $attendance->user->location->code : '';
                    break;
                case 'company_name':
                    $row[] = $attendance->user->company->name ? $attendance->user->company->name : '';
                    break;
                case 'company_code':
                    $row[] = $attendance->user->company->code ? $attendance->user->company->code : '';
                    break;
                case 'department_name':
                    $row[] = $attendance->user->department->name ? $attendance->user->department->name : '';
                    break;
                case 'department_code':
                    $row[] = $attendance->user->department->code ? $attendance->user->department->code : '';
                    break;
                case 'sub_department_name':
                    $row[] = $attendance->user->sub_department->name ? $attendance->user->sub_department->name : '';
                    break;
                case 'sub_department_code':
                    $row[] = $attendance->user->sub_department->code ? $attendance->user->sub_department->code : '';
                    break;
                case 'category_name':
                    $row[] = $attendance->user->category->name ? $attendance->user->category->name : '';
                    break;
                case 'category_code':
                    $row[] = $attendance->user->category->code ? $attendance->user->category->code : '';
                    break;
                case 'sub_category_name':
                    $row[] = $attendance->user->sub_category->name ? $attendance->user->sub_category->name : '';
                    break;
                case 'sub_category_code':
                    $row[] = $attendance->user->sub_category->code ? $attendance->user->sub_category->code : '';
                    break;
                case 'shift':
                    $row[] = $attendance->shift ? $attendance->shift->name : '';
                    break;
                case 'shift_in_time':
                    $row[] = $attendance->shift_in_time ? Carbon::parse($attendance->shift_in_time)->format('H:i:s') : '';
                    break;
                case 'night_shift':
                    $row[] = $attendance->is_night_shift ? 'Yes' : 'No';
                    break;
                case 'time':
                    $in_punch = AttendanceLog::where('user_id', $attendance->user->id)
                        ->whereDate('datetime', $attendance->date->format('Y-m-d'))
                        ->where('is_out', false)
                        ->orderBy('datetime')
                        ->first();
                    if ($in_punch != null && $in_punch != '') {
                        $row[] = Carbon::parse($in_punch->datetime)->format('H:i:s');
                    } else {
                        $row[] = '';
                    }
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

    public function generateXlsxReport($result)
    {
        // Implementation of generateXlsxReport method
    }

    public function generateDetailedXlsxReport($result)
    {
        // Check data format and generate appropriate data array
        if (isset($this->dailyReport->data_format) && $this->dailyReport->data_format->value == 'horizontal') {
            $data = $this->generateHorizontalCSV($result);
        } else {
            // Default vertical format
            $heading = [];
            foreach ($this->selected_columns as $column) {
                // map with enum get lable
                $heading[] = ArrivalReportColumns::{$column}->getLabel();
            }
            $dataRows = $this->renderCSVTree($result);
            $data = array_merge([$heading], $dataRows);
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arrival Report');

        // Get company name for the title
        $companyName = '';
        if ($this->dailyReport->companies()->count() > 0) {
            $companyName = $this->dailyReport->companies->first()->name;
        }

        // Add report title and date range
        $reportTitle = ($companyName ? $companyName.' - ' : '').'Arrival Report';
        $dateRange = 'Period: '.$this->dailyReport->from_datetime->format('d/m/Y').' to '.$this->dailyReport->to_datetime->format('d/m/Y');
        $generatedOn = 'Generated on: '.now()->format('d/m/Y H:i');

        // Try to add company logo if available
        $logoPath = public_path('images/company_logo.png');
        $hasLogo = file_exists($logoPath);

        // If logo exists, add it to the sheet
        if ($hasLogo) {
            $drawing = new Drawing;
            $drawing->setName('Company Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(60);
            $drawing->setWorksheet($sheet);

            // Adjust title position if logo is present
            $titleCell = 'C1';
            $sheet->setCellValue($titleCell, $reportTitle);
            $sheet->mergeCells($titleCell.':'.$this->columnIndexToLetter(count($data[0])).'1');

            // Add date range and generated date with offset
            $sheet->setCellValue('C2', $dateRange);
            $sheet->mergeCells('C2:'.$this->columnIndexToLetter(count($data[0])).'2');

            $sheet->setCellValue('C3', $generatedOn);
            $sheet->mergeCells('C3:'.$this->columnIndexToLetter(count($data[0])).'3');
        } else {
            // Add title row with merge cells (no logo)
            $sheet->setCellValue('A1', $reportTitle);
            $sheet->mergeCells('A1:'.$this->columnIndexToLetter(count($data[0])).'1');

            // Add date range row
            $sheet->setCellValue('A2', $dateRange);
            $sheet->mergeCells('A2:'.$this->columnIndexToLetter(count($data[0])).'2');

            // Add generated date row
            $sheet->setCellValue('A3', $generatedOn);
            $sheet->mergeCells('A3:'.$this->columnIndexToLetter(count($data[0])).'3');
        }

        // Style the title and date cells
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'],
            ],
        ];

        $dateStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // Apply styles based on whether logo is present
        if ($hasLogo) {
            $sheet->getStyle('C1:'.$this->columnIndexToLetter(count($data[0])).'1')->applyFromArray($titleStyle);
            $sheet->getStyle('C2:'.$this->columnIndexToLetter(count($data[0])).'3')->applyFromArray($dateStyle);
            // Reserve space for logo
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(5);
        } else {
            $sheet->getStyle('A1:'.$this->columnIndexToLetter(count($data[0])).'1')->applyFromArray($titleStyle);
            $sheet->getStyle('A2:'.$this->columnIndexToLetter(count($data[0])).'3')->applyFromArray($dateStyle);
        }

        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(15); // Empty row as separator

        // Add data to sheet starting from row 5 (after title and date)
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnIndex => $cell) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 5, $cell);
            }
        }

        // Style the header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ];
        $sheet->getStyle('A5:'.$this->columnIndexToLetter(count($data[0])).'5')->applyFromArray($headerStyle);

        // Zebra striping for data rows
        for ($i = 6; $i <= count($data) + 4; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A'.$i.':'.$this->columnIndexToLetter(count($data[0])).$i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'],
                    ],
                ]);
            }
        }

        // Add borders to all data cells
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A5:'.$this->columnIndexToLetter(count($data[0])).($rowIndex + 5))->applyFromArray($borderStyle);

        // Add footer with page numbers
        $sheet->getHeaderFooter()->setOddFooter('&L&B'.($companyName ?: 'Company').'&C&P of &N&R&D');

        // Set print area and page setup
        $lastRow = count($data) + 5;
        $lastColumn = $this->columnIndexToLetter(count($data[0]));
        $sheet->getPageSetup()->setPrintArea('A1:'.$lastColumn.$lastRow);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // Repeat header rows on each page when printing
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 5);

        // Auto size columns
        foreach (range(1, count($data[0])) as $columnIndex) {
            $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
        }

        // Set file name and path
        $file_name = 'report_'.$this->dailyReport->id.'.xlsx';
        $file_path = storage_path('app/public/reports/'.$file_name);
        $directory = dirname($file_path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save the spreadsheet with higher memory limit
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false); // Improve performance
        $writer->save($file_path);

        // Update report file path
        $this->dailyReport->report_path = 'storage/reports/'.$file_name;
        $this->dailyReport->report_status = ReportStatus::generated;
        $this->dailyReport->save();

        DailyReportGenerated::dispatch($this->dailyReport->id);
        // Notify the user that the report has been generated
        $this->dailyReport->created_by_user->notify(
            Notification::make()
                ->title('Report Generated')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->body('Your report has been generated successfully.')
                ->toDatabase(),
        );
    }

    /**
     * Convert column index to Excel column letter
     *
     * @param  int  $columnIndex
     * @return string
     */
    private function columnIndexToLetter($columnIndex)
    {
        $letter = '';
        while ($columnIndex > 0) {
            $remainder = ($columnIndex - 1) % 26;
            $letter = chr(65 + $remainder).$letter;
            $columnIndex = floor(($columnIndex - $remainder - 1) / 26);
        }

        return $letter;
    }
}
