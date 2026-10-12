<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\CanteenFacilityController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\LocationController;
use App\Http\Controllers\Tenant\SubDepartmentController;
use App\Http\Controllers\Tenant\TeamController;
use App\Http\Controllers\Tenant\UnitController;
use App\Support\CsvImportTemplate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class CompanyStructureImportTemplateTest extends TestCase
{
    public function test_each_company_structure_template_has_field_hints_and_an_ignored_hint_row(): void
    {
        $templates = [
            CompanyController::class => ['name', 'code', 'email', 'status', 'location'],
            LocationController::class => ['name', 'code', 'email', 'latitude', 'longitude', 'status'],
            DepartmentController::class => ['name', 'code', 'email', 'status', 'company', 'location'],
            SubDepartmentController::class => ['name', 'code', 'email', 'status', 'department', 'location'],
            TeamController::class => ['name', 'code', 'email', 'status', 'company', 'location'],
            UnitController::class => ['name', 'code', 'status', 'location'],
            CanteenFacilityController::class => [
                'name',
                'code',
                'total_cfa',
                'status',
                'location',
                'total_absent_days',
                'company_contribution_in_percentage_wise',
                'company_allowance_contribution_in_fixed',
                'company_allowance_contribution_in_percentage',
            ],
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
            $hints = strtolower(implode(' ', $rows[1]));
            $this->assertStringContainsString('required', $hints, $controllerClass);
            $this->assertStringContainsString('optional', $hints, $controllerClass);
        }
    }

    public function test_normal_import_data_is_not_detected_as_a_hint_row(): void
    {
        $this->assertFalse(CsvImportTemplate::isHintRow(['North Office', 'LOC-001']));
    }

    public function test_wrong_module_csv_template_is_rejected(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'company-csv');
        $handle = fopen($file, 'w');
        fputcsv($handle, ['name', 'code', 'email', 'status', 'location']);
        fclose($handle);

        $this->assertSame([], CsvImportTemplate::validateHeaders($file, ['name', 'code', 'email', 'status', 'location']));
        $this->assertNotSame([], CsvImportTemplate::validateHeaders($file, ['name', 'code', 'email', 'latitude', 'longitude', 'status']));

        unlink($file);
    }
}
