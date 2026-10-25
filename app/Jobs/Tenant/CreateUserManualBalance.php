<?php

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\ManualLeaveBalance;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUserManualBalance implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ManualLeaveBalance $manualLeaveBalance)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->manualLeaveBalance->getKey();
    }

    public function handle(): void
    {
        GeneralHelper::syncManualLeaveBalanceToAccounts($this->manualLeaveBalance);
    }
}
