<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class HalfDayRuleImportExportCompleted extends Notification implements ShouldQueue
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
                ? 'Half-day rule export completed'
                : 'Half-day rule import completed',
            'message' => $this->message,
            'url' => '/time-management/half-day-rules',
            'download_url' => $this->downloadUrl,
            'type' => $this->operation,
        ];
    }
}
