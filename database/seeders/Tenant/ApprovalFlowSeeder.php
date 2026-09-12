<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\ApprovalFlow;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    public function run(): void
    {
        $approvalFlow = ApprovalFlow::create([
            'approval_flow_code' => 'Default Approval Flow',
        ]);

        $detail = $approvalFlow->approval_flow_details()->create([
            'level' => 1,
            'approval_flow_id' => $approvalFlow->id,
        ]);

        $detail->users()->sync([1]);
    }
}
