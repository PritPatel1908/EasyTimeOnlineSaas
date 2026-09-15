<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DepartmentImportExportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $operation, public string $message, public ?string $downloadUrl = null)
    {
        $this->onConnection('database_tenant')->onQueue('tenant');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->operation === 'export' ? 'Department export completed' : 'Department import completed',
            'message' => $this->message,
            'url' => '/company-structure/departments',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
