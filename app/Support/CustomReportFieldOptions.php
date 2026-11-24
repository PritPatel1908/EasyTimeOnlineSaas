<?php

declare(strict_types=1);

namespace App\Support;

final class CustomReportFieldOptions
{
    public static function dateOnlyFields(): array
    {
        return ['joining_date', 'left_date', 'birth_date', 'date'];
    }

    public static function dateTimeOnlyFields(): array
    {
        return ['last_login'];
    }

    public static function dateDetailFields(): array
    {
        return [
            'attendance_specific_status',
            'attendance_status',
            'attendance_in_time',
            'attendance_out_time',
            'shift_name',
            'shift_code',
            'shift_in_time',
            'shift_out_time',
            'early_minutes',
            'late_minutes',
            'working_hrs',
            'overtime_minutes',
            'approved_overtime_minutes',
            'week_off',
            'no_punch',
            'single_punch',
            'machine_in_out',
            'break_hrs',
            'manual_punch',
            'manual_punch_time',
            'manual_punch_type',
            'manual_punch_created_on',
            'month_total_days',
            'leave_balance',
            'leave_add',
            'leave_taken',
            'break_start',
            'break_end',
            'holiday',
            'leave',
        ];
    }

    public static function dateTimeFields(): array
    {
        return array_values(array_diff(
            array_merge(self::dateOnlyFields(), self::dateTimeOnlyFields(), self::dateDetailFields()),
            array_merge(self::minuteFormatFields(), ['attendance_specific_status', 'attendance_status', 'shift_name', 'shift_code', 'manual_punch', 'month_total_days', 'no_punch']),
        ));
    }

    public static function statusSelectionFields(): array
    {
        return ['attendance_specific_status'];
    }

    public static function supportsStatusSelection(string $fieldName): bool
    {
        return in_array($fieldName, self::statusSelectionFields(), true);
    }

    public static function timeOnlyFields(): array
    {
        return ['attendance_in_time', 'attendance_out_time'];
    }

    public static function minuteFormatFields(): array
    {
        return ['late_minutes', 'early_minutes', 'working_hrs', 'overtime_minutes', 'approved_overtime_minutes'];
    }

    public static function summaryFields(): array
    {
        return array_values(array_diff(
            self::dateDetailFields(),
            ['attendance_in_time', 'attendance_out_time', 'shift_name', 'shift_code', 'manual_punch', 'manual_punch_time', 'manual_punch_type', 'manual_punch_created_on'],
        ));
    }

    public static function supportsDateTimeFormat(string $fieldName): bool
    {
        return in_array($fieldName, self::dateTimeFields(), true);
    }

    public static function supportsSummary(string $fieldName): bool
    {
        return in_array($fieldName, self::summaryFields(), true);
    }

    public static function manualPunchSubfields(): array
    {
        return [
            'manual_punch_time' => 'Time',
            'manual_punch_type' => 'Punch Type',
            'manual_punch_created_on' => 'Created On',
        ];
    }

    public static function supportsMinuteFormat(string $fieldName): bool
    {
        return in_array($fieldName, self::minuteFormatFields(), true);
    }

    public static function all(): array
    {
        return [
            'emp_code' => 'Emp Code',
            'emp_card' => 'Emp Card',
            'name' => 'Name',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'role' => 'Role',
            'data_policy' => 'Data Policy',
            'aadhar_card' => 'Aadhar Card',
            'uan_no' => 'UAN No',
            'esic_no' => 'ESIC No',
            'pan_no' => 'PAN No',
            'joining_date' => 'Joining Date',
            'left_date' => 'Left Date',
            'left_remark' => 'Left Remark',
            'last_login' => 'Last Login',
            'location_name' => 'Location Name',
            'location_code' => 'Location Code',
            'company_name' => 'Company Name',
            'company_code' => 'Company Code',
            'department_name' => 'Department Name',
            'department_code' => 'Department Code',
            'sub_department_name' => 'Sub Department Name',
            'sub_department_code' => 'Sub Department Code',
            'designation_name' => 'Designation Name',
            'designation_code' => 'Designation Code',
            'unit_mill_name' => 'Unit/Mill Name',
            'unit_mill_code' => 'Unit/Mill Code',
            'leave_group_name' => 'Leave Group Name',
            'team_name' => 'Team Name',
            'team_code' => 'Team Code',
            'canteen_facility_name' => 'Canteen Facility Name',
            'canteen_facility_code' => 'Canteen Facility Code',
            'category_name' => 'Category Name',
            'category_code' => 'Category Code',
            'shift_name' => 'Shift Name',
            'shift_code' => 'Shift Code',
            'status' => 'Status',
            'birth_date' => 'Birth Date',
            'gender' => 'Gender',
            'blood_group' => 'Blood Group',
            'height' => 'Height',
            'width' => 'Width',
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'current_address' => 'Current Address',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_number' => 'Emergency Contact Number',
            'emergency_contact_address' => 'Emergency Contact Address',
            'bank_name' => 'Bank Name',
            'bank_account_number' => 'Bank Account Number',
            'bank_ifsc_code' => 'Bank IFSC Code',
            'bank_account_branch_name' => 'Bank Account Branch Name',
            'approvers' => 'Approvers',
            'date' => 'Date',
            'attendance_specific_status' => 'Attendance Specific Status',
            'attendance_status' => 'Attendance Status',
            'attendance_in_time' => 'Attendance In Time',
            'attendance_out_time' => 'Attendance Out Time',
            'shift_in_time' => 'Shift In Time',
            'shift_out_time' => 'Shift Out Time',
            'early_minutes' => 'Early Minutes',
            'late_minutes' => 'Late Minutes',
            'working_hrs' => 'Working Hrs',
            'overtime_minutes' => 'Overtime Minutes',
            'approved_overtime_minutes' => 'Approved Overtime Minutes',
            'week_off' => 'Week Off',
            'no_punch' => 'No Punch',
            'single_punch' => 'Single Punch',
            'machine_in_out' => 'Machine In Out',
            'break_hrs' => 'Break Hrs',
            'manual_punch' => 'Manual Punch',
            'manual_punch_time' => 'Time',
            'manual_punch_type' => 'Punch Type',
            'manual_punch_created_on' => 'Created On',
            'month_total_days' => 'Selected Period Days',
            'leave_balance' => 'Leave Balance',
            'leave_add' => 'Leave Add',
            'leave_taken' => 'Leave Taken',
            'break_start' => 'Break Start',
            'break_end' => 'Break End',
            'holiday' => 'Holiday',
            'leave' => 'Leave',
        ];
    }
}
