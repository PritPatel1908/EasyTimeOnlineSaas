<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        } elseif ($page === 'security-settings') {
            $passwordHistories = $this->profileUser()
                ->password_histories()
                ->latest()
                ->limit(3)
                ->get(['id', 'created_at']);
            $data = [
                'passwordHistories' => $passwordHistories,
                'lastPasswordChangedAt' => $passwordHistories->first()?->created_at,
            ];
        }

        return view($this->viewPath($page), $data);
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
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->getKey())],
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
                'email' => $validated['email'] ?? null,
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

        return redirect()->back()->with('success', 'Profile updated successfully.');
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

    private function profileUser(): User
    {
        $user = Auth::guard('tenant')->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
