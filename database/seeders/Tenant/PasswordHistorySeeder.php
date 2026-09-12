<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\PasswordHistory;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

class PasswordHistorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::query()->withoutGlobalScopes()->get() as $user) {
            if ($user->password !== null) {
                PasswordHistory::create([
                    'user_id' => $user->id,
                    'password' => $user->password,
                ]);
            }
        }
    }
}
