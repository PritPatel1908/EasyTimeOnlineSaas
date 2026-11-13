<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\OvertimeApplication;
use Tests\TestCase;

class OvertimeApplicationApprovalStatusTest extends TestCase
{
    public function test_overtime_application_reports_cancelled_approval_status(): void
    {
        $application = new OvertimeApplication;
        $application->setRelation('approval_statuses', collect([
            new ApprovalStatus(['approval_status' => 'cancelled']),
        ]));

        $this->assertSame('cancelled', $application->overallApprovalStatus());
    }
}
