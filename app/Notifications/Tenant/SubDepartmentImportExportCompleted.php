<?php

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubDepartmentImportExportCompleted extends Notification implements ShouldQueue
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
            'title' => $this->operation === 'export' ? 'Sub Department export completed' : 'Sub Department import completed',
            'message' => $this->message,
            'url' => '/company-structure/sub-departments',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
