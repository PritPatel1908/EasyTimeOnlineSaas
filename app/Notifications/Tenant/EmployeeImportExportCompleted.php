<?php

namespace App\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class EmployeeImportExportCompleted extends Notification
{
    use Queueable;

    public function __construct(private string $type, private string $message, private ?string $url = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage(['type' => $this->type, 'message' => $this->message, 'url' => $this->url]);
    }
}
