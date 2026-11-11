<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SettingsController;
use App\Models\Tenant\User;
use Illuminate\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    public function test_toggle_persists_two_factor_status_for_authenticated_tenant_user(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });

        $migration = require base_path(
            'database/migrations/tenant/2026_11_11_192430_add_two_factor_enabled_to_users_table.php',
        );
        $migration->up();

        DB::table('users')->insert(['id' => 1]);

        $user = (new User)->setConnection('sqlite');
        $user->setRawAttributes(['id' => 1, 'two_factor_enabled' => false], true);
        $user->exists = true;

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->twice()->andReturn($user);
        Auth::shouldReceive('guard')->with('tenant')->twice()->andReturn($guard);

        User::withoutEvents(function () use ($user): void {
            app(SettingsController::class)->toggleTwoFactorAuthentication();
            $this->assertDatabaseHas('users', [
                'id' => 1,
                'two_factor_enabled' => true,
            ]);

            app(SettingsController::class)->toggleTwoFactorAuthentication();
            $this->assertDatabaseHas('users', [
                'id' => 1,
                'two_factor_enabled' => false,
            ]);
        });
    }
}
