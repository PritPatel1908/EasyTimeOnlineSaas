<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WeekOffSwapImportExportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $operation,
        public string $message,
        public ?string $downloadUrl = null,
    ) {
        $this->onConnection('database_tenant')->onQueue('notifications');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->operation === 'export'
                ? 'Week-off swap export completed'
                : 'Week-off swap import completed',
            'message' => $this->message,
            'download_url' => $this->downloadUrl,
            'url' => '/time-management/week-off-swaps',
            'type' => $this->operation,
        ];
    }
}
