<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['top_naviagation' => 1, 'change_passwords' => 1, 'locations' => 1, 'companies' => 1, 'departments' => 1, 'categories' => 1, 'users' => 1, 'roles' => 1, 'data_policies' => 1, 'password_policies' => 1, 'shifts' => 1, 'leave_types' => 1, 'leave_reasons' => 1, 'holidays' => 1, 'financial_years' => 1] as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }
    }
}
