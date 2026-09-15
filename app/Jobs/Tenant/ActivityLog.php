<?php

namespace App\Jobs\Tenant;

use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivityLog implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $authUserClass;

    public $authUserId;

    public $userClass;

    public $userId;

    public $tenantId;

    public $old;

    public $new;

    public $eventLog;

    /**
     * Create a new job instance.
     */
    public function __construct($authUser, $user, $old, $new, $eventLog)
    {
        $this->authUserClass = $authUser !== null ? get_class($authUser) : null;
        $this->authUserId = $authUser?->getKey();
        $this->userClass = get_class($user);
        $this->userId = $user->getKey();
        $this->tenantId = tenancy()->initialized && tenancy()->tenant !== null
            ? tenancy()->tenant->getTenantKey()
            : null;
        $this->old = $old;
        $this->new = $new;
        $this->eventLog = $eventLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->tenantId !== null && (! tenancy()->initialized || tenancy()->tenant?->getTenantKey() !== $this->tenantId)) {
            tenancy()->initialize($this->tenantId);
        }

        $authUser = $this->authUserClass !== null && $this->authUserId !== null
            ? $this->authUserClass::withoutGlobalScopes()->find($this->authUserId)
            : null;
        $user = $this->userClass::withoutGlobalScopes()->find($this->userId);

        if ($user === null) {
            return;
        }

        activity()
            ->causedBy($authUser)
            ->performedOn($user)
            ->withProperties([
                'old' => $this->old,
                'attributes' => $this->new,
            ])
            ->event($this->eventLog)
            ->log($this->eventLog);
    }
}
