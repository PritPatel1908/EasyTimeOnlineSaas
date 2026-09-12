<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Tenant $tenant, public bool $inApp = false) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return $this->inApp ? ['mail', 'database'] : ['mail'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tenant ready',
            'message' => ($this->tenant->name ?? $this->tenant->id).' database is ready to use.',
            'url' => route('admin.tenants.edit', $this->tenant->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your tenant is ready')
            ->greeting('Welcome to '.($this->tenant->name ?? $this->tenant->id))
            ->line('Your workspace has been created and is ready to use.');
    }
}
