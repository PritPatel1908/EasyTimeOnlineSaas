<?php

namespace App\Console\Commands;

use App\Dms\EmpDms;
use Illuminate\Console\Command;

class InactiveEmpVisitorSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:inactive-emp-visitor-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inactive emp_visitor schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new EmpDms)->makeInactiveLeftEmployee();
        (new EmpDms)->makeInactiveByLastActiveTime();
        // TODO:remove comment if requried visitor inactiveexpired
        // (new VisitorDms())->makeInactiveExpiredVisitors();
    }
}
