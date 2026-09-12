<?php

namespace App\Helpers\MonthlyReportHelpers;

use App\Enums\ReportColumns\PresentReportColumns;
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
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\User;
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

class PresentReportGenerator
{
    public static $overtime_early = 0;

    public static $overtime_late = 0;

    public static $overtime = null;

    protected MonthlyReport $monthlyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        PresentReportColumns::date->value => 1,
        PresentReportColumns::code->value => 2,
        PresentReportColumns::name->value => 3,
        PresentReportColumns::location_name->value => 4,
        PresentReportColumns::location_code->value => 5,
        PresentReportColumns::company_name->value => 6,
        PresentReportColumns::company_code->value => 7,
        PresentReportColumns::department_name->value => 8,
        PresentReportColumns::department_code->value => 9,
        PresentReportColumns::sub_department_name->value => 10,
        PresentReportColumns::sub_department_code->value => 11,
        PresentReportColumns::category_name->value => 12,
        PresentReportColumns::category_code->value => 13,
        PresentReportColumns::sub_category_name->value => 14,
        PresentReportColumns::sub_category_code->value => 15,
        PresentReportColumns::shift->value => 16,
        PresentReportColumns::shift_in_time->value => 17,
        PresentReportColumns::shift_out_time->value => 18,
        PresentReportColumns::night_shift->value => 19,
        PresentReportColumns::in_time->value => 20,
        PresentReportColumns::out_time->value => 21,
        PresentReportColumns::holiday->value => 22,
        PresentReportColumns::weekoff->value => 23,
        PresentReportColumns::late->value => 24,
        PresentReportColumns::early->value => 25,
        PresentReportColumns::half_day->value => 26,
        PresentReportColumns::absent->value => 27,
        PresentReportColumns::overtime->value => 28,
        PresentReportColumns::working_hrs->value => 29,
        PresentReportColumns::manual->value => 30,
        PresentReportColumns::has_error->value => 31,
        PresentReportColumns::status->value => 32,
        PresentReportColumns::weekoff_day->value => 33,
        PresentReportColumns::remarks->value => 34,
    ];

    public function __construct(MonthlyReport $monthlyReport)
    {
        $this->monthlyReport = $monthlyReport;
        $this->year = $this->monthlyReport->from_datetime->year;
    }

    public function generatePresentReport(): void
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

        // Get is_late records
        $attendanceDetails->whereIn('status_master_id', StatusMaster::whereIn('code', ['PP', 'AP', 'PA', 'WO', 'WOP', 'PPO'])->pluck('id'));

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
        //     $heading[] = PresentReportColumns::{$column}->getLabel();
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
        $reportTitle = 'Present Report';
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
            $csv[] = ['User Code : '.$employeeId, 'User Name : '.$records[0]->user->name];

            $heading = [];
            foreach ($this->selected_columns as $column) {
                $heading[] = PresentReportColumns::{$column}->getLabel();
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
                        $row[] = $attendance->shift_in_time ? Carbon::parse($attendance->shift_in_time)->format('H:i:s') : '';
                    } else {
                        $row[] = '';
                    }
                    break;
                case 'shift_out_time':
                    if ($attendance->shift != null) {
                        $row[] = $attendance->shift_out_time ? Carbon::parse($attendance->shift_out_time)->format('H:i:s') : '';
                    } else {
                        $row[] = '';
                    }
                    break;
                case 'night_shift':
                    $row[] = $attendance->is_night_shift ? 'Yes' : 'No';
                    break;
                case 'in_time':
                    $row[] = $attendance->in_time ? Carbon::parse($attendance->in_time)->format('H:i:s') : '';
                    break;
                case 'out_time':
                    $row[] = $attendance->out_time ? Carbon::parse($attendance->out_time)->format('H:i:s') : '';
                    break;
                case 'holiday':
                    $row[] = $attendance->is_holiday ? 'Yes' : 'No';
                    break;
                case 'weekoff':
                    $row[] = $attendance->is_weekoff ? 'Yes' : 'No';
                    break;
                case 'late':
                    $late = '';
                    if ($attendance->shift != null) {
                        if ($attendance->is_late) {
                            // Check if the in_time is greater than shift_in_time
                            if ($attendance->in_time > $attendance->shift_in_time) {
                                $lateMinutes = $attendance->shift_in_time->diffInMinutes($attendance->in_time);
                            } else {
                                $lateMinutes = 0;
                            }

                            // If late minutes are greater than 0, check the late coming rule
                            if ($lateMinutes > 0) {
                                if ($attendance->user->late_coming_rule !== null) {
                                    if ($lateMinutes > $attendance->user->late_coming_rule->ignore_late_coming_minutes) {
                                        $late = $lateMinutes;
                                    }
                                } else {
                                    $late = $lateMinutes;
                                }
                            }
                        }
                        if ($late != '') {
                            $formatLate = sprintf('%02d:%02d', floor($late / 60), $late % 60);
                            $row[] = $formatLate;
                        } else {
                            $row[] = $late;
                        }
                    } else {
                        $row[] = $late;
                    }
                    break;
                case 'early':
                    $early = '';
                    if ($attendance->shift != null) {
                        if ($attendance->is_early) {
                            // Check if the in_time is greater than shift_in_time
                            if ($attendance->out_time < $attendance->shift_out_time) {
                                $earlyMinutes = $attendance->out_time->diffInMinutes($attendance->shift_out_time);
                            } else {
                                $earlyMinutes = 0;
                            }

                            // If late minutes are greater than 0, check the late coming rule
                            if ($earlyMinutes > 0) {
                                if ($attendance->user->early_going_rule !== null) {
                                    if ($earlyMinutes > $attendance->user->early_going_rule->ignore_early_going_minutes) {
                                        $early = $earlyMinutes;
                                    }
                                } else {
                                    $early = $earlyMinutes;
                                }
                            }
                        }
                        if ($early != '') {
                            $formatEarly = sprintf('%02d:%02d', floor($early / 60), $early % 60);
                            $row[] = $formatEarly;
                        } else {
                            $row[] = $early;
                        }
                    } else {
                        $row[] = $early;
                    }
                    break;
                case 'half_day':
                    $row[] = $attendance->is_half_day ? 'Yes' : 'No';
                    break;
                case 'absent':
                    $row[] = $attendance->is_absent ? 'Yes' : 'No';
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
                case 'working_hrs':
                    if (($attendance->in_time != null && $attendance->in_time != '') && ($attendance->out_time != null && $attendance->out_time != '')) {
                        $working_hrs_in_minutes = $attendance->in_time->diffInMinutes($attendance->out_time);
                        $actual_working_hrs = sprintf('%02d:%02d', floor($working_hrs_in_minutes / 60), $working_hrs_in_minutes % 60);
                    } else {
                        $actual_working_hrs = '';
                    }
                    $row[] = $actual_working_hrs;
                    break;
                case 'manual':
                    $row[] = $attendance->is_manual ? 'Yes' : 'No';
                    break;
                case 'has_error':
                    $row[] = $attendance->has_error ? 'Yes' : 'No';
                    break;
                case 'status':
                    $row[] = $attendance->status_master ? $attendance->status_master->code : '';
                    break;
                case 'weekoff_day':
                    $days = [];
                    $dayMap = [
                        '0' => 'Sunday',
                        '1' => 'Monday',
                        '2' => 'Tuesday',
                        '3' => 'Wednesday',
                        '4' => 'Thursday',
                        '5' => 'Friday',
                        '6' => 'Saturday',
                    ];
                    if ($attendance->user->user_week_offs) {
                        foreach ($attendance->user->user_week_offs as $week_off_detail) {
                            $days[] = $dayMap[$week_off_detail->week_days] ?? '';
                        }
                        $row[] = implode(', ', $days);
                    } else {
                        $row[] = '';
                    }
                    break;
                case 'remarks':
                    $row[] = $attendance->remarks ? $attendance->remarks : '';
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
