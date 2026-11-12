<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureActiveDomain;
use App\Http\Middleware\EnsureValidTenantLicense;
use App\Models\Tenant\User;
use App\Notifications\LoginOtp;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class TenantTwoFactorLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.tenant' => config('database.connections.sqlite')]);
        config(['database.connections.tenant.database' => ':memory:']);
        DB::purge('tenant');

        Schema::connection('tenant')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->nullable();
            $table->string('code')->nullable();
            $table->string('number')->nullable();
            $table->string('password')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('is_inactive')->default(false);
            $table->unsignedInteger('login_attempts')->default(0);
            $table->softDeletes();
        });

        Schema::connection('tenant')->create('general_configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->softDeletes();
        });

        $this->withoutMiddleware([
            EnsureActiveDomain::class,
            EnsureValidTenantLicense::class,
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            ThrottleRequests::class,
        ]);
        $this->withServerVariables(['HTTP_HOST' => 'example.saas.test']);
    }

    public function test_enabled_user_must_verify_email_code_before_becoming_authenticated(): void
    {
        $this->createTwoFactorUser();
        Notification::fake();

        $response = $this->post('/login', [
            'email' => 'employee@example.com',
            'password' => 'Password123',
        ]);

        $this->assertSame(url('/login/otp'), $response->headers->get('Location'));
        $this->assertGuest('tenant');
        $this->assertSessionHas('tenant.auth.otp.email', 'employee@example.com');
        Notification::assertSentOnDemand(LoginOtp::class);
    }

    public function test_valid_email_code_authenticates_the_tenant_user(): void
    {
        $this->createTwoFactorUser();

        $response = $this->withSession([
            'tenant.auth.otp' => [
                'user_id' => 1,
                'email' => 'employee@example.com',
                'code' => Hash::make('123456'),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'sent_at' => now()->timestamp,
                'attempts' => 0,
                'remember' => false,
            ],
        ])->post('/login/otp', ['otp' => '123456']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::query()->findOrFail(1), 'tenant');
        $this->assertSessionMissing('tenant.auth.otp');
    }

    public function test_non_admin_account_is_locked_after_configured_wrong_password_attempts(): void
    {
        $this->createUser(2);
        DB::connection('tenant')->table('general_configurations')->insert([
            'key' => 'password_attempts_count',
            'value' => '2',
        ]);

        foreach (range(1, 2) as $attempt) {
            $this->post('/login', [
                'email' => 'employee@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $user = DB::connection('tenant')->table('users')->where('id', 2)->first();
        $this->assertSame(2, (int) $user->login_attempts);
        $this->assertTrue((bool) $user->is_inactive);

        foreach (['wrong-password', 'Password123'] as $password) {
            $this->post('/login', [
                'email' => 'employee@example.com',
                'password' => $password,
            ])->assertSessionHasErrors('email');
            $this->assertSame(
                'This account is locked. Please contact your administrator.',
                session('errors')->first('email')
            );
        }

        $this->assertGuest('tenant');
    }

    public function test_user_one_is_exempt_from_wrong_password_lockout(): void
    {
        $this->createUser(1);
        DB::connection('tenant')->table('general_configurations')->insert([
            'key' => 'password_attempts_count',
            'value' => '1',
        ]);

        $this->post('/login', [
            'email' => 'employee@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $user = DB::connection('tenant')->table('users')->where('id', 1)->first();
        $this->assertSame(0, (int) $user->login_attempts);
        $this->assertFalse((bool) $user->is_inactive);
    }

    private function createTwoFactorUser(): void
    {
        $this->createUser(1, true);
    }

    private function createUser(int $id, bool $twoFactorEnabled = false): void
    {
        DB::connection('tenant')->table('users')->insert([
            'id' => $id,
            'email' => 'employee@example.com',
            'code' => 'EMP001',
            'password' => Hash::make('Password123'),
            'two_factor_enabled' => $twoFactorEnabled,
        ]);
    }
}
