<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CategoryImportExportCompleted extends Notification implements ShouldQueue
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
        return ['title' => $this->operation === 'export' ? 'Category export completed' : 'Category import completed', 'message' => $this->message, 'url' => '/employee-structure/categories', 'download_url' => $this->downloadUrl, 'type' => $this->operation];
    }
}
