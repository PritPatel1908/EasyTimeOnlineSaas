<?php

namespace Tests\Unit;

use App\Jobs\Tenant\UserStatusMuster;
use App\Models\Tenant\User;
use Illuminate\Contracts\Database\ModelIdentifier;
use ReflectionProperty;
use Tests\TestCase;

class UserStatusMusterTest extends TestCase
{
    public function test_job_serializes_user_id_without_restoring_tenant_model(): void
    {
        $user = new User();
        $user->setAttribute('id', 42);

        $serializedJob = serialize(new UserStatusMuster($user, '2026-01-01'));
        $restoredJob = unserialize($serializedJob);

        $this->assertStringNotContainsString(ModelIdentifier::class, $serializedJob);
        $this->assertSame(42, (new ReflectionProperty($restoredJob, 'userId'))->getValue($restoredJob));
    }

    public function test_job_restores_legacy_payload_without_restoring_tenant_model(): void
    {
        $user = new User();
        $user->setAttribute('id', 42);
        $job = new UserStatusMuster($user, '2026-01-01');

        $job->__unserialize([
            "\0*\0user" => new ModelIdentifier(User::class, 42, [], 'tenant'),
            "\0*\0joinDate" => '2026-01-01',
        ]);

        $this->assertSame(42, (new ReflectionProperty($job, 'userId'))->getValue($job));
    }
}
