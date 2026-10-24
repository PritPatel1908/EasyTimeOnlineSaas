<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Tenant\StoreShiftChangeApplicationRequest;
use App\Jobs\Tenant\CreateShiftChangeApplicationApprovals;
use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\ShiftChangeApplication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tests\TestCase;

class TenantShiftChangeApplicationModuleTest extends TestCase
{
    public function test_shift_change_application_uses_existing_tables_and_approval_relations(): void
    {
        $application = new ShiftChangeApplication;

        $this->assertSame('shift_change_applications', $application->getTable());
        $this->assertSame(MorphMany::class, get_class($application->approval_statuses()));
        $this->assertSame([
            'employee_codes',
            'shift_type',
            'shifts',
            'shift_rotation_code',
            'from_date',
            'is_forever',
            'to_date',
            'reason',
            'approval_status',
        ], ShiftChangeApplication::IMPORT_EXPORT_COLUMNS);
    }

    public function test_shift_change_application_approval_status_aggregates_flows(): void
    {
        $application = new ShiftChangeApplication;
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
    }

    public function test_shift_change_application_request_validates_shift_change_fields(): void
    {
        $rules = (new StoreShiftChangeApplicationRequest)->rules();

        $this->assertSame('required', $rules['shift_type'][0]);
        $this->assertArrayHasKey('shift_ids.*', $rules);
        $this->assertArrayHasKey('shift_rotation_id', $rules);
        $this->assertArrayHasKey('employee_filters.department_id.*', $rules);
        $this->assertContains('after_or_equal:from_date', $rules['to_date']);
        $this->assertSame(['nullable', 'boolean'], $rules['is_forever']);
    }

    public function test_shift_change_approval_creation_runs_as_a_queued_job(): void
    {
        $this->assertTrue(is_subclass_of(CreateShiftChangeApplicationApprovals::class, ShouldQueue::class));
    }
}
