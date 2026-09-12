<?php

namespace App\Console\Commands;

use App\Mail\BirthdayMail;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DailyBirthdayCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-birthday-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for daily birthdays and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('id', '!=', 1)->where('dob', Carbon::today())->where('left_date', null)->where('is_inactive', false)->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                $authUser = User::where('id', '1')->first();
                if ($user->email) {
                    Mail::to($user->email)->queue(new BirthdayMail($user, $authUser));
                }
            }
        } else {
            Log::channel('attprocess')->debug('No user found');
        }
    }
}
