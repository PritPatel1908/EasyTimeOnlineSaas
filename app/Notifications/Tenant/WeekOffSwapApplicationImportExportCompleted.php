<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WeekOffSwapApplicationImportExportCompleted extends Notification
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
     * @return array<string, string|null>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->operation === 'export'
                ? 'Week-off swap application export completed'
                : 'Week-off swap application import completed',
            'message' => $this->message,
            'download_url' => $this->downloadUrl,
            'url' => '/time-management/week-off-swap-applications',
            'type' => $this->operation,
        ];
    }
}
