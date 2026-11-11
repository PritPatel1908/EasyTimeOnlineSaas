<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Mail\TenantEmailVerificationMail;
use App\Models\Tenant\EmailConfiguration;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class SendTenantEmailVerification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $verificationUrl,
        public readonly string $userName,
    ) {}

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null || $user->email !== $this->email || $user->email_verified_at !== null) {
            return;
        }

        $settings = EmailConfiguration::settings();
        $fromAddress = (string) ($settings['from_address'] ?? '');
        $fromName = (string) ($settings['from_name'] ?? '');

        if ($fromAddress === '' || $fromName === '') {
            throw new RuntimeException('Tenant email settings are missing a from address or name.');
        }

        $mailerConfig = [
            'transport' => ($settings['transport'] ?? 'mail') === 'smtp' ? 'smtp' : 'mail',
            'from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
        ];

        if ($mailerConfig['transport'] === 'smtp') {
            if (empty($settings['host']) || empty($settings['port'])) {
                throw new RuntimeException('Tenant SMTP settings are missing a host or port.');
            }

            $host = (string) $settings['host'];
            $password = (string) ($settings['password'] ?? '');
            $mailerConfig += [
                'host' => $host,
                'port' => (int) $settings['port'],
                'username' => $settings['username'] ?? null,
                'password' => strtolower(trim($host)) === 'smtp.gmail.com'
                    ? (preg_replace('/\s+/', '', $password) ?? $password)
                    : $password,
                'scheme' => ($settings['encryption'] ?? null) === 'ssl' ? 'smtps' : 'smtp',
            ];
        }

        $mailer = app('mail.manager')->build($mailerConfig);
        $mailer->to($this->email)->send(new TenantEmailVerificationMail(
            $this->verificationUrl,
            $this->userName,
            $fromAddress,
            $fromName,
        ));
    }
}
