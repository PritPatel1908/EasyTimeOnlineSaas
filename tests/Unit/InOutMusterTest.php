<?php

namespace Tests\Unit;

use App\Models\Tenant\InOutMuster;
use Tests\TestCase;

class InOutMusterTest extends TestCase
{
    public function test_category_scopes_can_be_mass_assigned(): void
    {
        $muster = new InOutMuster('2026');
        $muster->fill([
            'category_id' => [1, 2],
            'sub_category_id' => [3],
        ]);

        $this->assertSame([1, 2], $muster->category_id);
        $this->assertSame([3], $muster->sub_category_id);
    }
}
