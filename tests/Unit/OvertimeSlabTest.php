<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tenant\OvertimeSlab;
use InvalidArgumentException;
use Tests\TestCase;

class OvertimeSlabTest extends TestCase
{
    public function test_it_matches_numeric_minutes_inclusive_of_slab_boundaries(): void
    {
        $slab = new OvertimeSlab([
            'value_from' => '01:00',
            'value_to' => '02:00:00',
            'head_value' => '01:30',
        ]);

        $this->assertFalse($slab->containsMinutes(59));
        $this->assertTrue($slab->containsMinutes(60));
        $this->assertTrue($slab->containsMinutes(120));
        $this->assertFalse($slab->containsMinutes(121));
        $this->assertSame('01:30', $slab->formattedHeadValue());
    }

    public function test_it_compares_durations_over_one_hundred_hours_numerically(): void
    {
        $slab = new OvertimeSlab([
            'value_from' => '100:00',
            'value_to' => '101:00',
        ]);

        $this->assertTrue($slab->containsMinutes(6000));
        $this->assertTrue($slab->containsMinutes(6060));
        $this->assertFalse($slab->containsMinutes(5999));
    }

    public function test_it_accepts_sql_server_time_values_with_fractional_seconds(): void
    {
        $slab = new OvertimeSlab([
            'value_from' => '00:30:00.0000000',
            'value_to' => '01:00:00.0000000',
            'head_value' => '00:30:00.0000000',
        ]);

        $this->assertTrue($slab->containsMinutes(30));
        $this->assertTrue($slab->containsMinutes(60));
        $this->assertSame('00:30', $slab->formattedHeadValue());
    }

    public function test_a_slab_with_a_missing_boundary_does_not_match(): void
    {
        $slab = new OvertimeSlab([
            'value_from' => '00:00',
        ]);

        $this->assertFalse($slab->containsMinutes(1));
    }

    public function test_invalid_slab_values_are_reported(): void
    {
        $slab = new OvertimeSlab([
            'value_from' => 'invalid',
            'value_to' => '01:00',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $slab->containsMinutes(30);
    }
}
