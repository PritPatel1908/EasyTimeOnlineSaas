<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SettingsController extends Controller
{
    /**
     * @var array<string, array<string, string>>
     */
    private const SECTIONS = [
        'General Settings' => [
            'profile-settings' => 'Profile Settings',
            'security-settings' => 'Security Settings',
            'notification-settings' => 'Notifications',
            'connected-apps' => 'Connected Apps',
        ],
        'Website Settings' => [
            'business-settings' => 'Business Settings',
            'seo-settings' => 'SEO Settings',
            'localization-settings' => 'Localization',
            'prefixes' => 'Prefixes',
            'preferences' => 'Preferences',
            'appearance' => 'Appearance',
            'language' => 'Language',
            'authentication-settings' => 'Authentication Settings',
            'ai-settings' => 'AI Settings',
        ],
        'App Settings' => [
            'salary-settings' => 'Salary Settings',
            'leave-settings' => 'Leave Settings',
            'approval-settings' => 'Approval Settings',
            'invoice-settings' => 'Invoice Settings',
        ],
        'System Settings' => [
            'email-settings' => 'Email Settings',
            'email-template' => 'Email Template',
            'sms-settings' => 'SMS Settings',
            'sms-template' => 'SMS Template',
            'otp-settings' => 'OTP Settings',
            'gdpr' => 'GDPR',
            'maintenance-mode' => 'Maintenance Mode',
        ],
        'Financial Settings' => [
            'payment-gateways' => 'Payment Gateways',
            'currencies' => 'Currencies',
        ],
        'Other Settings' => [
            'cronjob' => 'Cronjob',
            'cronjob-schedule' => 'Cronjob Schedule',
            'custom-css' => 'Custom CSS',
            'custom-js' => 'Custom JS',
            'storage-settings' => 'Storage',
            'ban-ip-address' => 'Ban IP Address',
            'backup' => 'Backup',
            'clear-cache' => 'Clear Cache',
        ],
    ];

    public function index(): View
    {
        return view('tenant.settings.index', [
            'sections' => self::SECTIONS,
        ]);
    }

    public function show(string $tenant, string $page): View
    {
        return view($this->viewPath($page));
    }

    /**
     * @return array<string, string>
     */
    public static function pages(): array
    {
        $pages = [];

        foreach (self::sections() as $section) {
            $pages += $section;
        }

        return $pages;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function sections(): array
    {
        return self::SECTIONS;
    }

    private function viewPath(string $page): string
    {
        foreach (self::SECTIONS as $section => $pages) {
            if (array_key_exists($page, $pages)) {
                $sectionDirectory = strtolower(str_replace(' ', '-', $section));

                return 'tenant.settings.'.$sectionDirectory.'.'.$page;
            }
        }

        abort(404);
    }
}
