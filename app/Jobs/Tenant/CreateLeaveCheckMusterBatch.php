<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateLeaveCheckMusterBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function handle(): void
    {
        User::query()
            ->where('id', '!=', 1)
            ->whereNull('left_date')
            ->where('is_inactive', false)
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    (new CreateLeaveCheckMuster($user))->handle();
                }
            });
    }
}
