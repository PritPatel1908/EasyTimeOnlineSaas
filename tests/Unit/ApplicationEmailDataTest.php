<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffChangeApplicationDetail;
use App\Services\Tenant\ApplicationEmailData;
use Tests\TestCase;

class ApplicationEmailDataTest extends TestCase
{
    public function test_manual_attendance_template_variables_include_formatted_times_and_reason(): void
    {
        $application = new ManualAttendanceApplication([
            'in_time' => '2026-11-13 08:05:00',
            'out_time' => '2026-11-13 17:10:00',
            'reason' => 'Forgot to punch out',
        ]);

        $variables = ApplicationEmailData::applicationVariables(
            $application,
            'manual_attendance_application',
        );

        $this->assertSame('08:05:00', $variables['in_time']);
        $this->assertSame('17:10:00', $variables['out_time']);
        $this->assertSame('Forgot to punch out', $variables['reason']);
    }

    public function test_week_off_change_template_variables_include_detail_values(): void
    {
        $application = new WeekOffChangeApplication([
            'from_date' => '2026-11-13',
            'is_forever' => true,
            'reason' => 'Operational requirement',
        ]);
        $application->setRelation('WeekOffChangeDetails', collect([
            new WeekOffChangeApplicationDetail([
                'week_days' => 1,
                'wo_type' => 2,
                'first_week' => true,
                'second_week' => false,
                'third_week' => true,
                'fourth_week' => false,
                'fifth_week' => false,
            ]),
        ]));

        $variables = ApplicationEmailData::applicationVariables(
            $application,
            'week_off_change_application',
        );

        $this->assertSame('Monday', $variables['week_days']);
        $this->assertSame('FirstHalf', $variables['wo_types']);
        $this->assertSame('Yes', $variables['is_forever']);
        $this->assertSame('Yes', $variables['first_week']);
        $this->assertSame('No', $variables['second_week']);
    }
}
