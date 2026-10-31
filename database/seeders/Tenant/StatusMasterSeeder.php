<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\LeaveType;
use App\Models\Tenant\StatusMaster;
use Illuminate\Database\Seeder;

class StatusMasterSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'WOP' => 'The individual is present in week off.',
            'WHP' => 'The individual is present in week off or holiday.',
            'PWO' => 'The individual is present in week off.',
            'AWO' => 'The individual is absent on a week off.',
            'WOA' => 'The individual is absent on a week off.',
            'HLP' => 'The individual is absent due to a public or designated holiday.',
            'PHL' => 'The individual is absent due to a public or designated holiday.',
            'AHL' => 'The individual is absent on a public or designated holiday.',
            'HLA' => 'The individual is absent on a public or designated holiday.',
        ] as $code => $description) {
            StatusMaster::query()->firstOrCreate(
                ['code' => $code],
                [
                    'alias' => $code,
                    'description' => $description,
                ],
            );
        }

        foreach (LeaveType::query()->get(['code', 'description']) as $leaveType) {
            foreach ([
                'A'.$leaveType->code,
                $leaveType->code.'A',
                'P'.$leaveType->code,
                $leaveType->code.'P',
            ] as $code) {
                StatusMaster::query()->firstOrCreate(
                    ['code' => $code],
                    [
                        'alias' => $code,
                        'description' => $leaveType->description ?? $code,
                    ],
                );
            }
        }
    }
}
