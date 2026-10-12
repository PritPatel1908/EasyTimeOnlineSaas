<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Models\Tenant\Category;
use App\Models\Tenant\Designation;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class EmployeeStructureImportTemplateTest extends TestCase
{
    public function test_employee_structure_templates_include_hints_for_each_column(): void
    {
        $templates = [
            CategoryController::class => Category::IMPORT_EXPORT_COLUMNS,
            DesignationController::class => Designation::IMPORT_EXPORT_COLUMNS,
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
