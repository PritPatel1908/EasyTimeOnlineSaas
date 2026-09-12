<?php

namespace App\Helpers\YearlyReportHelpers;

use App\Enums\ReportColumns\LeaveInfoReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\YearlyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\YearlyReport;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class LeaveInfoReportGenerator
{
    protected YearlyReport $yearlyReport;

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

    public function __construct(YearlyReport $yearlyReport)
    {
        $this->yearlyReport = $yearlyReport;
        $this->year = $this->yearlyReport->from_datetime->year;
    }

    public function generateLeaveInfoReport(): void
    {
        $this->selected_columns = $this->yearlyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        // Generate attendance report
        $leaveInfoDetails = LeaveApplication::with('user');
        // $leaveInfoDetails->with('user.department');
        // $leaveInfoDetails->with('user.sub_department');
        // $leaveInfoDetails->with('user.company');
        // $leaveInfoDetails->with('user.location');
        // $leaveInfoDetails->with('user.category');
        // $leaveInfoDetails->with('user.sub_category');

        // Filter By Date
        $leaveInfoDetails->whereBetween(
            'application_date',
            [
                $this->yearlyReport->from_datetime,
                $this->yearlyReport->to_datetime,
            ]
        );

        // Filter By User Type
        $leaveInfoDetails->whereHas('user', function ($query) {
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
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($leaveInfoDetails));
        $result = $leaveInfoDetails->get();
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
        foreach ($this->yearlyReport->group_by_columns as $column) {
            $group_by_array[] = $name_to_column[$column];
        }
        if (count($group_by_array) > 0) {
            $result = $result->groupBy($group_by_array);
        }

        $user_group = fn (LeaveApplication $application) => $application->user->code;
        $result = $result->groupBy($user_group);

        $datacsv = $this->renderCSVTree($result);

        $csv = $datacsv;

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

    public function renderCSVTree($result, $level = 1)
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
