<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\EmailConfiguration;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\User;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Collection;
use RuntimeException;

class ManualPunchApplicationEmailSender
{
    /**
     * @param  Collection<int, User>  $users
     * @return Collection<int, User>
     */
    public static function recipientsWithEmail(Collection $users): Collection
    {
        return $users
            ->filter(static fn (User $user): bool => trim((string) $user->email) !== '')
            ->unique(static fn (User $user): string => strtolower(trim((string) $user->email)))
            ->values();
    }

    public static function mailer(): Mailer
    {
        $settings = EmailConfiguration::settings();
        $config = self::mailerConfig($settings);
        /** @var Mailer $mailer */
        $mailer = app('mail.manager')->build($config);
        $mailer->alwaysFrom(
            $config['from']['address'],
            $config['from']['name'],
        );

        return $mailer;
    }

    /**
     * @return array<string, string>
     */
    public static function applicationVariables(ManualPunchApplication $application): array
    {
        return [
            'punch_type' => ManualPunch::punchTypeLabel($application->punch_type),
            'punch_date' => $application->punch_date?->format('Y-m-d') ?? '',
            'punch_time' => $application->punch_time?->format('H:i:s') ?? '',
            'reason' => $application->reason ?? '',
            'submitted_on' => $application->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function overtimeApplicationVariables(OvertimeApplication $application): array
    {
        return [
            'date' => $application->date?->format('Y-m-d') ?? '',
            'overtime_minutes' => (string) ($application->overtime_minutes ?? ''),
            'reason' => $application->reason ?? '',
        ];
    }

    /**
     * @param  array<string, string>  $variables
     */
    public static function sendTemplateEmail(
        Mailer $mailer,
        ?string $subject,
        ?string $content,
        array $variables,
        string $recipient,
    ): void {
        $render = static function (?string $value) use ($variables): string {
            return preg_replace_callback(
                '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/',
                static fn (array $matches): string => e($variables[$matches[1]] ?? ''),
                $value ?? '',
            ) ?? '';
        };

        $mailer->html(nl2br($render($content)), static function ($message) use ($recipient, $render, $subject): void {
            $message->to($recipient)->subject($render($subject));
        });
    }

    /**
     * @param  array<string, string|null>  $settings
     * @return array<string, mixed>
     */
    private static function mailerConfig(array $settings): array
    {
        $fromAddress = trim((string) ($settings['from_address'] ?? ''));
        $fromName = trim((string) ($settings['from_name'] ?? ''));

        if ($fromAddress === '' || $fromName === '') {
            throw new RuntimeException('Tenant email settings are missing a from address or name.');
        }

        $config = [
            'transport' => ($settings['transport'] ?? 'mail') === 'smtp' ? 'smtp' : 'mail',
            'from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
        ];

        if ($config['transport'] === 'smtp') {
            if (empty($settings['host']) || empty($settings['port'])) {
                throw new RuntimeException('Tenant SMTP settings are missing a host or port.');
            }

            $host = (string) $settings['host'];
            $password = (string) ($settings['password'] ?? '');
            $config += [
                'host' => $host,
                'port' => (int) $settings['port'],
                'username' => $settings['username'] ?? null,
                'password' => strtolower(trim($host)) === 'smtp.gmail.com'
                    ? (preg_replace('/\s+/', '', $password) ?? $password)
                    : $password,
                'scheme' => ($settings['encryption'] ?? null) === 'ssl' ? 'smtps' : 'smtp',
            ];
        }

        return $config;
    }
}
