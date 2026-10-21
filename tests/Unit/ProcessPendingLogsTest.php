<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\AttendanceLog;
use Illuminate\Contracts\Database\ModelIdentifier;
use Tests\TestCase;

class ProcessPendingLogsTest extends TestCase
{
    public function test_job_serializes_attendance_log_id_without_restoring_tenant_model(): void
    {
        $log = new AttendanceLog();
        $log->setAttribute('id', 42);

        $serializedJob = serialize(new ProcessPendingLogs($log));
        $restoredJob = unserialize($serializedJob);

        $this->assertStringNotContainsString(ModelIdentifier::class, $serializedJob);
        $this->assertSame('42', $restoredJob->uniqueId());
    }
}
