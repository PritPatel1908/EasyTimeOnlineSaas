<?php

namespace App\Console\Commands;

use App\Models\Tenant\AttendanceLog;
use Illuminate\Console\Command;

class GenerateAttLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-logs {count=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Fake Logs for testing purpose.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating $count attendance logs...");

        AttendanceLog::factory()->count($count)->create();

        $this->info("$count attendance logs generated successfully!");

        return 0;
    }
}
