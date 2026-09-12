<?php

namespace App\Helpers\MonthlyReportHelpers;

use App\Enums\ReportColumns\WorkHrsReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\MonthlyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\MonthlyReport;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkHrsReportGenerator
{
    protected $work_hrs = 0;

    protected MonthlyReport $monthlyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        WorkHrsReportColumns::date->value => 1,
        WorkHrsReportColumns::code->value => 2,
        WorkHrsReportColumns::name->value => 3,
        WorkHrsReportColumns::location_name->value => 4,
        WorkHrsReportColumns::location_code->value => 5,
        WorkHrsReportColumns::company_name->value => 6,
        WorkHrsReportColumns::company_code->value => 7,
        WorkHrsReportColumns::department_name->value => 8,
        WorkHrsReportColumns::department_code->value => 9,
        WorkHrsReportColumns::sub_department_name->value => 10,
        WorkHrsReportColumns::sub_department_code->value => 11,
        WorkHrsReportColumns::category_name->value => 12,
        WorkHrsReportColumns::category_code->value => 13,
        WorkHrsReportColumns::sub_category_name->value => 14,
        WorkHrsReportColumns::sub_category_code->value => 15,
        WorkHrsReportColumns::shift->value => 16,
        WorkHrsReportColumns::in_time->value => 20,
        WorkHrsReportColumns::out_time->value => 21,
        WorkHrsReportColumns::shift_hrs->value => 22,
        WorkHrsReportColumns::work_hrs->value => 23,
    ];

    public function __construct(MonthlyReport $monthlyReport)
    {
        $this->monthlyReport = $monthlyReport;
        $this->year = $this->monthlyReport->from_datetime->year;
    }

    public function generateWorkHrsReport(): void
    {
        $this->selected_columns = $this->monthlyReport->selected_columns;
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
                $this->monthlyReport->from_datetime,
                $this->monthlyReport->to_datetime,
            ]
        );

        // Filter By Area
        // if ($this->monthlyReport->areas()->count() > 0) {
        //     $attendanceDetails->whereIn('canteen_id', $this->monthlyReport->canteens->pluck('id'));
        // }

        // Filter By User Type
        $attendanceDetails->whereHas('user', function ($query) {
            if ($this->monthlyReport->user_type == 1) {
                $query->whereIn('status', [1, 2]);
                $query->where('join_date', '<=', $this->monthlyReport->to_datetime);
                $query->where(function ($query) {
                    $query->where('left_date', '>=', $this->monthlyReport->from_datetime)
                        ->where('left_date', '<=', $this->monthlyReport->to_datetime)
                        ->orWhereNull('left_date');
                });
                $this->applyMasterFilter($query);
            } elseif ($this->monthlyReport->user_type == 2) {
                $query->whereIn('status', [1, 2]);
                $query->where('join_date', '<=', $this->monthlyReport->to_datetime);
                $query->where(function ($query) {
                    $query->where('left_date', '>=', $this->monthlyReport->from_datetime)
                        ->where('left_date', '<=', $this->monthlyReport->to_datetime)
                        ->orWhereNull('left_date');
                });
                $this->applyMasterFilter($query);
            }
        });
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($attendanceDetails));
        $result = $attendanceDetails->get();
        // dd("ReportingCSV", $result);
        if ($this->monthlyReport->report_format == ReportFormat::csv) {
            $this->generateDetailedCsvReport($result);
        } else {
            // Default to XLSX format
            $this->generateDetailedXlsxReport($result);
        }
    }

    public function applyMasterFilter($query)
    {
        // Filter By Location
        if ($this->monthlyReport->location_id) {
            $query->whereJsonContains('location_id', $this->monthlyReport->location_id);
        }
        // Filter By Companies
        if ($this->monthlyReport->companies()->count() > 0) {
            $companyIds = $this->monthlyReport->companies->pluck('id')->toArray();
            // dd($companyIds);
            $query->where(function ($q) use ($companyIds) {
                foreach ($companyIds as $companyId) {
                    $q->orWhereJsonContains('company_id', $companyId);
                }
            });
        }
        // Filter By Department
        if ($this->monthlyReport->departments()->count() > 0) {
            $departmentIds = $this->monthlyReport->departments->pluck('id')->toArray();
            $query->where(function ($q) use ($departmentIds) {
                foreach ($departmentIds as $departmentId) {
                    $q->orWhereJsonContains('department_id', $departmentId);
                }
            });
        }
        // Filter By SubDepartment
        if ($this->monthlyReport->sub_departments()->count() > 0) {
            $subDepartmentIds = $this->monthlyReport->sub_departments->pluck('id')->toArray();
            $query->where(function ($q) use ($subDepartmentIds) {
                foreach ($subDepartmentIds as $subDepartmentId) {
                    $q->orWhereJsonContains('sub_department_id', $subDepartmentId);
                }
            });
        }
        // Filter By Category
        if ($this->monthlyReport->categories()->count() > 0) {
            $categoryIds = $this->monthlyReport->categories->pluck('id')->toArray();
            $query->where(function ($q) use ($categoryIds) {
                foreach ($categoryIds as $categoryId) {
                    $q->orWhereJsonContains('category_id', $categoryId);
                }
            });
        }
        // Filter By SubCategory
        if ($this->monthlyReport->sub_categories()->count() > 0) {
            $subCategoryIds = $this->monthlyReport->sub_categories->pluck('id')->toArray();
            $query->where(function ($q) use ($subCategoryIds) {
                foreach ($subCategoryIds as $subCategoryId) {
                    $q->orWhereJsonContains('sub_category_id', $subCategoryId);
                }
            });
        }
        // Filter By Employees
        if ($this->monthlyReport->users()->count() > 0) {
            $query->whereIn('code', $this->monthlyReport->users->pluck('code'));
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
        foreach ($this->monthlyReport->group_by_columns as $column) {
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
        //     $heading[] = SinglePunchReportColumns::{$column}->getLabel();
        // }
        $datacsv = $this->renderCSVTree($result);

        $csv = $datacsv;

        // TODO:remove comment after generate report
        // write csv file
        // if (count($this->totalRow) > 0) {
        //     $totalRow = [];
        //     foreach ($this->selected_columns as $column) {
        //         switch ($column) {
        //                 // case "quantity":
        //                 //     $totalRow[] = $this->totalRow["quantity"];
        //                 //     break;
        //             case "total":
        //                 $totalRow[] = $this->totalRow["total"];
        //                 break;
        //                 // case "company_contribution":
        //                 //     $totalRow[] = $this->totalRow["company_contribution"];
        //                 //     break;
        //                 // case "employee_contribution":
        //                 //     $totalRow[] = $this->totalRow["employee_contribution"];
        //                 //     break;
        //             default:
        //                 $totalRow[] = "";
        //                 break;
        //         }
        //     }
        //     $totalRow[0] = (empty($totalRow[0])) ? "Grand Total" : "Grand Total : " . $totalRow[0];
        //     $csv[] = $totalRow;
        // }

        $file_name = 'report_'.$this->monthlyReport->id.'.csv';
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
        $this->monthlyReport->report_path = 'storage/reports/'.$file_name;
        $this->monthlyReport->report_status = ReportStatus::generated;
        $this->monthlyReport->save();

        MonthlyReportGenerated::dispatch($this->monthlyReport->id);
        // Notify the user that the report has been generated
        $this->monthlyReport->created_by_user->notify(
            Notification::make()
                ->title('Report Generated')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->body('Your report has been generated successfully.')
                ->toDatabase(),
        );
        // dd("ReportingCSV", $result);
    }

    public function generateDetailedXlsxReport($result)
    {
        // Process data similar to CSV method
        $user_group = fn (Attendance $attendance) => $attendance->user->code;
        $result = $result->groupBy($user_group);

        $datacsv = $this->renderCSVTree($result);

        // Add title header rows
        $reportTitle = 'Work Hours Report';
        $dateRange = 'Report Period: '.$this->monthlyReport->from_datetime->format('d/m/Y').' - '.$this->monthlyReport->to_datetime->format('d/m/Y');

        // Insert title rows at the beginning
        array_unshift($datacsv, [$dateRange], [$reportTitle]);

        $csv = $datacsv;

        // Create Excel file using Maatwebsite Excel
        $file_name = 'report_'.$this->monthlyReport->id.'.xlsx';
        $file_path = storage_path('app/public/reports/'.$file_name);
        $directory = dirname($file_path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create Excel file with better styling
        Excel::store(
            new class($csv, $this->monthlyReport) implements FromArray, ShouldAutoSize, WithColumnWidths, WithStyles
            {
                protected $data;

                protected $monthlyReport;

                public function __construct(array $data, MonthlyReport $monthlyReport)
                {
                    $this->data = $data;
                    $this->monthlyReport = $monthlyReport;
                }

                public function array(): array
                {
                    return $this->data;
                }

                public function styles(Worksheet $sheet)
                {
                    // Merge cells for title and date range and center align
                    $sheet->mergeCells('A1:J1');
                    $sheet->mergeCells('A2:J2');
                    $sheet->getStyle('A1:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Style title row
                    $sheet->getStyle('A1')->getFont()->setBold(true);
                    $sheet->getStyle('A1')->getFont()->setSize(16);
                    $sheet->getStyle('A1')->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('4472C4'); // Blue background
                    $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF'); // White text

                    // Style date range row
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $sheet->getStyle('A2')->getFont()->setSize(12);
                    $sheet->getStyle('A2')->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D9E1F2'); // Light blue background

                    // Add extra padding for title rows
                    $sheet->getRowDimension(1)->setRowHeight(30);
                    $sheet->getRowDimension(2)->setRowHeight(25);

                    // Add header styling for each section
                    foreach ($this->data as $rowIndex => $row) {
                        // Skip the first two rows (title and date range)
                        if ($rowIndex < 2) {
                            continue;
                        }

                        // Style headers (assumption: headers contain "User Code" or are column names)
                        if (is_array($row) && isset($row[0]) && (strpos($row[0], 'User Code') !== false || strpos($row[0], 'SUMMARY') !== false)) {
                            // Merge user info cells
                            if (strpos($row[0], 'User Code') !== false && isset($row[1]) && strpos($row[1], 'User Name') !== false) {
                                $sheet->mergeCells('A'.($rowIndex + 1).':B'.($rowIndex + 1));
                                $sheet->getStyle('A'.($rowIndex + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            }

                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFont()->setSize(12);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('DDEBF7'); // Light blue background
                        }
                        // Style column headers
                        elseif (is_array($row) && isset($row[0]) && in_array('Date', $row)) {
                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('E2EFDA'); // Light green background
                        }
                        // Style summary rows
                        elseif (is_array($row) && isset($row[0]) && strpos($row[0], 'SUMMARY') !== false) {
                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FCE4D6'); // Light orange background
                        }
                        // Style Grand Total rows
                        elseif (is_array($row) && isset($row[0]) && strpos($row[0], 'Grand Total') !== false) {
                            // Merge the Grand Total cells if there are multiple columns
                            if (count($row) > 1) {
                                $lastCol = chr(64 + min(count($row), 26)); // Convert to letter (A, B, C...)
                                $sheet->mergeCells('A'.($rowIndex + 1).':'.$lastCol.($rowIndex + 1));
                                $sheet->getStyle('A'.($rowIndex + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            }

                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFC000'); // Gold background
                        }
                    }

                    // Add borders to all data cells
                    $lastRow = count($this->data);
                    $maxColumn = 'Z'; // Adjust as needed based on your data width

                    $sheet->getStyle('A1:'.$maxColumn.$lastRow)
                        ->getBorders()->getAllBorders()->setBorderStyle(
                            Border::BORDER_THIN
                        );

                    return [];
                }

                public function columnWidths(): array
                {
                    return [
                        'A' => 15,
                        'B' => 15,
                        'C' => 25,
                        'D' => 15,
                        'E' => 15,
                        'F' => 15,
                        'G' => 15,
                        'H' => 15,
                        'I' => 15,
                        'J' => 15,
                    ];
                }
            },
            'reports/'.$file_name,
            'public'
        );

        // update report file path
        $this->monthlyReport->report_path = 'storage/reports/'.$file_name;
        $this->monthlyReport->report_status = ReportStatus::generated;
        $this->monthlyReport->save();

        MonthlyReportGenerated::dispatch($this->monthlyReport->id);
        // Notify the user that the report has been generated
        $this->monthlyReport->created_by_user->notify(
            Notification::make()
                ->title('Report Generated')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->body('Your report has been generated successfully.')
                ->toDatabase(),
        );
    }

    public function renderCSVTree($result)
    {
        $csv = [];

        $startDate = $this->monthlyReport->from_datetime;
        $endDate = $this->monthlyReport->to_datetime;

        $all_dates = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $all_dates[] = $current->copy();
            $current->addDay();
        }

        foreach ($result as $employeeId => $records) {
            $this->work_hrs = 0;
            $csv[] = ['User Code : '.$employeeId, 'User Name : '.$records[0]->user->name];

            $heading = [];
            foreach ($this->selected_columns as $column) {
                $heading[] = WorkHrsReportColumns::{$column}->getLabel();
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

            $totalRow = array_fill(0, count($this->selected_columns) - 1, '');
            $totalRow[0] = 'Grand Total';
            $totalRow[count($this->selected_columns) - 1] = sprintf('%02d:%02d', floor($this->work_hrs / 60), $this->work_hrs % 60);
            $csv[] = $totalRow;

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
                case 'in_time':
                    $row[] = $attendance->in_time ? Carbon::parse($attendance->in_time)->format('H:i:s') : '';
                    break;
                case 'out_time':
                    $row[] = $attendance->out_time ? Carbon::parse($attendance->out_time)->format('H:i:s') : '';
                    break;
                case 'shift_hrs':
                    if ($attendance->shift_in_time && $attendance->shift_out_time) {
                        $shift_hrs = $attendance->shift_in_time->diffInMinutes($attendance->shift_out_time);
                    } else {
                        $shift_hrs = 0;
                    }
                    $row[] = sprintf('%02d:%02d', floor($shift_hrs / 60), $shift_hrs % 60);
                    break;
                case 'work_hrs':
                    if ($attendance->in_time && $attendance->out_time) {
                        $work_hrs = $attendance->in_time->diffInMinutes($attendance->out_time);
                    } else {
                        $work_hrs = 0;
                    }
                    $this->work_hrs += $work_hrs;
                    $row[] = sprintf('%02d:%02d', floor($work_hrs / 60), $work_hrs % 60);
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
