<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('alias');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('status_masters')->insert([
            [
                'code' => 'WO',
                'alias' => 'WO',
                'description' => 'The individual is absent without prior approval or notice.',
            ],
            [
                'code' => 'WOP',
                'alias' => 'WOP',
                'description' => 'The individual is present in week off.',
            ],
            [
                'code' => 'WHP',
                'alias' => 'WHP',
                'description' => 'The individual is present in week off or holiday.',
            ],
            [
                'code' => 'PP',
                'alias' => 'PP',
                'description' => 'The individual is present.',
            ],
            [
                'code' => 'PA',
                'alias' => 'PA',
                'description' => 'The individual is present and has reported to the location on time.',
            ],
            [
                'code' => 'AA',
                'alias' => 'AA',
                'description' => 'The individual is absent but has received prior approval for their absence.',
            ],
            [
                'code' => 'AP',
                'alias' => 'AP',
                'description' => 'The individual is absent and has not received prior approval for their absence.',
            ],
            [
                'code' => 'PL',
                'alias' => 'PL',
                'description' => 'PL',
            ],
            [
                'code' => 'PPL',
                'alias' => 'PPL',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or late coming.',
            ],
            [
                'code' => 'PLP',
                'alias' => 'PLP',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or late coming.',
            ],
            [
                'code' => 'APL',
                'alias' => 'APL',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or late coming.',
            ],
            [
                'code' => 'PLA',
                'alias' => 'PLA',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or late coming.',
            ],
            [
                'code' => 'PE',
                'alias' => 'PE',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or early going.',
            ],
            [
                'code' => 'PPO',
                'alias' => 'PPO',
                'description' => 'The individual is present and overtime.',
            ],
            [
                'code' => 'PFH',
                'alias' => 'PFH',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or first half.',
            ],
            [
                'code' => 'PSH',
                'alia s' => 'PSH',
                'description' => 'The individual arrives after the scheduled time but is still present for the session or second half.',
            ],
            [
                'code' => 'HL',
                'alias' => 'HL',
                'description' => 'The absence is due to a public or designated holiday.',
            ],
            [
                'code' => 'HLP',
                'alias' => 'HLP',
                'description' => 'The absence is due to a public or designated holiday.',
            ],
            [
                'code' => 'PHL',
                'alias' => 'PHL',
                'description' => 'The absence is due to a public or designated holiday.',
            ],
            [
                'code' => 'OD',
                'alias' => 'OD',
                'description' => 'Used in workplaces to indicate the employee is not working, usually with prior notice.',
            ],
            [
                'code' => 'VAC',
                'alias' => 'VAC',
                'description' => 'The individual is absent due to taking time off for personal reasons.',
            ],
            [
                'code' => 'EL',
                'alias' => 'EL',
                'description' => 'The individual is absent due to an unexpected emergency.',
            ],
            [
                'code' => 'PEL',
                'alias' => 'PEL',
                'description' => 'The individual is absent due to an unexpected emergency.',
            ],
            [
                'code' => 'ELP',
                'alias' => 'ELP',
                'description' => 'The individual is absent due to an unexpected emergency.',
            ],
            [
                'code' => 'AEL',
                'alias' => 'AEL',
                'description' => 'The individual is absent due to an unexpected emergency.',
            ],
            [
                'code' => 'ELA',
                'alias' => 'ELA',
                'description' => 'The individual is absent due to an unexpected emergency.',
            ],
            [
                'code' => 'SL',
                'alias' => 'SL',
                'description' => 'The individual is absent due to illness, with sick leave benefits applied.',
            ],
            [
                'code' => 'PSL',
                'alias' => 'PSL',
                'description' => 'The individual is absent due to illness, with sick leave benefits applied.',
            ],
            [
                'code' => 'SLP',
                'alias' => 'SLP',
                'description' => 'The individual is absent due to illness, with sick leave benefits applied.',
            ],
            [
                'code' => 'ASL',
                'alias' => 'ASL',
                'description' => 'The individual is absent due to illness, with sick leave benefits applied.',
            ],
            [
                'code' => 'SLA',
                'alias' => 'SLA',
                'description' => 'The individual is absent due to illness, with sick leave benefits applied.',
            ],
            [
                'code' => 'CL',
                'alias' => 'CL',
                'description' => 'CL.',
            ],
            [
                'code' => 'PCL',
                'alias' => 'PCL',
                'description' => 'PCL.',
            ],
            [
                'code' => 'CLP',
                'alias' => 'CLP',
                'description' => 'CLP.',
            ],
            [
                'code' => 'ACL',
                'alias' => 'ACL',
                'description' => 'ACL.',
            ],
            [
                'code' => 'CLA',
                'alias' => 'CLA',
                'description' => 'CLA.',
            ],
            [
                'code' => 'OL',
                'alias' => 'OL',
                'description' => 'OL.',
            ],
            [
                'code' => 'POL',
                'alias' => 'POL',
                'description' => 'POL.',
            ],
            [
                'code' => 'OLP',
                'alias' => 'OLP',
                'description' => 'OLP.',
            ],
            [
                'code' => 'AOL',
                'alias' => 'AOL',
                'description' => 'AOL.',
            ],
            [
                'code' => 'OLA',
                'alias' => 'OLA',
                'description' => 'OLA.',
            ],
            [
                'code' => 'CO',
                'alias' => 'CO',
                'description' => 'CO.',
            ],
            [
                'code' => 'PCO',
                'alias' => 'PCO',
                'description' => 'PCO.',
            ],
            [
                'code' => 'COP',
                'alias' => 'COP',
                'description' => 'COP.',
            ],
            [
                'code' => 'ACO',
                'alias' => 'ACO',
                'description' => 'ACO.',
            ],
            [
                'code' => 'COA',
                'alias' => 'COA',
                'description' => 'COA.',
            ],
            [
                'code' => 'LWP',
                'alias' => 'LWP',
                'description' => 'LWP.',
            ],
            [
                'code' => 'PLWP',
                'alias' => 'PLWP',
                'description' => 'PLWP.',
            ],
            [
                'code' => 'LWPP',
                'alias' => 'LWPP',
                'description' => 'LWPP.',
            ],
            [
                'code' => 'ALWP',
                'alias' => 'ALWP',
                'description' => 'ALWP.',
            ],
            [
                'code' => 'LWPA',
                'alias' => 'LWPA',
                'description' => 'LWPA.',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('att_status');
    }
};
