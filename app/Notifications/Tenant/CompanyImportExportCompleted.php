<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CompanyImportExportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $operation,
        public string $message,
        public ?string $downloadUrl = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->operation === 'export'
                ? 'Company export completed'
                : 'Company import completed',
            'message' => $this->message,
            'url' => '/company-structure/companies',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
