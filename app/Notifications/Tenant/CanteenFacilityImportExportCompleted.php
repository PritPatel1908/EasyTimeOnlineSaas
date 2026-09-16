<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CanteenFacilityImportExportCompleted extends Notification implements ShouldQueue
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
        return ['title' => $this->operation === 'export' ? 'Canteen Facility export completed' : 'Canteen Facility import completed', 'message' => $this->message, 'url' => '/company-structure/canteen-facilities', 'download_url' => $this->downloadUrl, 'type' => $this->operation];
    }
}
