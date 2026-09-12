<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\PasswordPolicy;
use Illuminate\Database\Seeder;

class PasswordPolicySeeder extends Seeder
{
    public function run(): void
    {
        PasswordPolicy::create([
            'policy_name' => 'Default Password Policy',
            'password_expiry_days' => 10,
            'notify_expiry_days' => 5,
        ]);
    }
}
