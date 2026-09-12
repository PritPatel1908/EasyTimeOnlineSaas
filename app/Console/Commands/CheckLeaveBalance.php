<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\CreditDebitBalance;
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
        $users = User::where('id', '!=', 1)->where('left_date', null)->where('is_inactive', false)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                CreditDebitBalance::dispatch($user)->onQueue('processing');
            }
        } else {
            Log::channel('attprocess')->debug('No user found');
        }
    }
}
