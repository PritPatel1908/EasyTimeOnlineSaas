<?php

namespace App\Helpers\MonthlyReportHelpers;

use App\Enums\ReportColumns\SummaryReportColumns;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Events\MonthlyReportGenerated;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Department;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\MonthlyReport;
use App\Models\Tenant\StatusMaster;
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

class SummaryReportGenerator
{
    protected MonthlyReport $monthlyReport;

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

    public function __construct(MonthlyReport $monthlyReport)
    {
        $this->monthlyReport = $monthlyReport;
        $this->year = $this->monthlyReport->from_datetime->year;
    }

    public function generateSummaryReport(): void
    {
        $this->selected_columns = $this->monthlyReport->selected_columns;
        usort($this->selected_columns, function ($a, $b) {
            return $this->orderMap[$a] - $this->orderMap[$b];
        });

        $attendanceDetails = Attendance::whereBetween(
            'date',
            [
                $this->monthlyReport->from_datetime,
                $this->monthlyReport->to_datetime,
            ]
        )->with(['user', 'status_master']);

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
    }

    public function generateDetailedXlsxReport($result)
    {
        // Process data similar to CSV method
        $datacsv = $this->renderCSVTree($result);

        // Add title header rows
        $reportTitle = 'Summary Report';
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

                        // Style headers
                        if (is_array($row) && isset($row[0]) && $rowIndex === 2) {
                            $sheet->getStyle($rowIndex + 1)->getFont()->setBold(true);
                            $sheet->getStyle($rowIndex + 1)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('E2EFDA'); // Light green background
                        }
                        // Style total rows
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
                        'A' => 30,
                        'B' => 15,
                        'C' => 15,
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
            $keyParts = explode('|', $key);
            $department = $keyParts[0] ?? 'Unknown Department';
            $code = $keyParts[1] ?? '';
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
        $status = StatusMaster::where('code', $statusCode)->first();
        if (! $status) {
            return 0;
        }

        return $records->where('status_master_id', $status->id)->count();
    }

    private function getFullLeaveCount($records)
    {
        $statusIds = StatusMaster::whereIn('code', LeaveType::all()->pluck('code'))->pluck('id')->toArray();

        return $statusIds ? $records->whereIn('status_master_id', $statusIds)->count() : 0;
    }

    private function getFirstLeaveCount($records)
    {
        $leaveTypeCodes = LeaveType::pluck('code')->toArray();

        $statusIds = StatusMaster::where(function ($query) use ($leaveTypeCodes) {
            foreach ($leaveTypeCodes as $code) {
                $query->orWhere('code', 'LIKE', "P{$code}");
            }
        })->pluck('id')->toArray();

        return ! empty($statusIds) ? $records->whereIn('status_master_id', $statusIds)->count() : 0;
    }

    private function getSecondLeaveCount($records)
    {
        $leaveTypeCodes = LeaveType::pluck('code')->toArray();

        $statusIds = StatusMaster::where(function ($query) use ($leaveTypeCodes) {
            foreach ($leaveTypeCodes as $code) {
                $query->orWhere('code', 'LIKE', "{$code}P");
            }
        })->pluck('id')->toArray();

        return ! empty($statusIds) ? $records->whereIn('status_master_id', $statusIds)->count() : 0;
    }
}
