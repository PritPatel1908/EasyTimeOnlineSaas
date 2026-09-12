<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class schedular extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:schedular';

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
        // run artisan schedule:run every minute loop forever
        while (true) {
            $this->call('schedule:run');
            sleep(60);
        }
    }
}
