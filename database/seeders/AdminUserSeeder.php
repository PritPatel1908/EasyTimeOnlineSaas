<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Central\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'prit89039@gmail.com'],
            [
                'name' => 'Prit Admin',
                'password' => Hash::make('Prit@1234'),
            ],
        );
    }
}
