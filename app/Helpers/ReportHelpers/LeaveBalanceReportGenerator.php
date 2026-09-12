<?php

namespace App\Helpers\ReportHelpers;

use App\Enums\ReportColumns\LeaveBalanceReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\DailyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DailyReport;
use App\Models\Tenant\Department;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeaveBalanceReportGenerator
{
    protected DailyReport $dailyReport;

    public $totalRow = [];

    public $year;

    public $selected_columns = [];

    private $orderMap = [
        LeaveBalanceReportColumns::date->value => 1,
        LeaveBalanceReportColumns::emp_code->value => 2,
        LeaveBalanceReportColumns::emp_name->value => 3,
        LeaveBalanceReportColumns::department_name->value => 4,
        LeaveBalanceReportColumns::department_code->value => 5,
        LeaveBalanceReportColumns::category_name->value => 6,
        LeaveBalanceReportColumns::category_code->value => 7,
        LeaveBalanceReportColumns::leave_type->value => 8,
        LeaveBalanceReportColumns::opening_balance->value => 9,
        LeaveBalanceReportColumns::credit->value => 10,
        LeaveBalanceReportColumns::debit->value => 11,
        LeaveBalanceReportColumns::balance->value => 12,
        LeaveBalanceReportColumns::remarks->value => 13,
    ];

    public function __construct(DailyReport $dailyReport)
    {
        $this->dailyReport = $dailyReport;
        $this->year = $this->dailyReport->from_datetime->year;
    }

    public function generateLeaveBalanceReport(): void
    {
        $this->selected_columns = $this->dailyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        // Generate attendance report
        $transactionDetails = Transaction::Year($this->year)
            ->with('trx_user');
        // $transactionDetails->with('trx_user.department');
        // $transactionDetails->with('trx_user.sub_department');
        // $transactionDetails->with('trx_user.company');
        // $transactionDetails->with('trx_user.location');
        // $transactionDetails->with('trx_user.category');
        // $transactionDetails->with('trx_user.sub_category');

        // Filter By Date
        $transactionDetails->whereBetween(
            'trx_datetime',
            [
                $this->dailyReport->from_datetime,
                $this->dailyReport->to_datetime,
            ]
        );

        // Filter By User Type
        $transactionDetails->whereHas('user', function ($query) {
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
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($transactionDetails));
        $result = $transactionDetails->get();
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
            'company' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->company_id)) {
                    $companies = Company::whereIn('id', $transaction->user->company_id)->get();

                    $companyStrings = $companies->map(function ($company) {
                        return $company->name.' ('.$company->code.')';
                    });

                    return $companyStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->company) {
                    return $transaction->user->company->name.' ('.$transaction->user->compnay->code.')';
                }

                return '';
            },
            'department' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->department_id)) {
                    $departments = Department::whereIn('id', $transaction->user->department_id)->get();

                    $departmentStrings = $departments->map(function ($department) {
                        return $department->name.' ('.$department->code.')';
                    });

                    return $departmentStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->department) {
                    return $transaction->user->department->name.' ('.$transaction->user->department->code.')';
                }

                return '';
            },
            'sub_department' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->sub_department_id)) {
                    $sub_departments = SubDepartment::whereIn('id', $transaction->user->sub_department_id)->get();

                    $subDepartmentStrings = $sub_departments->map(function ($sub_department) {
                        return $sub_department->name.' ('.$sub_department->code.')';
                    });

                    return $subDepartmentStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->sub_department) {
                    return $transaction->user->sub_department->name.' ('.$transaction->user->sub_department->code.')';
                }

                return '';
            },
            'category' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->category_id)) {
                    $categories = Category::whereIn('id', $transaction->user->category_id)->get();

                    $categoryStrings = $categories->map(function ($category) {
                        return $category->name.' ('.$category->code.')';
                    });

                    return $categoryStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->category) {
                    return $transaction->user->category->name.' ('.$transaction->user->category->code.')';
                }

                return '';
            },
            'sub_category' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->sub_category_id)) {
                    $sub_categories = SubCategory::whereIn('id', $transaction->user->sub_category_id)->get();

                    $subCategoryStrings = $sub_categories->map(function ($sub_category) {
                        return $sub_category->name.' ('.$sub_category->code.')';
                    });

                    return $subCategoryStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->sub_category) {
                    return $transaction->user->sub_category->name.' ('.$transaction->user->sub_category->code.')';
                }

                return '';
            },
            'user' => function (Transaction $transaction) {
                return $transaction->user ? $transaction->user->code : '';
            },
        ];
        $group_by_array = [];
        foreach ($this->dailyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }
        $result = $result->sortBy('trx_user_id');
        // $result = $result->sortBy('trx_datetime');
        $heading = [];
        foreach ($this->selected_columns as $column) {
            // map with enum get lable
            $heading[] = LeaveBalanceReportColumns::{$column}->getLabel();
        }
        $datacsv = $this->renderCSVTree($result);

        $csv = array_merge([$heading], $datacsv);

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
            } elseif ($value instanceof Transaction) {
                $csv[] = $this->renderDetailedCSVColumns($value);
            }
        }

        return $csv;
    }

    public function renderDetailedCSVColumns($transaction)
    {
        $row = [];
        foreach ($this->selected_columns as $column) {
            switch ($column) {
                case 'date':
                    $row[] = $transaction->trx_datetime;
                    break;
                case 'emp_code':
                    $row[] = $transaction->trx_user->code ? $transaction->trx_user->code : '';
                    break;
                case 'emp_name':
                    $row[] = $transaction->trx_user->name ? $transaction->trx_user->name : '';
                    break;
                case 'department_name':
                    $row[] = is_array($transaction->trx_user->department_id) ? collect(Department::whereIn('id', $transaction->trx_user->department_id)->get())->pluck('name')->implode(', ') : ($transaction->trx_user->department ? $transaction->trx_user->department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($transaction->trx_user->department_id) ? collect(Department::whereIn('id', $transaction->trx_user->department_id)->get())->pluck('code')->implode(', ') : ($transaction->trx_user->department ? $transaction->trx_user->department->code : '');
                    break;
                case 'category_name':
                    $row[] = is_array($transaction->trx_user->category_id) ? collect(Category::whereIn('id', $transaction->trx_user->category_id)->get())->pluck('name')->implode(', ') : ($transaction->trx_user->category ? $transaction->trx_user->category->name : '');
                    break;
                case 'category_code':
                    $row[] = is_array($transaction->trx_user->category_id) ? collect(Category::whereIn('id', $transaction->trx_user->category_id)->get())->pluck('code')->implode(', ') : ($transaction->trx_user->category ? $transaction->trx_user->category->code : '');
                    break;
                case 'leave_type':
                    if ($transaction->referenceable_type == 'App\Models\Tenant\GradeWiseMonthlyLeaveDetail' || $transaction->referenceable_type == 'App\Models\Tenant\GradeWiseYearlyLeaveDetail') {
                        $grage_wise_leave = $transaction->referenceable_type::where('id', $transaction->referenceable_id)->first();
                    }
                    $row[] = $grage_wise_leave != null ? $grage_wise_leave->leave_type->code : '';
                    break;
                case 'opening_balance':
                    $row[] = $transaction->opening_balance ? $transaction->opening_balance : '';
                    break;
                case 'credit':
                    $row[] = $transaction->credit ? $transaction->credit : '';
                    break;
                case 'debit':
                    $row[] = $transaction->debit ? $transaction->debit : '';
                    break;
                case 'balance':
                    $row[] = $transaction->balance ? $transaction->balance : '';
                    break;
                case 'remarks':
                    $row[] = $transaction->remarks ? $transaction->remarks : '';
                    break;
                default:
                    $row[] = 'Unknown Column';
                    break;
            }
        }

        return $row;
    }

    public function generateDetailedXlsxReport($result)
    {
        $name_to_column = [
            'company' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->company_id)) {
                    $companies = Company::whereIn('id', $transaction->user->company_id)->get();

                    $companyStrings = $companies->map(function ($company) {
                        return $company->name.' ('.$company->code.')';
                    });

                    return $companyStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->company) {
                    return $transaction->user->company->name.' ('.$transaction->user->compnay->code.')';
                }

                return '';
            },
            'department' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->department_id)) {
                    $departments = Department::whereIn('id', $transaction->user->department_id)->get();

                    $departmentStrings = $departments->map(function ($department) {
                        return $department->name.' ('.$department->code.')';
                    });

                    return $departmentStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->department) {
                    return $transaction->user->department->name.' ('.$transaction->user->department->code.')';
                }

                return '';
            },
            'sub_department' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->sub_department_id)) {
                    $sub_departments = SubDepartment::whereIn('id', $transaction->user->sub_department_id)->get();

                    $subDepartmentStrings = $sub_departments->map(function ($sub_department) {
                        return $sub_department->name.' ('.$sub_department->code.')';
                    });

                    return $subDepartmentStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->sub_department) {
                    return $transaction->user->sub_department->name.' ('.$transaction->user->sub_department->code.')';
                }

                return '';
            },
            'category' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->category_id)) {
                    $categories = Category::whereIn('id', $transaction->user->category_id)->get();

                    $categoryStrings = $categories->map(function ($category) {
                        return $category->name.' ('.$category->code.')';
                    });

                    return $categoryStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->category) {
                    return $transaction->user->category->name.' ('.$transaction->user->category->code.')';
                }

                return '';
            },
            'sub_category' => function (Transaction $transaction) {
                if ($transaction->user && is_array($transaction->user->sub_category_id)) {
                    $sub_categories = SubCategory::whereIn('id', $transaction->user->sub_category_id)->get();

                    $subCategoryStrings = $sub_categories->map(function ($sub_category) {
                        return $sub_category->name.' ('.$sub_category->code.')';
                    });

                    return $subCategoryStrings->implode(', ');
                } elseif ($transaction->user && $transaction->user->sub_category) {
                    return $transaction->user->sub_category->name.' ('.$transaction->user->sub_category->code.')';
                }

                return '';
            },
            'user' => function (Transaction $transaction) {
                return $transaction->user ? $transaction->user->code : '';
            },
        ];
        $group_by_array = [];
        foreach ($this->dailyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }
        $result = $result->sortBy('trx_user_id');

        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Add report title and date period in header
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Leave Balance Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $dateRange = 'Period: '.$this->dailyReport->from_datetime->format('d-m-Y').' to '.$this->dailyReport->to_datetime->format('d-m-Y');
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', $dateRange);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set headers
        $columnIndex = 'A';
        $rowIndex = 4;

        foreach ($this->selected_columns as $column) {
            $sheet->setCellValue($columnIndex.$rowIndex, LeaveBalanceReportColumns::{$column}->getLabel());
            $sheet->getStyle($columnIndex.$rowIndex)->getFont()->setBold(true);
            $columnIndex++;
        }

        $rowIndex++;

        // Prepare data for Excel
        $dataArray = $this->prepareXlsxData($result);

        // Add data to sheet
        foreach ($dataArray as $row) {
            $columnIndex = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($columnIndex.$rowIndex, $value);
                $columnIndex++;
            }
            $rowIndex++;
        }

        // Auto size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save file
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

    public function prepareXlsxData($result, $level = 1)
    {
        $data = [];

        foreach ($result as $key => $value) {
            if ($value instanceof Collection) {
                $row = array_fill(0, count($this->selected_columns), '');
                $row[0] = $key;
                $data[] = $row;
                $data = array_merge($data, $this->prepareXlsxData($value, $level + 1));
            } elseif ($value instanceof Transaction) {
                $data[] = $this->renderDetailedXlsxColumns($value);
            }
        }

        return $data;
    }

    public function renderDetailedXlsxColumns($transaction)
    {
        $row = [];
        foreach ($this->selected_columns as $column) {
            switch ($column) {
                case 'date':
                    $row[] = $transaction->trx_datetime;
                    break;
                case 'emp_code':
                    $row[] = $transaction->trx_user->code ? $transaction->trx_user->code : '';
                    break;
                case 'emp_name':
                    $row[] = $transaction->trx_user->name ? $transaction->trx_user->name : '';
                    break;
                case 'department_name':
                    $row[] = is_array($transaction->trx_user->department_id) ? collect(Department::whereIn('id', $transaction->trx_user->department_id)->get())->pluck('name')->implode(', ') : ($transaction->trx_user->department ? $transaction->trx_user->department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($transaction->trx_user->department_id) ? collect(Department::whereIn('id', $transaction->trx_user->department_id)->get())->pluck('code')->implode(', ') : ($transaction->trx_user->department ? $transaction->trx_user->department->code : '');
                    break;
                case 'category_name':
                    $row[] = is_array($transaction->trx_user->category_id) ? collect(Category::whereIn('id', $transaction->trx_user->category_id)->get())->pluck('name')->implode(', ') : ($transaction->trx_user->category ? $transaction->trx_user->category->name : '');
                    break;
                case 'category_code':
                    $row[] = is_array($transaction->trx_user->category_id) ? collect(Category::whereIn('id', $transaction->trx_user->category_id)->get())->pluck('code')->implode(', ') : ($transaction->trx_user->category ? $transaction->trx_user->category->code : '');
                    break;
                case 'leave_type':
                    if ($transaction->referenceable_type == 'App\Models\Tenant\GradeWiseMonthlyLeaveDetail' || $transaction->referenceable_type == 'App\Models\Tenant\GradeWiseYearlyLeaveDetail') {
                        $grage_wise_leave = $transaction->referenceable_type::where('id', $transaction->referenceable_id)->first();
                    }
                    $row[] = $grage_wise_leave != null ? $grage_wise_leave->leave_type->code : '';
                    break;
                case 'opening_balance':
                    $row[] = $transaction->opening_balance ? $transaction->opening_balance : '';
                    break;
                case 'credit':
                    $row[] = $transaction->credit ? $transaction->credit : '';
                    break;
                case 'debit':
                    $row[] = $transaction->debit ? $transaction->debit : '';
                    break;
                case 'balance':
                    $row[] = $transaction->balance ? $transaction->balance : '';
                    break;
                case 'remarks':
                    $row[] = $transaction->remarks ? $transaction->remarks : '';
                    break;
                default:
                    $row[] = 'Unknown Column';
                    break;
            }
        }

        return $row;
    }
}
