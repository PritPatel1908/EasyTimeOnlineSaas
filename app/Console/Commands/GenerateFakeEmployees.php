<?php

namespace App\Console\Commands;

use App\Models\Tenant\RotationMuster;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\User;
use Illuminate\Console\Command;

class GenerateFakeEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-fake-emp {count=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Fake Employees for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating $count employee records...");

        // Generate the specified number of employees
        $users = User::factory()->count($count)->create();

        foreach ($users as $user) {
            $user->areas()->sync([1]);

            $user->shifts()->sync([1]);
        }

        // TODO: fix shift change and muster or create week off change and muster
        foreach ($users as $user) {
            $joinDate = $user->join_date;
            $endDate = now()->endOfYear();

            // For create shift change and muster
            $shift_change = ShiftChange::create([
                // 'user_id' => $user->id,
                'shift_type' => $user->shift_type,
                'shift_rotation_id' => $user->shift_rotation_id,
                'from_date' => $user->join_date,
                'is_forever' => true,
                'to_date' => $endDate,
            ]);

            if ($user->shifts) {
                $user_shifts = $user->shifts;
                $shift_change->shifts()->sync($user_shifts->pluck('id'));
            }

            $shift_change->Users()->sync($user->id);

            if ($user->shift_type == 'auto') {
                while ($joinDate <= $endDate) {
                    ShiftMuster::updateOrCreate(
                        [
                            'date' => $joinDate,
                            'user_id' => $user->id,
                            'is_locked' => false,
                        ],
                        [
                            'is_auto' => $shift_change->shift_type == 'auto' ? true : false,
                            'shift' => $user->shifts->pluck('id'),
                            'shift_change_id' => $shift_change->id,
                        ]
                    );
                    $joinDate->addDay();
                }
            } elseif ($user->shift_type == 'rotational') {
                $user_rotational_shift = $user->shift_rotation;
                while ($joinDate <= $endDate) {
                    ShiftMuster::updateOrCreate(
                        [
                            'date' => $joinDate,
                            'user_id' => $user->id,
                            'is_locked' => false,
                        ],
                        [
                            'shift' => RotationMuster::where('rotation_id', $user_rotational_shift->id)->where('date', $joinDate)->first()->shift_id,
                            'shift_change_id' => $shift_change->id,
                        ]
                    );
                    $joinDate->addDay();
                }
            } else {
                while ($joinDate <= $endDate) {
                    ShiftMuster::updateOrCreate(
                        [
                            'date' => $joinDate,
                            'user_id' => $user->id,
                            'is_locked' => false,
                        ],
                        [
                            'shift' => $user->shifts->pluck('id'),
                            'shift_change_id' => $shift_change->id,
                        ]
                    );
                    $joinDate->addDay();
                }
            }
        }

        $this->info("$count employee records generated successfully!");

        return 0;
    }
}
