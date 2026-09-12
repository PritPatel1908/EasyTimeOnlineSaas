<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\CreateLeaveCheckMuster;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
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
        $users = User::where('id', '!=', 1)->where('left_date', null)->where('is_inactive', false)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                CreateLeaveCheckMuster::dispatch($user)->onQueue('processing');
            }
        } else {
            Log::channel('attprocess')->debug('No user found');
        }
    }
}
