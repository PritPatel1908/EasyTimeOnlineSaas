<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\Tenant\StoreEmployeeRequest;
use ReflectionMethod;
use Tests\TestCase;

class StoreEmployeeRequestTest extends TestCase
{
    public function test_request_preparation_preserves_relation_arrays_and_single_select_scalars(): void
    {
        $request = StoreEmployeeRequest::create('/employees', 'POST', [
            'location_id' => ['1', '2'],
            'area_id' => ['5', '6'],
            'category_id' => '3',
            'designation_id' => '4',
        ]);

        $prepareForValidation = new ReflectionMethod(StoreEmployeeRequest::class, 'prepareForValidation');
        $prepareForValidation->invoke($request);

        $this->assertSame(['1', '2'], $request->input('location_id'));
        $this->assertSame(['5', '6'], $request->input('area_id'));
        $this->assertSame('3', $request->input('category_id'));
        $this->assertSame('4', $request->input('designation_id'));
    }
}
