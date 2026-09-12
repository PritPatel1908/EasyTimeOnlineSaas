<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncDmsSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-dms-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync dms schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // AreaCheckJob::dispatch()->onQueue('low');
        // MachineCheckJob::dispatch()->onQueue('low');
        // EmpCheckJob::dispatch()->onQueue('low');
        // VisitorCheckJob::dispatch()->onQueue('low');
    }
}
