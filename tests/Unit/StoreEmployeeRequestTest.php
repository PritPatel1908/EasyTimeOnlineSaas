<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\Tenant\StoreEmployeeRequest;
use ReflectionMethod;
use Tests\TestCase;

class StoreEmployeeRequestTest extends TestCase
{
    public function test_single_select_relations_remain_scalar_during_request_preparation(): void
    {
        $request = StoreEmployeeRequest::create('/employees', 'POST', [
            'location_id' => ['1', '2'],
            'category_id' => '3',
            'designation_id' => '4',
        ]);

        $prepareForValidation = new ReflectionMethod(StoreEmployeeRequest::class, 'prepareForValidation');
        $prepareForValidation->invoke($request);

        $this->assertSame(['1', '2'], $request->input('location_id'));
        $this->assertSame('3', $request->input('category_id'));
        $this->assertSame('4', $request->input('designation_id'));
    }
}
