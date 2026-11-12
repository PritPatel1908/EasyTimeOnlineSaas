<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\SendTenantEmailVerification;
use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\EmailConfiguration;
use App\Models\Tenant\EmailTemplate;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\User;
use App\Support\TenantCompanyBranding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    /**
     * @var array<string, array<string, string>>
     */
    private const FORMAT_PRESETS = [
        'datetime_format' => [
            'd/m/Y H:i' => 'd/m/Y H:i',
            'm/d/Y h:i A' => 'm/d/Y h:i A',
            'Y-m-d H:i' => 'Y-m-d H:i',
            'd M Y, h:i A' => 'd M Y, h:i A',
            'd-m-Y h:i A' => 'd-m-Y h:i A',
        ],
        'date_format' => [
            'Y-m-d' => 'Y-m-d',
            'm/d/Y' => 'm/d/Y',
            'd/m/Y' => 'd/m/Y',
            'Y/m/d' => 'Y/m/d',
            'd M Y' => 'd M Y',
            'd-m-Y' => 'd-m-Y',
        ],
        'time_format' => [
            'H:i' => 'H:i',
            'h:i A' => 'h:i A',
            'H:i:s' => 'H:i:s',
            'h:i:s A' => 'h:i:s A',
        ],
    ];

    /**
     * @var list<string>
     */
    private const FILE_TYPE_OPTIONS = ['jpg', 'gif', 'png'];

    /**
     * @var list<string>
     */
    private const COMPANY_BASIC_SETTING_KEYS = [
        'company_name',
        'company_email',
        'company_phone',
        'company_web',
    ];

    /**
     * @var list<string>
     */
    private const COMPANY_IMAGE_SETTING_KEYS = TenantCompanyBranding::IMAGE_KEYS;

    /**
     * @var list<string>
     */
    private const ATTENDANCE_SETTING_KEYS = [
        'punch_direction_consider_same',
        'attendance_schedule_time',
        'monthly_leave_check_date',
        'yearly_leave_check_date',
        'password_attempts_count',
    ];

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
        'App Settings' => [
            'salary-settings' => 'Salary Settings',
            'leave-settings' => 'Leave Settings',
            'approval-settings' => 'Approval Settings',
            'invoice-settings' => 'Invoice Settings',
        ],
        'System Settings' => [
            'default-settings' => 'Default Settings',
            'email-settings' => 'Email Settings',
            'email-template' => 'Email Template',
            'sms-settings' => 'SMS Settings',
            'sms-template' => 'SMS Template',
            'otp-settings' => 'OTP Settings',
            'gdpr' => 'GDPR',
            'maintenance-mode' => 'Maintenance Mode',
            'seo-settings' => 'SEO Settings',
            'localization-settings' => 'Localization',
            'prefixes' => 'Prefixes',
            'preferences' => 'Preferences',
            'appearance' => 'Appearance',
            'language' => 'Language',
            'authentication-settings' => 'Authentication Settings',
            'ai-settings' => 'AI Settings',
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
        $data = [];

        if ($page === 'profile-settings') {
            $user = $this->profileUser();
            $data = [
                'employee' => $user->load([
                    'personalInfo',
                    'addresses',
                    'education',
                    'experiences',
                    'family_details',
                ]),
                'bloodGroups' => BloodGroup::query()->orderBy('name')->get(),
            ];
        } elseif ($page === 'email-settings') {
            $data['emailConfiguration'] = EmailConfiguration::settings();
        } elseif ($page === 'email-template') {
            $data['emailTemplates'] = EmailTemplate::query()
                ->orderBy('template_type')
                ->orderBy('id')
                ->get();
        } elseif ($page === 'default-settings') {
            $data['formatPresets'] = self::FORMAT_PRESETS;
            $data['defaultSettings'] = GeneralConfiguration::withTrashed()
                ->whereIn('key', [
                    ...array_keys(self::FORMAT_PRESETS),
                    ...self::ATTENDANCE_SETTING_KEYS,
                    'allowed_files',
                    'max_file_size',
                    ...self::COMPANY_BASIC_SETTING_KEYS,
                    ...self::COMPANY_IMAGE_SETTING_KEYS,
                ])
                ->pluck('value', 'key')
                ->all();
            $data['allowedFileTypes'] = self::FILE_TYPE_OPTIONS;
            $data['selectedAllowedFiles'] = $this->selectedAllowedFiles(
                $data['defaultSettings']['allowed_files'] ?? null
            );
            $data['companyImageUrls'] = app(TenantCompanyBranding::class)->imageUrls($tenant);
        } elseif ($page === 'security-settings') {
            $securityUser = $this->profileUser();
            $passwordHistories = $securityUser
                ->password_histories()
                ->latest()
                ->limit(3)
                ->get(['id', 'created_at']);
            $data = [
                'securityUser' => $securityUser,
                'passwordHistories' => $passwordHistories,
                'lastPasswordChangedAt' => $passwordHistories->first()?->created_at,
            ];
        }

        return view($this->viewPath($page), $data);
    }

    public function storeEmailTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'template_type' => [
                'required',
                'string',
                Rule::in(EmailTemplate::TEMPLATE_TYPES),
            ],
            'email_content' => ['required', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        EmailTemplate::query()->create([
            'title' => $validated['title'],
            'template_type' => $validated['template_type'],
            'email_content' => $validated['email_content'],
            'status' => $validated['status'],
        ]);

        return redirect()->to(url('email-template'))
            ->with('success', 'Email template created successfully.');
    }

    public function updateEmailTemplate(Request $request, string $tenant, int|string $emailTemplate): RedirectResponse
    {
        $template = EmailTemplate::query()->findOrFail($emailTemplate);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'template_type' => [
                'required',
                'string',
                Rule::in(EmailTemplate::TEMPLATE_TYPES),
            ],
            'email_content' => ['required', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email_template_form' => ['required', Rule::in(['edit'])],
        ]);

        $template->update([
            'title' => $validated['title'],
            'template_type' => $validated['template_type'],
            'email_content' => $validated['email_content'],
            'status' => $validated['status'],
        ]);

        return redirect()->to(url('email-template'))
            ->with('success', 'Email template updated successfully.');
    }

    public function updateDefaultSettings(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (self::FORMAT_PRESETS as $key => $presets) {
            $rules[$key] = ['required', 'string', Rule::in([...array_keys($presets), 'custom'])];
            $rules['custom_'.$key] = [
                'required_if:'.$key.',custom',
                'nullable',
                'string',
                'max:100',
            ];
        }
        $rules += [
            'punch_direction_consider_same' => ['required', 'integer', 'min:0'],
            'attendance_schedule_time' => ['required', 'date_format:H:i:s'],
            'monthly_leave_check_date' => ['nullable', 'integer', 'between:1,31'],
            'yearly_leave_check_date' => ['nullable', 'integer', 'between:1,31'],
            'password_attempts_count' => ['required', 'integer', 'min:1'],
            'allowed_files' => ['sometimes', 'nullable', 'array'],
            'allowed_files.*' => ['string', Rule::in(self::FILE_TYPE_OPTIONS), 'distinct'],
            'max_file_size' => ['nullable', 'integer', 'min:1'],
        ];

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated): void {
            foreach ([
                ...array_keys(self::FORMAT_PRESETS),
                ...self::ATTENDANCE_SETTING_KEYS,
                'allowed_files',
                'max_file_size',
            ] as $key) {
                if ($key === 'allowed_files') {
                    $value = json_encode($validated[$key] ?? [], JSON_THROW_ON_ERROR);
                } elseif ($key === 'max_file_size') {
                    $value = isset($validated[$key]) ? (string) $validated[$key] : null;
                } else {
                    $value = isset(self::FORMAT_PRESETS[$key]) && $validated[$key] === 'custom'
                        ? trim((string) $validated['custom_'.$key])
                        : $validated[$key];
                }

                $configuration = GeneralConfiguration::withTrashed()->firstOrNew(['key' => $key]);
                $configuration->value = $value;

                if ($configuration->trashed()) {
                    $configuration->restore();
                } else {
                    $configuration->save();
                }
            }
        });

        return redirect()->to(url('default-settings'))
            ->with('success', 'Default settings updated successfully.');
    }

    public function updateCompanySettings(Request $request): RedirectResponse
    {
        $rules = [
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_web' => ['nullable', 'string', 'max:255'],
        ];
        foreach (self::COMPANY_IMAGE_SETTING_KEYS as $key) {
            $rules[$key] = ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'];
        }

        $validated = $request->validate($rules);

        $values = [];
        foreach (self::COMPANY_BASIC_SETTING_KEYS as $key) {
            $value = trim((string) ($validated[$key] ?? ''));
            $values[$key] = $value === '' ? null : $value;
        }

        $imagePaths = [];
        foreach (self::COMPANY_IMAGE_SETTING_KEYS as $key) {
            if ($request->hasFile($key)) {
                $storedPath = $request->file($key)->store('company-images', 'public');
                if (! is_string($storedPath)) {
                    throw new RuntimeException('Unable to store the uploaded company image.');
                }

                $imagePaths[$key] = $storedPath;
                $values[$key] = $storedPath;
            }
        }

        $previousImagePaths = $imagePaths === []
            ? []
            : GeneralConfiguration::withTrashed()
                ->whereIn('key', array_keys($imagePaths))
                ->pluck('value', 'key')
                ->all();

        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                $configuration = GeneralConfiguration::withTrashed()->firstOrNew(['key' => $key]);
                $configuration->value = $value;

                if ($configuration->trashed()) {
                    $configuration->restore();
                } else {
                    $configuration->save();
                }
            }
        });

        foreach ($previousImagePaths as $previousPath) {
            if (is_string($previousPath) && str_starts_with($previousPath, 'company-images/')) {
                Storage::disk('public')->delete($previousPath);
            }
        }

        return redirect()->to(url('default-settings'))
            ->with('success', 'Company information updated successfully.');
    }

    public function companyImage(string $tenant, string $key): StreamedResponse
    {
        abort_unless(in_array($key, self::COMPANY_IMAGE_SETTING_KEYS, true), 404);

        $imagePath = GeneralConfiguration::query()->where('key', $key)->value('value');
        abort_unless(
            is_string($imagePath)
                && str_starts_with($imagePath, 'company-images/')
                && Storage::disk('public')->exists($imagePath),
            404,
        );

        return Storage::disk('public')->response($imagePath);
    }

    /**
     * @return list<string>
     */
    private function selectedAllowedFiles(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        $files = is_array($decoded) ? $decoded : [$value];
        $files = array_filter($files, 'is_string');

        return array_values(array_intersect(self::FILE_TYPE_OPTIONS, $files));
    }

    public function updateEmailSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transport' => ['required', Rule::in(['mail', 'smtp'])],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'host' => ['required_if:transport,smtp', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:transport,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
        ]);

        $settings = [];
        foreach ([
            'transport',
            'from_address',
            'from_name',
            'host',
            'port',
            'username',
            'encryption',
        ] as $key) {
            if (array_key_exists($key, $validated)) {
                $settings[$key] = $validated[$key] === null ? null : (string) $validated[$key];
            }
        }

        if (($validated['password'] ?? '') !== '') {
            $settings['password'] = $validated['transport'] === 'smtp'
                ? $this->normalizeSmtpPassword((string) ($validated['host'] ?? ''), $validated['password'])
                : $validated['password'];
        }

        EmailConfiguration::saveSettings($settings);

        return redirect()->to(url('email-settings'))
            ->with('success', 'Email settings updated successfully.');
    }

    public function deleteEmailSettings(): RedirectResponse
    {
        EmailConfiguration::deleteSettings();

        return redirect()->to(url('email-settings'))
            ->with('success', 'Email configuration deleted successfully.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $this->profileUser();
        $validated = $request->validate([
            'new_email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
        ]);
        $newEmail = strtolower(trim($validated['new_email']));

        if ($user->email !== null && strcasecmp($newEmail, $user->email) === 0) {
            throw ValidationException::withMessages([
                'new_email' => 'Enter an email address different from your current address.',
            ]);
        }

        $settings = EmailConfiguration::settings();

        if (empty($settings['from_address']) || empty($settings['from_name'])) {
            throw ValidationException::withMessages([
                'new_email' => 'Configure your email transport before changing your email address.',
            ]);
        }

        if (($settings['transport'] ?? 'mail') === 'smtp') {
            if (empty($settings['host']) || empty($settings['port'])) {
                throw ValidationException::withMessages([
                    'new_email' => 'Complete your SMTP host and port settings before changing your email address.',
                ]);
            }
        }

        $routeName = (string) $request->route()?->getName();
        $settingsRouteSuffix = 'tenant.settings.email.update';
        $routePrefix = str_ends_with($routeName, $settingsRouteSuffix)
            ? substr($routeName, 0, -strlen($settingsRouteSuffix))
            : '';
        $verificationUrl = URL::temporarySignedRoute(
            $routePrefix.'tenant.email.verify',
            now()->addHour(),
            [
                'tenant' => $request->route('tenant'),
                'id' => $user->getKey(),
                'hash' => sha1($newEmail),
            ],
        );

        DB::transaction(function () use ($user, $newEmail, $verificationUrl): void {
            $user->email = $newEmail;
            $user->email_verified_at = null;
            $user->save();

            SendTenantEmailVerification::dispatch(
                (int) $user->getKey(),
                $newEmail,
                $verificationUrl,
                (string) ($user->name ?? ''),
            )
                ->onConnection('database_tenant')
                ->onQueue('notifications')
                ->afterCommit();
        });

        return redirect()->to(url('security-settings'))
            ->with('success', 'Email address updated. A verification email has been queued for your new email address.');
    }

    public function removeEmail(): RedirectResponse
    {
        $user = $this->profileUser();

        DB::transaction(function () use ($user): void {
            $user->email = null;
            $user->email_verified_at = null;
            $user->save();
        });

        return redirect()->to(url('security-settings'))
            ->with('success', 'Email address removed successfully.');
    }

    public function toggleTwoFactorAuthentication(): RedirectResponse
    {
        $user = $this->profileUser();

        if (! $user->two_factor_enabled
            && (! is_string($user->email) || filter_var($user->email, FILTER_VALIDATE_EMAIL) === false)) {
            return back()->withErrors([
                'email' => 'Add a valid email address before enabling two-factor authentication.',
            ]);
        }

        $user->two_factor_enabled = ! $user->two_factor_enabled;
        $user->save();

        $message = $user->two_factor_enabled
            ? 'Two-factor authentication enabled successfully.'
            : 'Two-factor authentication disabled successfully.';

        return redirect()->to(url('security-settings'))->with('success', $message);
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->route('id'));
        $hash = (string) $request->route('hash');

        abort_unless(
            is_string($user->email) && hash_equals(sha1($user->email), $hash),
            403,
        );

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
            $user->save();
        }

        return redirect('/settings/security-settings')
            ->with('success', 'Your email address has been verified successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->profileUser();
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password:tenant'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except([
                    'current_password',
                    'new_password',
                    'new_password_confirmation',
                ]));
        }

        $validated = $validator->validated();

        if (Hash::check($validated['new_password'], $user->password)) {
            return back()
                ->withErrors(['new_password' => 'Choose a password you have not used before.']);
        }

        foreach ($user->password_histories()->select('password')->cursor() as $history) {
            if (Hash::check($validated['new_password'], $history->password)) {
                return back()
                    ->withErrors(['new_password' => 'Choose a password you have not used before.']);
            }
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->password = $validated['new_password'];
            $user->save();

            $user->password_histories()->create([
                'password' => $user->password,
            ]);
        });

        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/login')
            ->with('success', 'Password changed successfully. Please sign in with your new password.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->profileUser();
        $validated = $request->validate([
            'fname' => ['required', 'string', 'max:100'],
            'mname' => ['nullable', 'string', 'max:100'],
            'lname' => ['nullable', 'string', 'max:100'],
            'number' => ['nullable', 'string', 'max:30'],
            'aadhar_number' => ['nullable', 'string', 'max:255'],
            'uan_number' => ['nullable', 'string', 'max:255'],
            'esic_number' => ['nullable', 'string', 'max:255'],
            'pan_number' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'blood_group_id' => ['nullable', 'integer', 'exists:blood_groups,id'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['nullable', 'string', 'max:255'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*' => ['nullable', 'string', 'max:255'],
            'emergency_name' => ['nullable', 'array'],
            'emergency_name.*' => ['nullable', 'string', 'max:255'],
            'emergency_number' => ['nullable', 'array'],
            'emergency_number.*' => ['nullable', 'string', 'max:30'],
            'emergency_address' => ['nullable', 'array'],
            'emergency_address.*' => ['nullable', 'string', 'max:1000'],
            'flat_building' => ['nullable', 'string', 'max:255'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'flore' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'current_flat_building' => ['nullable', 'string', 'max:255'],
            'current_house_no' => ['nullable', 'string', 'max:255'],
            'current_flore' => ['nullable', 'string', 'max:255'],
            'current_street' => ['nullable', 'string', 'max:255'],
            'current_landmark' => ['nullable', 'string', 'max:255'],
            'current_country' => ['nullable', 'string', 'max:255'],
            'current_state' => ['nullable', 'string', 'max:255'],
            'current_city' => ['nullable', 'string', 'max:255'],
            'current_pincode' => ['nullable', 'string', 'max:20'],
            'is_current_address' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_ifsc_code' => ['nullable', 'string', 'max:255'],
            'bank_account_branch_name' => ['nullable', 'string', 'max:255'],
            'educations' => ['nullable', 'array'],
            'educations.*.education' => ['nullable', 'string', 'max:255'],
            'educations.*.year' => ['nullable', 'string', 'max:20'],
            'educations.*.university_name' => ['nullable', 'string', 'max:255'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.previous_employeer' => ['nullable', 'string', 'max:255'],
            'experiences.*.designation' => ['nullable', 'string', 'max:255'],
            'experiences.*.join_date' => ['nullable', 'date'],
            'experiences.*.left_date' => ['nullable', 'date'],
            'experiences.*.ctc' => ['nullable', 'numeric', 'min:0'],
            'family_details' => ['nullable', 'array'],
            'family_details.*.relation' => ['nullable', 'string', 'max:255'],
            'family_details.*.name' => ['nullable', 'string', 'max:255'],
            'family_details.*.dob' => ['nullable', 'date'],
            'family_details.*.is_nominee' => ['nullable', 'boolean'],
            'family_details.*.occupation' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $user->update([
                'fname' => trim($validated['fname']),
                'mname' => trim($validated['mname'] ?? ''),
                'lname' => trim($validated['lname'] ?? ''),
                'name' => trim(implode(' ', array_filter([
                    trim($validated['fname']),
                    trim($validated['mname'] ?? ''),
                    trim($validated['lname'] ?? ''),
                ]))),
                'number' => $validated['number'] ?? null,
                'aadhar_number' => $validated['aadhar_number'] ?? null,
                'uan_number' => $validated['uan_number'] ?? null,
                'esic_number' => $validated['esic_number'] ?? null,
                'pan_number' => $validated['pan_number'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'blood_group_id' => $validated['blood_group_id'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_ifsc_code' => $validated['bank_ifsc_code'] ?? null,
                'bank_account_branch_name' => $validated['bank_account_branch_name'] ?? null,
            ]);

            $listValues = static fn(string $field): array => array_values(array_filter(
                array_map(static fn($value): string => trim((string) $value), $validated[$field] ?? []),
                static fn(string $value): bool => $value !== '',
            ));

            $emergencyFields = ['emergency_name', 'emergency_number', 'emergency_address'];
            $emergencyValues = array_fill_keys($emergencyFields, []);
            $emergencyRowCount = max(array_map(
                static fn(string $field): int => count($validated[$field] ?? []),
                $emergencyFields,
            ));

            for ($index = 0; $index < $emergencyRowCount; $index++) {
                $row = array_map(
                    static fn(string $field): string => trim((string) (($validated[$field] ?? [])[$index] ?? '')),
                    $emergencyFields,
                );

                if (count(array_filter($row, static fn(string $value): bool => $value !== '')) === 0) {
                    continue;
                }

                foreach ($emergencyFields as $fieldIndex => $field) {
                    $emergencyValues[$field][] = $row[$fieldIndex];
                }
            }

            $user->personalInfo()->updateOrCreate([], [
                'height' => $validated['height'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'languages' => $listValues('languages') ?: null,
                'hobbies' => $listValues('hobbies') ?: null,
                'emergency_name' => $emergencyValues['emergency_name'] !== []
                    ? json_encode($emergencyValues['emergency_name'], JSON_THROW_ON_ERROR)
                    : null,
                'emergency_number' => $emergencyValues['emergency_number'] !== []
                    ? json_encode($emergencyValues['emergency_number'], JSON_THROW_ON_ERROR)
                    : null,
                'emergency_address' => $emergencyValues['emergency_address'] !== []
                    ? json_encode($emergencyValues['emergency_address'], JSON_THROW_ON_ERROR)
                    : null,
            ]);

            $addressAttributes = [];
            foreach ([
                'flat_building', 'house_no', 'flore', 'street', 'landmark', 'country', 'state', 'city', 'pincode',
                'current_flat_building', 'current_house_no', 'current_flore', 'current_street',
                'current_landmark', 'current_country', 'current_state', 'current_city', 'current_pincode',
            ] as $field) {
                $addressAttributes[$field] = $validated[$field] ?? null;
            }
            $addressAttributes['is_current_address'] = (bool) ($validated['is_current_address'] ?? false);
            $address = $user->addresses()->where('is_current_address', true)->first()
                ?? $user->addresses()->first();
            if ($address) {
                $address->update($addressAttributes);
            } else {
                $user->addresses()->create($addressAttributes);
            }

            foreach ([
                'educations' => ['education', 'year', 'university_name'],
                'experiences' => ['previous_employeer', 'designation', 'join_date', 'left_date', 'ctc'],
                'family_details' => ['relation', 'name', 'dob', 'is_nominee', 'occupation'],
            ] as $field => $columns) {
                $relation = $field === 'educations' ? $user->education() : $user->{$field}();
                $relation->delete();

                foreach ($validated[$field] ?? [] as $row) {
                    $attributes = array_intersect_key($row, array_flip($columns));
                    if ($field === 'family_details') {
                        $attributes['is_nominee'] = (bool) ($attributes['is_nominee'] ?? false);
                    }

                    if (collect($attributes)->except('is_nominee')->every(
                        static fn($value): bool => $value === null || $value === '',
                    )) {
                        continue;
                    }

                    $relation->create($attributes);
                }
            }
        });

        return redirect()->to(url('profile-settings'))
            ->with('success', 'Profile updated successfully.');
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
        $directories = [
            'general-settings',
            'app-settings',
            'system-settings',
            'website-settings',
            'financial-settings',
            'other-settings',
        ];

        foreach ($directories as $directory) {
            $candidate = 'tenant.settings.'.$directory.'.'.$page;
            if (view()->exists($candidate)) {
                return $candidate;
            }
        }

        foreach (self::SECTIONS as $section => $pages) {
            if (array_key_exists($page, $pages)) {
                $sectionDirectory = strtolower(str_replace(' ', '-', $section));

                return 'tenant.settings.'.$sectionDirectory.'.'.$page;
            }
        }

        abort(404);
    }

    private function profileUser(): User
    {
        $user = Auth::guard('tenant')->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function normalizeSmtpPassword(string $host, string $password): string
    {
        return strtolower(trim($host)) === 'smtp.gmail.com'
            ? (preg_replace('/\s+/', '', $password) ?? $password)
            : $password;
    }
}
