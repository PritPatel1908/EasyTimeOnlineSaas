<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\CustomReportFieldOptions;
use PHPUnit\Framework\TestCase;

class CustomReportFieldOptionsTest extends TestCase
{
    public function test_employee_and_master_fields_do_not_support_format_or_summary_options(): void
    {
        foreach ([
            'emp_code',
            'emp_card',
            'name',
            'first_name',
            'middle_name',
            'last_name',
            'role',
            'data_policy',
            'aadhar_card',
            'uan_no',
            'esic_no',
            'pan_no',
            'left_remark',
            'location_name',
        ] as $fieldName) {
            $this->assertFalse(CustomReportFieldOptions::supportsDateTimeFormat($fieldName), $fieldName);
            $this->assertFalse(CustomReportFieldOptions::supportsSummary($fieldName), $fieldName);
        }
    }

    public function test_master_date_fields_support_date_only_formats_and_last_login_supports_combined_formats(): void
    {
        foreach (['joining_date', 'left_date', 'birth_date'] as $fieldName) {
            $this->assertTrue(CustomReportFieldOptions::supportsDateTimeFormat($fieldName));
            $this->assertFalse(CustomReportFieldOptions::supportsSummary($fieldName));
            $this->assertContains($fieldName, CustomReportFieldOptions::dateOnlyFields());
        }

        $this->assertTrue(CustomReportFieldOptions::supportsDateTimeFormat('last_login'));
        $this->assertFalse(CustomReportFieldOptions::supportsSummary('last_login'));
        $this->assertContains('last_login', CustomReportFieldOptions::dateTimeOnlyFields());
    }

    public function test_date_and_attendance_detail_fields_keep_their_supported_options(): void
    {
        $this->assertTrue(CustomReportFieldOptions::supportsDateTimeFormat('date'));
        $this->assertFalse(CustomReportFieldOptions::supportsSummary('date'));
        $this->assertTrue(CustomReportFieldOptions::supportsDateTimeFormat('attendance_in_time'));
        $this->assertTrue(CustomReportFieldOptions::supportsSummary('attendance_in_time'));
    }

    public function test_attendance_fields_are_available_in_the_field_selector_options(): void
    {
        foreach (['week_off', 'holiday', 'early_minutes', 'late_minutes'] as $fieldName) {
            $this->assertArrayHasKey($fieldName, CustomReportFieldOptions::all());
        }
    }
}
