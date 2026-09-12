<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\CarryForwardLeaveBalance as JobsCarryForwardLeaveBalance;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CarryForwardLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:carry-forward-leave-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carry Forward Leave Balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('id', '!=', 1)->where('left_date', null)->where('is_inactive', false)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                JobsCarryForwardLeaveBalance::dispatch($user)->onQueue('processing');
            }
        } else {
            Log::channel('attprocess')->debug('No users found');
        }
    }
}
