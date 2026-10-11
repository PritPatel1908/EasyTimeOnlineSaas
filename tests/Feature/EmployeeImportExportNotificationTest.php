<?php

namespace Tests\Feature;

use App\Notifications\Tenant\EmployeeImportExportCompleted;
use Tests\TestCase;

class EmployeeImportExportNotificationTest extends TestCase
{
    public function test_employee_export_notification_contains_the_download_action(): void
    {
        $notification = new EmployeeImportExportCompleted(
            'export',
            'Employee export is ready for download.',
            '/employee-structure/employees/export/download/employees.csv',
        );

        $data = $notification->toArray(new \stdClass());

        $this->assertSame('Employee export completed', $data['title']);
        $this->assertSame('/employee-structure/employees', $data['url']);
        $this->assertSame('/employee-structure/employees/export/download/employees.csv', $data['download_url']);
        $this->assertSame('export', $data['type']);
    }
}
