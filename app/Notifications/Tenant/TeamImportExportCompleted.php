<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TeamImportExportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $operation, public string $message, public ?string $downloadUrl = null)
    {
        $this->onConnection('database_tenant')->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->operation === 'export' ? 'Team export completed' : 'Team import completed',
            'message' => $this->message,
            'url' => '/company-structure/teams',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
