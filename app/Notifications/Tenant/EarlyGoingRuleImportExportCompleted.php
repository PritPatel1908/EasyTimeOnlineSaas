<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EarlyGoingRuleImportExportCompleted extends Notification implements ShouldQueue
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
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->operation === 'export'
                ? 'Early going rule export completed'
                : 'Early going rule import completed',
            'message' => $this->message,
            'url' => '/time-management/early-going-rules',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
