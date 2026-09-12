<?php

namespace App\Helpers\ReportHelpers;

use App\Enums\ReportColumns\SummaryReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\DailyReportGenerated;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\DailyReport;
use App\Models\Tenant\Department;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\StatusMaster;
use Filament\Notifications\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SummaryReportGenerator
{
    protected DailyReport $dailyReport;

    public $selected_columns = [];

    public $year;

    private $orderMap = [
        SummaryReportColumns::department_name->value => 1,
        SummaryReportColumns::department_code->value => 2,
        SummaryReportColumns::present_count->value => 3,
        SummaryReportColumns::absent_count->value => 4,
        SummaryReportColumns::week_off_count->value => 5,
        SummaryReportColumns::holiday_count->value => 6,
        SummaryReportColumns::full_day_leave_count->value => 7,
        SummaryReportColumns::first_half_leave_count->value => 8,
        SummaryReportColumns::second_half_leave_count->value => 9,
        SummaryReportColumns::total->value => 10,
    ];

    public function __construct(DailyReport $dailyReport)
    {
        $this->dailyReport = $dailyReport;
        $this->year = $this->dailyReport->from_datetime->year;
    }

    public function generateSummaryReport(): void
    {
        $this->selected_columns = $this->dailyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        $attendanceDetails = Attendance::Year($this->year)
            ->with('user');
        // ->with(['user.department', 'status_master'])
        // ->whereHas('user.department');

        // Filter By Date
        $attendanceDetails->whereBetween(
            'date',
            [
                $this->dailyReport->from_datetime,
                $this->dailyReport->to_datetime,
            ]
        );

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
    }

    public function generateDetailedCsvReport($result)
    {
        $result = $result->groupBy(function ($attendance) {
            if ($attendance->user) {
                if (is_array($attendance->user->department_id)) {
                    $departments = Department::whereIn('id', $attendance->user->department_id)->get();
                    $departmentString = $departments->map(function ($department) {
                        return $department->name.'|'.($department->code ?? '');
                    })->first();

                    return $departmentString;
                } elseif ($attendance->user->department) {
                    return $attendance->user->department->name.'|'.
                        ($attendance->user->department->code ?? '');
                }
            }
        });

        $datacsv = $this->renderCSVTree($result);
        $csv = $datacsv;

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

    public function generateDetailedXlsxReport($result)
    {
        $result = $result->groupBy(function ($attendance) {
            if ($attendance->user) {
                if (is_array($attendance->user->department_id)) {
                    $departments = Department::whereIn('id', $attendance->user->department_id)->get();
                    $departmentString = $departments->map(function ($department) {
                        return $department->name.'|'.($department->code ?? '');
                    })->first();

                    return $departmentString;
                } elseif ($attendance->user->department) {
                    return $attendance->user->department->name.'|'.
                        ($attendance->user->department->code ?? '');
                }
            }
        });

        $datacsv = $this->renderCSVTree($result);

        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Add report title and date period
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'Summary Report');
        $sheet->mergeCells('A2:J2');
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

        $sheet->getStyle('A1:J2')->applyFromArray($headerStyle);

        // Add column headers at row 4
        $columnIndex = 1;
        foreach ($datacsv[0] as $head) {
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

        $sheet->getStyle('A4:J4')->applyFromArray($columnHeaderStyle);

        // Add data starting from row 5
        $rowIndex = 5;
        for ($i = 1; $i < count($datacsv); $i++) {
            $columnIndex = 1;
            foreach ($datacsv[$i] as $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $value);
                $columnIndex++;
            }
            $rowIndex++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style the Grand Total row
        $grandTotalStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFFF00',
                ],
            ],
        ];

        $sheet->getStyle('A'.($rowIndex - 1).':J'.($rowIndex - 1))->applyFromArray($grandTotalStyle);

        // Set borders for all data
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A4:J'.($rowIndex - 1))->applyFromArray($borderStyle);

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

    public function renderCSVTree($result)
    {
        $csv = [];
        $grandTotal = [
            'department_name' => 'Grand Total',
            'department_code' => '',
            'present_count' => 0,
            'absent_count' => 0,
            'week_off_count' => 0,
            'holiday_count' => 0,
            'full_day_leave_count' => 0,
            'first_half_leave_count' => 0,
            'second_half_leave_count' => 0,
            'total' => 0,
        ];
        $heading = [];
        foreach ($this->selected_columns as $column) {
            $heading[] = SummaryReportColumns::{$column}->getLabel();
        }
        $csv[] = $heading;
        foreach ($result as $key => $records) {
            [$department, $code] = explode('|', $key);
            if ($records) {
                $csv[] = $this->renderDetailedCSVColumns($records, $department, $code);
            }
            $grandTotal['present_count'] += $this->getStatusCount($records, 'PP');
            $grandTotal['absent_count'] += $this->getStatusCount($records, 'AA');
            $grandTotal['week_off_count'] += $this->getStatusCount($records, 'WO');
            $grandTotal['holiday_count'] += $this->getStatusCount($records, 'HL');
            $grandTotal['full_day_leave_count'] += $this->getFullLeaveCount($records);
            $grandTotal['first_half_leave_count'] += $this->getFirstLeaveCount($records);
            $grandTotal['second_half_leave_count'] += $this->getSecondLeaveCount($records);
            $grandTotal['total'] += $this->getStatusCount($records, 'PP') + $this->getStatusCount($records, 'AA') + $this->getStatusCount($records, 'WO') + $this->getStatusCount($records, 'HL') + $this->getFullLeaveCount($records) + $this->getFirstLeaveCount($records) + $this->getSecondLeaveCount($records);
        }
        $csv[] = $grandTotal;

        return $csv;
    }

    public function renderDetailedCSVColumns($attendance, $department, $code)
    {
        $row = [];
        foreach ($this->selected_columns as $column) {
            switch ($column) {
                case 'department_name':
                    $row[] = $department;
                    break;
                case 'department_code':
                    $row[] = $code;
                    break;
                case 'present_count':
                    $row[] = $this->getStatusCount($attendance, 'PP');
                    break;
                case 'absent_count':
                    $row[] = $this->getStatusCount($attendance, 'AA');
                    break;
                case 'week_off_count':
                    $row[] = $this->getStatusCount($attendance, 'WO');
                    break;
                case 'holiday_count':
                    $row[] = $this->getStatusCount($attendance, 'HL');
                    break;
                case 'full_day_leave_count':
                    $row[] = $this->getFullLeaveCount($attendance);
                    break;
                case 'first_half_leave_count':
                    $row[] = $this->getFirstLeaveCount($attendance);
                    break;
                case 'second_half_leave_count':
                    $row[] = $this->getSecondLeaveCount($attendance);
                    break;
                case 'total':
                    $row[] = $this->getStatusCount($attendance, 'PP') + $this->getStatusCount($attendance, 'AA') + $this->getStatusCount($attendance, 'WO') + $this->getStatusCount($attendance, 'HL') + $this->getFullLeaveCount($attendance) + $this->getFirstLeaveCount($attendance) + $this->getSecondLeaveCount($attendance);
                    break;
                default:
                    $row[] = 'Unknown Column';
                    break;
            }
        }

        return $row;
    }

    private function getStatusCount($records, $statusCode)
    {
        return $records->where('status_master_id', StatusMaster::where('code', $statusCode)->first()->id)->count();
    }

    private function getFullLeaveCount($records)
    {
        return $records->whereIn('status_master_id', StatusMaster::whereIn('code', LeaveType::all()->pluck('code'))->first()->id)->count();
    }

    private function getFirstLeaveCount($records)
    {
        $leaveTypeCodes = LeaveType::pluck('code')->toArray();

        $statusIds = StatusMaster::where(function ($query) use ($leaveTypeCodes) {
            foreach ($leaveTypeCodes as $code) {
                $query->orWhere('code', 'LIKE', "P{$code}");
            }
        })->pluck('id');

        return $records->whereIn('status_master_id', $statusIds)->count();
    }

    private function getSecondLeaveCount($records)
    {
        $leaveTypeCodes = LeaveType::pluck('code')->toArray();

        $statusIds = StatusMaster::where(function ($query) use ($leaveTypeCodes) {
            foreach ($leaveTypeCodes as $code) {
                $query->orWhere('code', 'LIKE', "{$code}P");
            }
        })->pluck('id');

        return $records->whereIn('status_master_id', $statusIds)->count();
    }
}
