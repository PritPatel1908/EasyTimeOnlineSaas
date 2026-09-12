<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\DailyAtten;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('id', '!=', 1)->where('left_date', null)->where('is_inactive', false)->get();
        if ($users->count() > 0) {
            // foreach ($users as $user) {
            $authUser = User::where('id', '1')->first();
            DailyAtten::dispatch($users, Carbon::today(), $authUser)->onQueue('processing');
            // }
        } else {
            Log::channel('attprocess')->debug('No user found');
        }
    }
}
