<?php

namespace App\Jobs\Tenant;

use App\Helpers\AfterUserImportProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class AfterImportUser implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $users;

    public bool $options = false;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function uniqueId(): string
    {
        return (string) tenant('id') . ':' . implode(',', Arr::wrap($this->users));
    }

    public function handle(): void
    {
        AfterUserImportProcessor::processAfterImportUsers($this->users, $this->options);
    }
}
