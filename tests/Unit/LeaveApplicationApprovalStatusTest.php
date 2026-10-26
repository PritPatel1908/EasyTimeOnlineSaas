<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\LeaveApplication;
use Tests\TestCase;

class LeaveApplicationApprovalStatusTest extends TestCase
{
    public function test_leave_application_aggregates_approval_flow_statuses(): void
    {
        $application = new LeaveApplication;

        $application->setRelation('approval_statuses', collect([
            new ApprovalStatus(['approval_status' => 'approved']),
            new ApprovalStatus(['approval_status' => 'pending']),
        ]));
        $this->assertSame('pending', $application->overallApprovalStatus());

        $application->setRelation('approval_statuses', collect([
            new ApprovalStatus(['approval_status' => 'approved']),
            new ApprovalStatus(['approval_status' => 'rejected']),
        ]));
        $this->assertSame('rejected', $application->overallApprovalStatus());

        $application->setRelation('approval_statuses', collect([
            new ApprovalStatus(['approval_status' => 'approved']),
        ]));
        $this->assertSame('approved', $application->overallApprovalStatus());

        $application->setRelation('approval_statuses', collect([
            new ApprovalStatus(['approval_status' => 'cancelled']),
        ]));
        $this->assertSame('cancelled', $application->overallApprovalStatus());
    }
}
