<?php

namespace App\Helpers\MonthlyReportHelpers;

use App\Enums\ReportColumns\ManualPunchReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\MonthlyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\MonthlyReport;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ManualPunchReportGenerator
{
    protected MonthlyReport $monthlyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        ManualPunchReportColumns::date->value => 1,
        ManualPunchReportColumns::time->value => 2,
        ManualPunchReportColumns::code->value => 3,
        ManualPunchReportColumns::name->value => 4,
        ManualPunchReportColumns::location_name->value => 5,
        ManualPunchReportColumns::location_code->value => 6,
        ManualPunchReportColumns::company_name->value => 7,
        ManualPunchReportColumns::company_code->value => 8,
        ManualPunchReportColumns::department_name->value => 9,
        ManualPunchReportColumns::department_code->value => 10,
        ManualPunchReportColumns::sub_department_name->value => 11,
        ManualPunchReportColumns::sub_department_code->value => 12,
        ManualPunchReportColumns::category_name->value => 13,
        ManualPunchReportColumns::category_code->value => 14,
        ManualPunchReportColumns::sub_category_name->value => 15,
        ManualPunchReportColumns::sub_category_code->value => 16,
        ManualPunchReportColumns::punch_type->value => 17,
        ManualPunchReportColumns::reason->value => 18,
    ];

    public function __construct(MonthlyReport $monthlyReport)
    {
        $this->monthlyReport = $monthlyReport;
        $this->year = $this->monthlyReport->from_datetime->year;
    }

    public function generateManualPunchReport(): void
    {
        $this->selected_columns = $this->monthlyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        // Generate transaction report
        $manualPunches = ManualPunch::with('Users');

        // Filter By Date
        $manualPunches->whereBetween(
            'created_at',
            [
                $this->monthlyReport->from_datetime,
                $this->monthlyReport->to_datetime,
            ]
        );

        // Filter By User Type
        $manualPunches->whereHas('Users', function ($query) {
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
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($manualPunches));
        $result = $manualPunches->get();
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
            'company' => function (ManualPunch $manual_punch) {
                if ($manual_punch->Users->first() && is_array($manual_punch->Users->first()->company_id)) {
                    $companies = Company::whereIn('id', $manual_punch->Users->first()->sub_category_id)->get();

                    $companyStrings = $companies->map(function ($company) {
                        return $company->name.' ('.$company->code.')';
                    });

                    return $companyStrings->implode(', ');
                } elseif ($manual_punch->Users->first() && $manual_punch->Users->first()->company_id) {
                    return $manual_punch->Users->first()->company->name.' ('.$manual_punch->Users->first()->company->code.')';
                }

                return '';
            },
            'department' => function (ManualPunch $manual_punch) {
                if ($manual_punch->Users->first() && is_array($manual_punch->Users->first()->department_id)) {
                    $departments = Department::whereIn('id', $manual_punch->Users->first()->department_id)->get();

                    $departmentStrings = $departments->map(function ($department) {
                        return $department->name.' ('.$department->code.')';
                    });

                    return $departmentStrings->implode(', ');
                } elseif ($manual_punch->Users->first() && $manual_punch->Users->first()->department_id) {
                    return $manual_punch->Users->first()->department->name.' ('.$manual_punch->Users->first()->department->code.')';
                }

                return '';
            },
            'sub_department' => function (ManualPunch $manual_punch) {
                if ($manual_punch->Users->first() && is_array($manual_punch->Users->first()->sub_department_id)) {
                    $sub_departments = SubDepartment::whereIn('id', $manual_punch->Users->first()->sub_department_id)->get();

                    $subDepartmentStrings = $sub_departments->map(function ($sub_department) {
                        return $sub_department->name.' ('.$sub_department->code.')';
                    });

                    return $subDepartmentStrings->implode(', ');
                } elseif ($manual_punch->Users->first() && $manual_punch->Users->first()->sub_department_id) {
                    return $manual_punch->Users->first()->sub_department->name.' ('.$manual_punch->Users->first()->sub_department->code.')';
                }

                return '';
            },
            'category' => function (ManualPunch $manual_punch) {
                if ($manual_punch->Users->first() && is_array($manual_punch->Users->first()->categoty_id)) {
                    $categories = Category::whereIn('id', $manual_punch->Users->first()->category_id)->get();

                    $categoryStrings = $categories->map(function ($category) {
                        return $category->name.' ('.$category->code.')';
                    });

                    return $categoryStrings->implode(', ');
                } elseif ($manual_punch->Users->first() && $manual_punch->Users->first()->category_id) {
                    return $manual_punch->Users->first()->category->name.' ('.$manual_punch->Users->first()->category->code.')';
                }

                return '';
            },
            'sub_category' => function (ManualPunch $manual_punch) {
                if ($manual_punch->Users->first() && is_array($manual_punch->Users->first()->sub_categoty_id)) {
                    $sub_categories = SubCategory::whereIn('id', $manual_punch->Users->first()->sub_category_id)->get();

                    $subCategoryStrings = $sub_categories->map(function ($sub_category) {
                        return $sub_category->name.' ('.$sub_category->code.')';
                    });

                    return $subCategoryStrings->implode(', ');
                } elseif ($manual_punch->Users->first() && $manual_punch->Users->first()->sub_category_id) {
                    return $manual_punch->Users->first()->sub_category->name.' ('.$manual_punch->Users->first()->sub_category->code.')';
                }

                return '';
            },
            'user' => function (ManualPunch $manual_punch) {
                return $manual_punch->Users ? $manual_punch->Users->first()->code : '';
            },
        ];
        $group_by_array = [];
        foreach ($this->monthlyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }
        $heading = [];
        foreach ($this->selected_columns as $column) {
            // map with enum get lable
            $heading[] = ManualPunchReportColumns::{$column}->getLabel();
        }
        $datacsv = $this->renderCSVTree($result);

        $csv = array_merge([$heading], $datacsv);

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
        $datacsv = $this->renderCSVTree($result);

        // Add title header rows
        $reportTitle = 'Manual Punch Report';
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
                    $sheet->mergeCells('A1:G1');
                    $sheet->mergeCells('A2:G2');
                    $sheet->getStyle('A1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

                        // Style headers
                        if (is_array($row) && isset($row[0]) && $rowIndex === 2) {
                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('E2EFDA'); // Light green background
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
                        'E' => 25, // For reason column
                        'F' => 15,
                        'G' => 15,
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
        $raw_number = 0;
        $columns = count($this->selected_columns);

        foreach ($result as $key => $value) {
            $row = [];
            // check value is collection of array or object
            if ($value instanceof Collection) {
                $row[] = $key;
                for ($i = 0; $i < $columns - $level; $i++) {
                    $row[] = '';
                }
                $csv[] = $row;
                $csv = array_merge($csv, $this->renderCSVTree($value, $level + 1));
            } elseif ($value instanceof ManualPunch) {
                $csv[] = $this->renderDetailedCSVColumns($value);
            }
        }

        return $csv;
    }

    public function renderDetailedCSVColumns($value)
    {
        $row = [];
        foreach ($this->selected_columns as $column) {
            switch ($column) {
                case 'date':
                    $row[] = $value->punch_date->format('d/m/Y');
                    break;
                case 'time':
                    $row[] = $value->punch_time->format('H:i:s');
                    break;
                case 'code':
                    $row[] = $value->Users->first()->code ?? '';
                    break;
                case 'name':
                    $row[] = $value->Users->first()->name ?? '';
                    break;
                case 'location_name':
                    $row[] = is_array($value->location_id) ? collect(Location::whereIn('id', $value->location_id)->get())->pluck('name')->implode(', ') : ($value->location ? $value->location->name : '');
                    break;
                case 'location_code':
                    $row[] = is_array($value->location_id) ? collect(Location::whereIn('id', $value->location_id)->get())->pluck('code')->implode(', ') : ($value->location ? $value->location->name : '');
                    break;
                case 'company_name':
                    $row[] = is_array($value->company_id) ? collect(Company::whereIn('id', $value->company_id)->get())->pluck('name')->implode(', ') : ($value->company ? $value->company->name : '');
                    break;
                case 'company_code':
                    $row[] = is_array($value->company_id) ? collect(Company::whereIn('id', $value->company_id)->get())->pluck('code')->implode(', ') : ($value->company ? $value->company->code : '');
                    break;
                case 'department_name':
                    $row[] = is_array($value->Users->first()->department_id) ? collect(Department::whereIn('id', $value->Users->first()->department_id)->get())->pluck('name')->implode(', ') : ($value->Users->first()->department ? $value->Users->first()->department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($value->Users->first()->department_id) ? collect(Department::whereIn('id', $value->Users->first()->department_id)->get())->pluck('code')->implode(', ') : ($value->Users->first()->department ? $value->Users->first()->department->code : '');
                    break;
                case 'sub_department_name':
                    $row[] = is_array($value->Users->first()->sub_department_id) ? collect(SubDepartment::whereIn('id', $value->Users->first()->sub_department_id)->get())->pluck('name')->implode(', ') : ($value->Users->first()->sub_department ? $value->Users->first()->sub_department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($value->Users->first()->sub_department_id) ? collect(SubDepartment::whereIn('id', $value->Users->first()->sub_department_id)->get())->pluck('code')->implode(', ') : ($value->Users->first()->sub_department ? $value->Users->first()->sub_department->code : '');
                    break;
                case 'category_name':
                    $row[] = is_array($value->Users->first()->category_id) ? collect(Category::whereIn('id', $value->Users->first()->category_id)->get())->pluck('name')->implode(', ') : ($value->Users->first()->category ? $value->Users->first()->category->name : '');
                    break;
                case 'category_code':
                    $row[] = is_array($value->Users->first()->category_id) ? collect(Category::whereIn('id', $value->Users->first()->category_id)->get())->pluck('code')->implode(', ') : ($value->Users->first()->category ? $value->Users->first()->category->code : '');
                    break;
                case 'sub_category_name':
                    $row[] = is_array($value->Users->first()->sub_category_id) ? collect(SubCategory::whereIn('id', $value->Users->first()->sub_category_id)->get())->pluck('name')->implode(', ') : ($value->Users->first()->sub_category ? $value->Users->first()->sub_category->name : '');
                    break;
                case 'sub_category_code':
                    $row[] = is_array($value->Users->first()->sub_category_id) ? collect(SubCategory::whereIn('id', $value->Users->first()->sub_category_id)->get())->pluck('code')->implode(', ') : ($value->Users->first()->sub_category ? $value->Users->first()->sub_category->code : '');
                    break;
                case 'punch_type':
                    $row[] = ($value->punch_type != null ? ($value->punch_type == '1' ? 'In Punch' : 'Out Punch') : '');
                    break;
                case 'reason':
                    $row[] = $value->reason ? $value->reason : '';
                    break;
                default:
                    $row[] = 'Unknown Column';
                    break;
            }
        }

        return $row;
    }
}
