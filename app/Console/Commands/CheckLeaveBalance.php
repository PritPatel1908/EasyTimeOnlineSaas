<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\CreditDebitBalanceBatch;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-leave-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Leave Balance';

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
            CreditDebitBalanceBatch::dispatch()
                ->onConnection('database_tenant')
                ->onQueue('processing');

            $this->info('Dispatched one leave-balance batch job to database_tenant:processing.');
        } else {
            $this->warn('No eligible employees found; no leave-balance jobs were dispatched.');
            Log::channel('attprocess')->debug('No user found');
        }
    }
}
