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

    public $authUser;

    public $user;

    public $old;

    public $new;

    public $eventLog;

    /**
     * Create a new job instance.
     */
    public function __construct($authUser, $user, $old, $new, $eventLog)
    {
        $this->authUser = $authUser;
        $this->user = $user;
        $this->old = $old;
        $this->new = $new;
        $this->eventLog = $eventLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        activity()
            ->causedBy($this->authUser)
            ->performedOn($this->user)
            ->withProperties([
                'old' => $this->old,
                'attributes' => $this->new,
            ])
            ->event($this->eventLog)
            ->log($this->eventLog);
    }
}
