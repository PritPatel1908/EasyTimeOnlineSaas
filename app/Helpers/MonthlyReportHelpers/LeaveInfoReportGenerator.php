<?php

namespace App\Helpers\MonthlyReportHelpers;

use App\Enums\ReportColumns\LeaveInfoReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\MonthlyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\LeaveApplication;
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

class LeaveInfoReportGenerator
{
    protected MonthlyReport $monthlyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        LeaveInfoReportColumns::date->value => 1,
        LeaveInfoReportColumns::emp_code->value => 2,
        LeaveInfoReportColumns::emp_name->value => 3,
        LeaveInfoReportColumns::department_name->value => 4,
        LeaveInfoReportColumns::department_code->value => 5,
        LeaveInfoReportColumns::category_name->value => 6,
        LeaveInfoReportColumns::category_code->value => 7,
        LeaveInfoReportColumns::leave_type->value => 8,
        LeaveInfoReportColumns::from_date->value => 9,
        LeaveInfoReportColumns::to_date->value => 10,
        LeaveInfoReportColumns::leave_count->value => 11,
        LeaveInfoReportColumns::status->value => 12,
        LeaveInfoReportColumns::leave_reason->value => 13,
        LeaveInfoReportColumns::reason_explanation->value => 14,
    ];

    public function __construct(MonthlyReport $monthlyReport)
    {
        $this->monthlyReport = $monthlyReport;
        $this->year = $this->monthlyReport->from_datetime->year;
    }

    public function generateLeaveInfoReport(): void
    {
        $this->selected_columns = $this->monthlyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        // Generate transaction report
        $leaveApplications = LeaveApplication::whereHas('user');

        // Filter By Date
        $leaveApplications->whereBetween(
            'application_date',
            [
                $this->monthlyReport->from_datetime,
                $this->monthlyReport->to_datetime,
            ]
        );

        // Filter By User Type
        $leaveApplications->whereHas('user', function ($query) {
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
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($leaveApplications));
        $result = $leaveApplications->get();
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
            'company' => function (LeaveApplication $application) {
                if ($application->user && is_array($application->user->company_id)) {
                    $companies = Company::whereIn('id', $application->user->company_id)->get();

                    $companyStrings = $companies->map(function ($company) {
                        return $company->name.' ('.$company->code.')';
                    });

                    return $companyStrings->implode(', ');
                } elseif ($application->user && $application->user->company) {
                    return $application->user->company->name.' ('.$application->user->compnay->code.')';
                }

                return '';
            },
            'department' => function (LeaveApplication $application) {
                if ($application->user && is_array($application->user->department_id)) {
                    $departments = Department::whereIn('id', $application->user->department_id)->get();

                    $departmentStrings = $departments->map(function ($department) {
                        return $department->name.' ('.$department->code.')';
                    });

                    return $departmentStrings->implode(', ');
                } elseif ($application->user && $application->user->department) {
                    return $application->user->department->name.' ('.$application->user->department->code.')';
                }

                return '';
            },
            'sub_department' => function (LeaveApplication $application) {
                if ($application->user && is_array($application->user->sub_department_id)) {
                    $sub_departments = SubDepartment::whereIn('id', $application->user->sub_department_id)->get();

                    $subDepartmentStrings = $sub_departments->map(function ($sub_department) {
                        return $sub_department->name.' ('.$sub_department->code.')';
                    });

                    return $subDepartmentStrings->implode(', ');
                } elseif ($application->user && $application->user->sub_department) {
                    return $application->user->sub_department->name.' ('.$application->user->sub_department->code.')';
                }

                return '';
            },
            'category' => function (LeaveApplication $application) {
                if ($application->user && is_array($application->user->category_id)) {
                    $categories = Category::whereIn('id', $application->user->category_id)->get();

                    $categoryStrings = $categories->map(function ($category) {
                        return $category->name.' ('.$category->code.')';
                    });

                    return $categoryStrings->implode(', ');
                } elseif ($application->user && $application->user->category) {
                    return $application->user->category->name.' ('.$application->user->category->code.')';
                }

                return '';
            },
            'sub_category' => function (LeaveApplication $application) {
                if ($application->user && is_array($application->user->sub_category_id)) {
                    $sub_categories = SubCategory::whereIn('id', $application->user->sub_category_id)->get();

                    $subCategoryStrings = $sub_categories->map(function ($sub_category) {
                        return $sub_category->name.' ('.$sub_category->code.')';
                    });

                    return $subCategoryStrings->implode(', ');
                } elseif ($application->user && $application->user->sub_category) {
                    return $application->user->sub_category->name.' ('.$application->user->sub_category->code.')';
                }

                return '';
            },
            'user' => function (LeaveApplication $application) {
                return $application->user ? $application->user->code : '';
            },
        ];
        $group_by_array = [];
        foreach ($this->monthlyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }

        $user_group = fn (LeaveApplication $application) => $application->user->code;
        $result = $result->groupBy($user_group);

        $datacsv = $this->renderCSVTree($result);

        $csv = $datacsv;

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
        $datacsv = $this->renderCSVTree($result);

        // Add title header rows
        $reportTitle = 'Leave Information Report';
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

                        // Style header rows
                        if (is_array($row) && isset($row[0]) && $rowIndex === 2) {
                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('E2EFDA'); // Light green background
                        }
                        // Style Grand Total rows
                        elseif (is_array($row) && isset($row[0]) && strpos($row[0], 'Grand Total') !== false) {
                            // Merge the Grand Total cells across multiple columns
                            $lastCol = chr(64 + min(count($row), 26)); // Convert to letter (A, B, C...)
                            $sheet->mergeCells('A'.($rowIndex + 1).':B'.($rowIndex + 1));
                            $sheet->getStyle('A'.($rowIndex + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FCE4D6'); // Light orange background
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
                        'B' => 25,
                        'C' => 20,
                        'D' => 20,
                        'E' => 15,
                        'F' => 15,
                        'G' => 25,
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

    public function renderCSVTree($result, $level = 1)
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
                $heading[] = LeaveInfoReportColumns::{$column}->getLabel();
            }
            $csv[] = $heading;

            foreach ($all_dates as $date) {
                $leaveRecord = $records->first(function ($record) use ($date) {
                    return $record->application_date->format('Y-m-d') === $date->format('Y-m-d');
                });

                if ($leaveRecord) {
                    $csv[] = $this->renderDetailedCSVColumns($leaveRecord);
                } else {
                    $csv[] = [$date->format('d/m/Y')];
                }
            }

            $totalRow = array_fill(0, count($this->selected_columns) - 2, '');
            // $totalRow[0] = "Grand Total";
            // $totalRow[count($this->selected_columns) - 1] = sprintf('%02d:%02d', floor($this->totalLate / 60), $this->totalLate % 60);
            $csv[] = $totalRow;

            // $csv[] = [""];

            // $csv[] = [""];
        }

        return $csv;
    }

    public function renderDetailedCSVColumns($application)
    {
        $row = [];
        foreach ($this->selected_columns as $column) {
            switch ($column) {
                case 'date':
                    $row[] = $application->application_date->format('d-m-Y');
                    break;
                case 'emp_code':
                    $row[] = $application->user->code ? $application->user->code : '';
                    break;
                case 'emp_name':
                    $row[] = $application->user->name ? $application->user->name : '';
                    break;
                case 'department_name':
                    $row[] = is_array($application->trx_user->department_id) ? collect(Department::whereIn('id', $application->trx_user->department_id)->get())->pluck('name')->implode(', ') : ($application->trx_user->department ? $application->trx_user->department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($application->trx_user->department_id) ? collect(Department::whereIn('id', $application->trx_user->department_id)->get())->pluck('code')->implode(', ') : ($application->trx_user->department ? $application->trx_user->department->code : '');
                    break;
                case 'category_name':
                    $row[] = is_array($application->trx_user->category_id) ? collect(Category::whereIn('id', $application->trx_user->category_id)->get())->pluck('name')->implode(', ') : ($application->trx_user->category ? $application->trx_user->category->name : '');
                    break;
                case 'category_code':
                    $row[] = is_array($application->trx_user->category_id) ? collect(Category::whereIn('id', $application->trx_user->category_id)->get())->pluck('code')->implode(', ') : ($application->trx_user->category ? $application->trx_user->category->code : '');
                    break;
                case 'leave_type':
                    $row[] = $application->leave_type ? $application->leave_type->code : '';
                    break;
                case 'from_date':
                    $row[] = $application->from_date ? Carbon::parse($application->from_date)->format('d-m-Y') : '';
                    break;
                case 'to_date':
                    $row[] = $application->to_date ? Carbon::parse($application->to_date)->format('d-m-Y') : '';
                    break;
                case 'leave_count':
                    $row[] = $application->leave_count ? $application->leave_count : '';
                    break;
                case 'status':
                    $row[] = $application->approval_status() ? $application->approval_status()?->first()?->approval_status_details()?->first()?->approval_status : '';
                    break;
                case 'leave_reason':
                    $row[] = $application->leave_reason ? $application->leave_reason->leave_reason : '';
                    break;
                case 'reason_explanation':
                    $row[] = $application->reason_explanation ? $application->reason_explanation : '';
                    break;
                default:
                    $row[] = 'Unknown Column';
                    break;
            }
        }

        return $row;
    }
}
