<?php

namespace App\Helpers\ReportHelpers;

use App\Enums\ReportColumns\InOutReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\DailyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DailyReport;
use App\Models\Tenant\Department;
use App\Models\Tenant\InOutMuster;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InOutReportGenerator
{
    public static $overtime_early = 0;

    public static $overtime_late = 0;

    public static $overtime = null;

    protected DailyReport $dailyReport;

    protected $in_out_duration;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        InOutReportColumns::date->value => 1,
        InOutReportColumns::code->value => 2,
        InOutReportColumns::name->value => 3,
        InOutReportColumns::location_name->value => 4,
        InOutReportColumns::location_code->value => 5,
        InOutReportColumns::company_name->value => 6,
        InOutReportColumns::company_code->value => 7,
        InOutReportColumns::department_name->value => 8,
        InOutReportColumns::department_code->value => 9,
        InOutReportColumns::sub_department_name->value => 10,
        InOutReportColumns::sub_department_code->value => 11,
        InOutReportColumns::category_name->value => 12,
        InOutReportColumns::category_code->value => 13,
        InOutReportColumns::sub_category_name->value => 14,
        InOutReportColumns::sub_category_code->value => 15,
        InOutReportColumns::shift->value => 16,
        InOutReportColumns::shift_in_time->value => 17,
        InOutReportColumns::shift_out_time->value => 18,
        InOutReportColumns::night_shift->value => 19,
        InOutReportColumns::in_out_time->value => 20,
        InOutReportColumns::actual_working_hrs->value => 21,
        InOutReportColumns::break_hrs->value => 22,
        InOutReportColumns::total_hrs->value => 23,
        InOutReportColumns::overtime->value => 24,
        InOutReportColumns::status->value => 25,
        // InOutReportColumns::in_time->value => 20,
        // InOutReportColumns::out_time->value => 21,
        // InOutReportColumns::actual_working_hrs->value => 22,
        // InOutReportColumns::break_hrs->value => 23,
        // InOutReportColumns::total_hrs->value => 24,
        // InOutReportColumns::overtime->value => 25,
        // InOutReportColumns::status->value => 26,
    ];

    public function __construct(DailyReport $dailyReport)
    {
        $this->dailyReport = $dailyReport;
        $this->year = $this->dailyReport->from_datetime->year;
    }

    public function generateInOutMusterReport(): void
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

        // Get not null in shift records
        // $attendanceDetails->where('shift_id', '!=', null);

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
            $heading[] = InOutReportColumns::{$column}->getLabel();
        }
        $datacsv = $this->renderCSVTree($result);

        // Add title header rows first
        $reportTitle = 'In-Out Muster Report';
        $dateRange = 'Report Period: '.$this->dailyReport->from_datetime->format('d/m/Y').' - '.$this->dailyReport->to_datetime->format('d/m/Y');

        // Create the initial array with title and headers
        $csv = [
            [], // Empty row
            [$dateRange],
            [$reportTitle],
            $heading, // Headers now come BEFORE data
        ];

        // Get data rows and append them AFTER the headers
        $csv = array_merge($csv, $datacsv);

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
            $heading[] = InOutReportColumns::{$column}->getLabel();
        }

        $datacsv = $this->renderCSVTree($result);

        // Add title header rows first
        $reportTitle = 'In-Out Muster Report';
        $dateRange = 'Report Period: '.$this->dailyReport->from_datetime->format('d/m/Y').' - '.$this->dailyReport->to_datetime->format('d/m/Y');

        // Create the initial array with title and headers
        $csv = [
            [], // Empty row
            [$dateRange],
            [$reportTitle],
            $heading, // Headers now come BEFORE data
        ];

        // Get data rows and append them AFTER the headers
        $csv = array_merge($csv, $datacsv);

        // Create Excel file using PhpSpreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Add data to spreadsheet
        foreach ($csv as $rowIndex => $row) {
            foreach ($row as $columnIndex => $cellValue) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 1, $cellValue);
            }
        }

        // Apply styling
        // Title styling
        $sheet->mergeCells('A3:'.$this->columnIndexToLetter(count($heading)).'3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Date range styling
        $sheet->mergeCells('A2:'.$this->columnIndexToLetter(count($heading)).'2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row styling
        $headerRowIndex = 4;
        $headerRange = 'A'.$headerRowIndex.':'.$this->columnIndexToLetter(count($heading)).$headerRowIndex;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Set column widths
        foreach (range(0, count($heading) - 1) as $columnID) {
            $sheet->getColumnDimensionByColumn($columnID + 1)->setAutoSize(true);
        }

        // Auto filter for headers
        $lastRow = count($csv);
        $sheet->setAutoFilter($headerRange);

        // Set print area
        $lastColumn = $this->columnIndexToLetter(count($heading));
        $sheet->getPageSetup()->setPrintArea('A1:'.$lastColumn.$lastRow);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // Repeat header rows on each page when printing
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRowIndex, $headerRowIndex);

        // Set file name and path
        $file_name = 'report_'.$this->dailyReport->id.'.xlsx';
        $file_path = storage_path('app/public/reports/'.$file_name);
        $directory = dirname($file_path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save the spreadsheet
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

    // Helper method to convert column index to Excel column letter
    private function columnIndexToLetter($columnIndex)
    {
        $columnLetter = '';
        while ($columnIndex > 0) {
            $modulo = ($columnIndex - 1) % 26;
            $columnLetter = chr(65 + $modulo).$columnLetter;
            $columnIndex = (int) (($columnIndex - $modulo) / 26);
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
                case 'in_out_time':
                    $this->in_out_duration = 0;
                    $punches = InOutMuster::where('user_id', $attendance->user->id)
                        ->whereDate('date', $attendance->date->format('Y-m-d'))
                        // ->whereNotBetween('datetime', [$breakStart, $breakEnd])
                        ->get(['in_time', 'out_time']);

                    $formattedTimes = [];

                    foreach ($punches as $punch) {
                        if ($attendance->shift != null) {
                            // Fix the formatting to properly display IN and OUT times
                            $inTime = $punch->in_time ? 'IN: '.$punch->in_time->format('H:i') : 'IN: --:--';
                            $outTime = $punch->out_time ? 'OUT: '.$punch->out_time->format('H:i') : 'OUT: --:--';
                            $formattedTimes[] = $inTime.' - '.$outTime;

                            // Calculate duration in minutes and add to in_out_duration
                            if ($punch->in_time && $punch->out_time) {
                                $this->in_out_duration += $punch->in_time->diffInMinutes($punch->out_time);
                            }
                        }
                    }
                    if ($formattedTimes != null) {
                        $row[] = implode('&#10;', $formattedTimes);
                    } else {
                        $row[] = '';
                    }
                    break;
                case 'actual_working_hrs':
                    $actualHours = '00:00';
                    if ($this->in_out_duration !== null && $this->in_out_duration !== 0) {
                        $actualHours = sprintf('%02d:%02d', floor($this->in_out_duration / 60), $this->in_out_duration % 60);
                    }
                    $row[] = $actualHours;
                    break;
                case 'break_hrs':
                    // $breakHours = '00:00';
                    if ($attendance->in_time !== null && $attendance->out_time !== null) {
                        $total_diff = $attendance->in_time->diffInMinutes($attendance->out_time);
                        $diff = $total_diff - $this->in_out_duration;
                        $breakHours = $diff > 0 ? sprintf('%02d:%02d', floor($diff / 60), $diff % 60) : '00:00';
                    } else {
                        $breakHours = '';
                    }
                    $row[] = $breakHours;
                    break;
                case 'total_hrs':
                    $total_diff = 0;
                    if ($attendance->in_time && $attendance->out_time) {
                        $total_diff = $attendance->in_time->diffInMinutes($attendance->out_time);
                    }
                    $row[] = $total_diff ? sprintf('%02d:%02d', floor($total_diff / 60), $total_diff % 60) : '';
                    break;
                case 'total_hrs':
                    $total_diff = 0;
                    if ($attendance->in_time && $attendance->out_time) {
                        $total_diff = $attendance->in_time->diffInMinutes($attendance->out_time);
                    }
                    $row[] = $total_diff ? sprintf('%02d:%02d', floor($total_diff / 60), $total_diff % 60) : 'No';
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
