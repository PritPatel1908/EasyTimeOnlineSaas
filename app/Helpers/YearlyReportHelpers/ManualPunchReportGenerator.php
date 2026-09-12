<?php

namespace App\Helpers\YearlyReportHelpers;

use App\Enums\ReportColumns\ManualPunchReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\YearlyReportGenerated;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\YearlyReport;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class ManualPunchReportGenerator
{
    protected YearlyReport $yearlyReport;

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

    public function __construct(YearlyReport $yearlyReport)
    {
        $this->yearlyReport = $yearlyReport;
        $this->year = $this->yearlyReport->from_datetime->year;
    }

    public function generateManualPunchReport(): void
    {
        $this->selected_columns = $this->yearlyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        // Generate manual_punch report
        $manualPunch = ManualPunch::with('Users');
        // $manualPunch->with('Users.department');
        // $manualPunch->with('Users.sub_department');
        // $manualPunch->with('Users.company');
        // $manualPunch->with('Users.location');
        // $manualPunch->with('Users.category');
        // $manualPunch->with('Users.sub_category');

        // Filter By Date
        $manualPunch->whereBetween(
            'punch_date',
            [
                $this->yearlyReport->from_datetime,
                $this->yearlyReport->to_datetime,
            ]
        );

        // Filter By Area
        // if ($this->yearlyReport->areas()->count() > 0) {
        //     $manualPunch->whereIn('canteen_id', $this->yearlyReport->canteens->pluck('id'));
        // }

        // Filter By User Type
        $manualPunch->whereHas('Users', function ($query) {
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
        // dd("Query : ", GeneralHelper::getEloquentSqlWithBindings($manualPunch));
        $result = $manualPunch->get();
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
            'company' => function (ManualPunch $manual_punch) {
                if ($manual_punch->users->first() && is_array($manual_punch->users->first()->company_id)) {
                    $companies = Company::whereIn('id', $manual_punch->users->first()->sub_category_id)->get();

                    $companyStrings = $companies->map(function ($company) {
                        return $company->name.' ('.$company->code.')';
                    });

                    return $companyStrings->implode(', ');
                } elseif ($manual_punch->users->first() && $manual_punch->users->first()->company_id) {
                    return $manual_punch->users->first()->company->name.' ('.$manual_punch->users->first()->company->code.')';
                }

                return '';
            },
            'department' => function (ManualPunch $manual_punch) {
                if ($manual_punch->users->first() && is_array($manual_punch->users->first()->department_id)) {
                    $departments = Department::whereIn('id', $manual_punch->users->first()->department_id)->get();

                    $departmentStrings = $departments->map(function ($department) {
                        return $department->name.' ('.$department->code.')';
                    });

                    return $departmentStrings->implode(', ');
                } elseif ($manual_punch->users->first() && $manual_punch->users->first()->department_id) {
                    return $manual_punch->users->first()->department->name.' ('.$manual_punch->users->first()->department->code.')';
                }

                return '';
            },
            'sub_department' => function (ManualPunch $manual_punch) {
                if ($manual_punch->users->first() && is_array($manual_punch->users->first()->sub_department_id)) {
                    $sub_departments = SubDepartment::whereIn('id', $manual_punch->users->first()->sub_department_id)->get();

                    $subDepartmentStrings = $sub_departments->map(function ($sub_department) {
                        return $sub_department->name.' ('.$sub_department->code.')';
                    });

                    return $subDepartmentStrings->implode(', ');
                } elseif ($manual_punch->users->first() && $manual_punch->users->first()->sub_department_id) {
                    return $manual_punch->users->first()->sub_department->name.' ('.$manual_punch->users->first()->sub_department->code.')';
                }

                return '';
            },
            'category' => function (ManualPunch $manual_punch) {
                if ($manual_punch->users->first() && is_array($manual_punch->users->first()->categoty_id)) {
                    $categories = Category::whereIn('id', $manual_punch->users->first()->category_id)->get();

                    $categoryStrings = $categories->map(function ($category) {
                        return $category->name.' ('.$category->code.')';
                    });

                    return $categoryStrings->implode(', ');
                } elseif ($manual_punch->users->first() && $manual_punch->users->first()->category_id) {
                    return $manual_punch->users->first()->category->name.' ('.$manual_punch->users->first()->category->code.')';
                }

                return '';
            },
            'sub_category' => function (ManualPunch $manual_punch) {
                if ($manual_punch->users->first() && is_array($manual_punch->users->first()->sub_categoty_id)) {
                    $sub_categories = SubCategory::whereIn('id', $manual_punch->users->first()->sub_category_id)->get();

                    $subCategoryStrings = $sub_categories->map(function ($sub_category) {
                        return $sub_category->name.' ('.$sub_category->code.')';
                    });

                    return $subCategoryStrings->implode(', ');
                } elseif ($manual_punch->users->first() && $manual_punch->users->first()->sub_category_id) {
                    return $manual_punch->users->first()->sub_category->name.' ('.$manual_punch->users->first()->sub_category->code.')';
                }

                return '';
            },
            'user' => function (ManualPunch $manual_punch) {
                return $manual_punch->users ? $manual_punch->users->first()->code : '';
            },
        ];
        $group_by_array = [];
        foreach ($this->yearlyReport->group_by_columns as $column) {
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
    //         } elseif ($value instanceof \App\Models\Tenant\ManualPunch) {
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
    //         } elseif ($value instanceof \App\Models\Tenant\ManualPunch) {
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
                    $row[] = $value->users->first()->first()->code ? $value->users->first()->code : '';
                    break;
                case 'name':
                    $row[] = $value->users->first()->name ? $value->users->first()->name : '';
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
                    $row[] = is_array($value->user->department_id) ? collect(Department::whereIn('id', $value->user->department_id)->get())->pluck('name')->implode(', ') : ($value->user->department ? $value->user->department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($value->user->department_id) ? collect(Department::whereIn('id', $value->user->department_id)->get())->pluck('code')->implode(', ') : ($value->user->department ? $value->user->department->code : '');
                    break;
                case 'sub_department_name':
                    $row[] = is_array($value->user->sub_department_id) ? collect(SubDepartment::whereIn('id', $value->user->sub_department_id)->get())->pluck('name')->implode(', ') : ($value->user->sub_department ? $value->user->sub_department->name : '');
                    break;
                case 'department_code':
                    $row[] = is_array($value->user->sub_department_id) ? collect(SubDepartment::whereIn('id', $value->user->sub_department_id)->get())->pluck('code')->implode(', ') : ($value->user->sub_department ? $value->user->sub_department->code : '');
                    break;
                case 'category_name':
                    $row[] = is_array($value->user->category_id) ? collect(Category::whereIn('id', $value->user->category_id)->get())->pluck('name')->implode(', ') : ($value->user->category ? $value->user->category->name : '');
                    break;
                case 'category_code':
                    $row[] = is_array($value->user->category_id) ? collect(Category::whereIn('id', $value->user->category_id)->get())->pluck('code')->implode(', ') : ($value->user->category ? $value->user->category->code : '');
                    break;
                case 'sub_category_name':
                    $row[] = is_array($value->user->sub_category_id) ? collect(SubCategory::whereIn('id', $value->user->sub_category_id)->get())->pluck('name')->implode(', ') : ($value->user->sub_category ? $value->user->sub_category->name : '');
                    break;
                case 'sub_category_code':
                    $row[] = is_array($value->user->sub_category_id) ? collect(SubCategory::whereIn('id', $value->user->sub_category_id)->get())->pluck('code')->implode(', ') : ($value->user->sub_category ? $value->user->sub_category->code : '');
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
