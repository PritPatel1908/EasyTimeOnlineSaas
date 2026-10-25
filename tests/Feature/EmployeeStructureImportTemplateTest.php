<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Http\Controllers\Tenant\LeaveGroupController;
use App\Models\Tenant\Category;
use App\Models\Tenant\Designation;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class EmployeeStructureImportTemplateTest extends TestCase
{
    public function test_leave_group_uses_grade_wise_leave_tables_and_relationships(): void
    {
        $leaveGroup = new LeaveGroup();

        $this->assertSame('grade_wise_leaves', $leaveGroup->getTable());
        $this->assertInstanceOf(HasMany::class, $leaveGroup->grade_wise_monthly_leave_details());
        $this->assertInstanceOf(HasMany::class, $leaveGroup->grade_wise_yearly_leave_details());
        $this->assertSame('grade_wise_leave_id', $leaveGroup->grade_wise_monthly_leave_details()->getForeignKeyName());
        $this->assertSame('grade_wise_leave_id', $leaveGroup->grade_wise_yearly_leave_details()->getForeignKeyName());
        $this->assertTrue($leaveGroup->is_monthly);
        $this->assertNotContains('is_monthly', LeaveGroup::IMPORT_EXPORT_COLUMNS);
        $this->assertInstanceOf(BelongsTo::class, (new GradeWiseMonthlyLeaveDetail())->grade_wise_leave());
        $this->assertInstanceOf(BelongsTo::class, (new GradeWiseYearlyLeaveDetail())->grade_wise_leave());
        $legacyAssignment = (new User())->leave_group();
        $this->assertSame('leave_groups', $legacyAssignment->getRelated()->getTable());
        $this->assertSame('leave_group_id', $legacyAssignment->getForeignKeyName());
    }

    public function test_employee_structure_templates_include_hints_for_each_column(): void
    {
        $templates = [
            CategoryController::class => Category::IMPORT_EXPORT_COLUMNS,
            DesignationController::class => Designation::IMPORT_EXPORT_COLUMNS,
            LeaveGroupController::class => LeaveGroup::IMPORT_EXPORT_COLUMNS,
            EmployeeController::class => User::IMPORT_EXPORT_COLUMNS,
        ];

        foreach ($templates as $controllerClass => $expectedHeaders) {
            $response = app($controllerClass)->downloadImportSample();
            $this->assertInstanceOf(StreamedResponse::class, $response);

            ob_start();
            ($response->getCallback())();
            $csv = ob_get_clean();
            $rows = array_map('str_getcsv', explode("\n", trim($csv)));

            $this->assertSame($expectedHeaders, $rows[0], $controllerClass);
            $this->assertCount(2, $rows, $controllerClass);
            $this->assertCount(count($expectedHeaders), $rows[1], $controllerClass);
            $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]), $controllerClass);
        }
    }

    public function test_employee_structure_import_rejects_a_differently_named_sample_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'employee-import');
        $handle = fopen($path, 'w');
        fputcsv($handle, User::IMPORT_EXPORT_COLUMNS);
        fclose($handle);

        $controller = app(EmployeeController::class);
        $validator = new \ReflectionMethod($controller, 'validateImportCsv');

        try {
            $file = new UploadedFile($path, 'companies_import_sample.csv', 'text/csv', UPLOAD_ERR_OK, true);
            $validator->invoke($controller, $file, User::IMPORT_EXPORT_COLUMNS, 'employees_import_sample.csv');
            $this->fail('An employee import must reject a sample with a different filename.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        } finally {
            unlink($path);
        }
    }

    public function test_employee_structure_import_accepts_its_named_template(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'employee-import');
        $handle = fopen($path, 'w');
        fputcsv($handle, User::IMPORT_EXPORT_COLUMNS);
        fclose($handle);

        $file = new UploadedFile($path, 'employees_import_sample.csv', 'text/csv', UPLOAD_ERR_OK, true);
        $validator = new \ReflectionMethod(app(EmployeeController::class), 'validateImportCsv');

        try {
            $validator->invoke(app(EmployeeController::class), $file, User::IMPORT_EXPORT_COLUMNS, 'employees_import_sample.csv');
        } catch (ValidationException $exception) {
            $this->fail('The matching employee template should be accepted: ' . $exception->getMessage());
        } finally {
            unlink($path);
        }
    }
}
