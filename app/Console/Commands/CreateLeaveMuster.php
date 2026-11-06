<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\CreateLeaveCheckMusterBatch;
use App\Jobs\Tenant\CreditDebitBalanceBatch;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class CreateLeaveMuster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-leave-muster';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Leave Muster';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hasEligibleUsers = User::query()
            ->where('id', '!=', 1)
            ->whereNull('left_date')
            ->where('is_inactive', false)
            ->exists();

        if ($hasEligibleUsers) {
            $startedFinancialYearId = FinancialYear::query()
                ->where('is_started', true)
                ->value('id');

            Bus::chain([
                new CreateLeaveCheckMusterBatch(
                    $startedFinancialYearId === null ? null : (int) $startedFinancialYearId,
                ),
                new CreditDebitBalanceBatch,
                new CreateLeaveCheckMusterBatch(
                    $startedFinancialYearId === null ? null : (int) $startedFinancialYearId,
                ),
            ])
                ->onConnection('database_tenant')
                ->onQueue('processing')
                ->dispatch();

            $this->info('Dispatched the leave-muster and balance batches in sequence to database_tenant:processing.');
        } else {
            $this->warn('No eligible employees found; no leave-muster jobs were dispatched.');
            Log::channel('attprocess')->debug('No user found');
        }
    }
}
