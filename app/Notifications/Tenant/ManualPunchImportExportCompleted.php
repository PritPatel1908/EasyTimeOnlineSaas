<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualPunchImportExportCompleted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $type,
        private readonly string $message,
        private readonly ?string $downloadUrl = null,
    ) {
    }

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
            'type' => $this->type,
            'message' => $this->message,
            'download_url' => $this->downloadUrl,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->line($this->message);
    }
}
