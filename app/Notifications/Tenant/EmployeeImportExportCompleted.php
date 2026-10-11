<?php

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EmployeeImportExportCompleted extends Notification implements ShouldQueue
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
            'title' => $this->operation === 'export' ? 'Employee export completed' : 'Employee import completed',
            'message' => $this->message,
            'url' => '/employee-structure/employees',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
