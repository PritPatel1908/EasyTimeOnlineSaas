<?php

namespace Tests\Unit;

use App\Models\Tenant\InOutMuster;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InOutMusterTest extends TestCase
{
    public function test_organization_scope_fields_are_not_mass_assignable_or_cast(): void
    {
        $muster = new InOutMuster('2026');

        foreach ([
            'location_id',
            'company_id',
            'department_id',
            'sub_department_id',
            'category_id',
            'sub_category_id',
        ] as $field) {
            $this->assertFalse(Schema::hasColumn('in_out_musters2026', $field));
            $this->assertNotContains($field, $muster->getFillable());
            $this->assertArrayNotHasKey($field, $muster->getCasts());
        }
    }
}
