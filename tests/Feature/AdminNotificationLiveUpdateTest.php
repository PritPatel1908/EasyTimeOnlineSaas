<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Central\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class AdminNotificationLiveUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_notification_poll_returns_unread_notifications_as_json(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $user->notify(new class extends Notification
        {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return [
                    'title' => 'Tenant ready',
                    'message' => 'Acme tenant is ready to use.',
                    'url' => '/tenants/acme/edit',
                ];
            }

            public function toMail(object $notifiable): MailMessage
            {
                return (new MailMessage)
                    ->subject('Tenant ready');
            }
        });

        $this->actingAs($user)
            ->getJson('http://admin.saas.test/notifications/poll')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.title', 'Tenant ready');
    }
}
