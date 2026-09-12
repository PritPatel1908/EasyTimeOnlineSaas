<?php

namespace Tests\Feature;

use App\Models\Central\User;
use App\Notifications\LoginOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_sends_a_new_otp_for_an_active_session(): void
    {
        $user = User::factory()->create();

        Notification::fake();

        $response = $this->withSession([
            'auth.otp' => [
                'user_id' => $user->getKey(),
                'email' => $user->email,
                'code' => 'old-hash',
                'expires_at' => now()->addMinutes(10),
                'sent_at' => now(),
                'attempts' => 0,
            ],
        ])->post('/login/otp/resend');

        $response->assertRedirect();
        Notification::assertSentOnDemand(LoginOtp::class);
    }
}
