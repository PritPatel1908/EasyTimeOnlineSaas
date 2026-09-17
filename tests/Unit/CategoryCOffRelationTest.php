<?php

namespace Tests\Unit;

use App\Models\Tenant\Category;
use App\Models\Tenant\CoffAgainstOtSlab;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class CategoryCOffRelationTest extends TestCase
{
    public function test_category_has_ot_slab_relation(): void
    {
        $category = new Category();

        $relation = $category->c_off_against_ot_slabs();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(CoffAgainstOtSlab::class, $relation->getRelated());
    }
}
