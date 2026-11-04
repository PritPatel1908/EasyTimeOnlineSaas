<?php

namespace Tests\Feature;

use App\Jobs\Tenant\CreateLeaveCheckMusterBatch;
use App\Jobs\Tenant\CreditDebitBalanceBatch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveBatchCommandDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.central.database' => ':memory:',
            'database.connections.sqlite.database' => ':memory:',
            'queue.connections.database_tenant.connection' => 'central',
            'queue.connections.database_tenant.table' => 'jobs',
        ]);
        DB::purge('central');
        DB::purge('sqlite');

        Schema::connection('central')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('left_date')->nullable();
            $table->boolean('is_inactive')->default(false);
            $table->softDeletes();
        });
        Schema::connection('central')->create('jobs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function test_leave_commands_dispatch_one_batch_job_for_multiple_eligible_employees(): void
    {
        DB::connection('central')->table('users')->insert([
            ['id' => 2, 'left_date' => null, 'is_inactive' => false, 'deleted_at' => null],
            ['id' => 3, 'left_date' => null, 'is_inactive' => false, 'deleted_at' => null],
            ['id' => 4, 'left_date' => null, 'is_inactive' => false, 'deleted_at' => null],
            ['id' => 5, 'left_date' => now(), 'is_inactive' => false, 'deleted_at' => null],
            ['id' => 6, 'left_date' => null, 'is_inactive' => true, 'deleted_at' => null],
        ]);
        Bus::fake();

        $this->artisan('app:create-leave-muster')
            ->expectsOutputToContain('one leave-muster batch job')
            ->assertExitCode(0);
        $this->artisan('app:check-leave-balance')
            ->expectsOutputToContain('one leave-balance batch job')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(CreateLeaveCheckMusterBatch::class, 1);
        Bus::assertDispatchedTimes(CreditDebitBalanceBatch::class, 1);
        Bus::assertDispatched(CreateLeaveCheckMusterBatch::class, fn (CreateLeaveCheckMusterBatch $job): bool => $job->connection === 'database_tenant'
            && $job->queue === 'processing');
        Bus::assertDispatched(CreditDebitBalanceBatch::class, fn (CreditDebitBalanceBatch $job): bool => $job->connection === 'database_tenant'
            && $job->queue === 'processing');
    }

    public function test_leave_commands_insert_batch_jobs_into_the_central_queue_table(): void
    {
        DB::connection('central')->table('users')->insert([
            ['id' => 2, 'left_date' => null, 'is_inactive' => false, 'deleted_at' => null],
        ]);

        $this->artisan('app:create-leave-muster')->assertExitCode(0);
        $this->artisan('app:check-leave-balance')->assertExitCode(0);

        $jobs = DB::connection('central')->table('jobs')->orderBy('id')->get();

        $this->assertCount(2, $jobs);
        $this->assertSame(['processing', 'processing'], $jobs->pluck('queue')->all());
        $this->assertSame(
            CreateLeaveCheckMusterBatch::class,
            json_decode($jobs[0]->payload, true, flags: JSON_THROW_ON_ERROR)['displayName'],
        );
        $this->assertSame(
            CreditDebitBalanceBatch::class,
            json_decode($jobs[1]->payload, true, flags: JSON_THROW_ON_ERROR)['displayName'],
        );
    }
}
