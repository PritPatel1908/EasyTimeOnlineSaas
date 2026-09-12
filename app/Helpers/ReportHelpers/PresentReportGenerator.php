<?php

namespace App\Helpers\ReportHelpers;

use App\Enums\ReportColumns\PresentReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\DailyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DailyReport;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PresentReportGenerator
{
    public static $overtime_early = 0;

    public static $overtime_late = 0;

    public static $overtime = null;

    protected DailyReport $dailyReport;

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

    public function __construct(DailyReport $dailyReport)
    {
        $this->dailyReport = $dailyReport;
        $this->year = $this->dailyReport->from_datetime->year;
    }

    public function generatePresentReport(): void
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
        $heading = [];
        foreach ($this->selected_columns as $column) {
            // map with enum get lable
            $heading[] = PresentReportColumns::{$column}->getLabel();
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
        // dd("ReportingCSV", $result);
    }

    public function generateDetailedXlsxReport($result)
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

        $heading = [];
        foreach ($this->selected_columns as $column) {
            // map with enum get lable
            $heading[] = PresentReportColumns::{$column}->getLabel();
        }

        $datacsv = $this->renderCSVTree($result);

        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Add report title and date period
        $sheet->mergeCells('A1:'.$this->getColumnLetter(count($heading)).'1');
        $sheet->setCellValue('A1', 'Present Report');
        $sheet->mergeCells('A2:'.$this->getColumnLetter(count($heading)).'2');
        $sheet->setCellValue('A2', 'Period: '.$this->dailyReport->from_datetime->format('d/m/Y').' to '.$this->dailyReport->to_datetime->format('d/m/Y'));

        // Style the header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
        ];

        $sheet->getStyle('A1:'.$this->getColumnLetter(count($heading)).'2')->applyFromArray($headerStyle);

        // Add column headers at row 4
        $columnIndex = 1;
        foreach ($heading as $head) {
            $sheet->setCellValueByColumnAndRow($columnIndex, 4, $head);
            $columnIndex++;
        }

        // Style the column headers
        $columnHeaderStyle = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D9D9D9',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A4:'.$this->getColumnLetter(count($heading)).'4')->applyFromArray($columnHeaderStyle);

        // Add data starting from row 5
        $rowIndex = 5;
        foreach ($datacsv as $row) {
            $columnIndex = 1;
            foreach ($row as $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $value);
                $columnIndex++;
            }
            $rowIndex++;
        }

        // Auto-size columns
        foreach (range('A', $this->getColumnLetter(count($heading))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set borders for all data
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A4:'.$this->getColumnLetter(count($heading)).($rowIndex - 1))->applyFromArray($borderStyle);

        // Save the Excel file
        $file_name = 'report_'.$this->dailyReport->id.'.xlsx';
        $file_path = storage_path('app/public/reports/'.$file_name);
        $directory = dirname($file_path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
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
     * Convert column number to Excel column letter
     */
    private function getColumnLetter($columnNumber)
    {
        $columnLetter = '';
        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $columnLetter = chr(65 + $modulo).$columnLetter;
            $columnNumber = (int) (($columnNumber - $modulo) / 26);
        }

        return $columnLetter;
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
