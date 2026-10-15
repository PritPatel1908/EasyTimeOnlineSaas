<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CsvImportTemplate;
use Tests\TestCase;

class CsvImportTemplateTest extends TestCase
{
    public function test_additional_columns_can_be_accepted_while_required_columns_are_present(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv-import-template-');
        if ($path === false) {
            $this->fail('Unable to create a temporary CSV file.');
        }

        file_put_contents($path, "name,status,monthly_rule_1_increment\nAnnual Leave,Active,2\n");

        try {
            $this->assertSame([], CsvImportTemplate::validateHeaders($path, ['name', 'status'], true));
            $this->assertNotEmpty(CsvImportTemplate::validateHeaders($path, ['name', 'financial_year_id'], true));
        } finally {
            unlink($path);
        }
    }
}
