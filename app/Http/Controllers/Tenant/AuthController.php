<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Notifications\LoginOtp;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class AuthController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 10;

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('tenant')->check()) {
            return redirect()->to('/dashboard');
        }

        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->session()->forget('tenant.auth.otp');
        $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim((string) $request->input('email'));

        $user = User::query()
            ->where(function ($query) use ($identity): void {
                $query->where('email', $identity)
                    ->orWhere('code', $identity)
                    ->orWhere('number', $identity);
            })
            ->first();

        if ($user === null || ! Hash::check((string) $request->input('password'), (string) $user->password)) {
            return back()->withErrors(['email' => 'Invalid email/user code or password.'])->withInput();
        }

        if ($user->two_factor_enabled) {
            if (! is_string($user->email) || filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
                return back()
                    ->withErrors(['email' => 'Two-factor authentication is enabled, but this account has no valid email address. Contact your administrator.'])
                    ->withInput($request->except('password'));
            }

            $this->sendOtp($request, $user);

            return redirect()->to(url('/login/otp'))
                ->with('success', 'A verification code has been sent to your email address.');
        }

        if (! Auth::guard('tenant')->attempt([
            'id' => $user->getKey(),
            'password' => (string) $request->input('password'),
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email/user code or password.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        $challenge = $request->session()->get('tenant.auth.otp');

        if (! is_array($challenge)
            || ! is_string($challenge['email'] ?? null)
            || ! isset($challenge['code'], $challenge['user_id'])
            || (int) ($challenge['attempts'] ?? 0) >= 5) {
            $request->session()->forget('tenant.auth.otp');

            return redirect()->to(url('/login'))
                ->withErrors(['email' => 'Your verification session is unavailable. Please sign in again.']);
        }

        return view('tenant.auth.otp', [
            'email' => $challenge['email'],
            'codeExpired' => (int) ($challenge['expires_at'] ?? 0) <= now()->timestamp,
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);
        $challenge = $request->session()->get('tenant.auth.otp');

        if (! is_array($challenge)
            || (int) ($challenge['expires_at'] ?? 0) <= now()->timestamp
            || (int) ($challenge['attempts'] ?? 0) >= 5) {
            $request->session()->forget('tenant.auth.otp');

            return redirect()->to(url('/login'))
                ->withErrors(['email' => 'Your verification session has expired. Please sign in again.']);
        }

        if (! Hash::check((string) $request->input('otp'), (string) $challenge['code'])) {
            $attempts = (int) $challenge['attempts'] + 1;
            if ($attempts >= 5) {
                $request->session()->forget('tenant.auth.otp');

                return redirect()->to(url('/login'))
                    ->withErrors(['email' => 'Too many incorrect codes. Please sign in again to request a new code.']);
            }

            $request->session()->put('tenant.auth.otp.attempts', $attempts);

            return back()->withErrors(['otp' => 'The verification code is incorrect.']);
        }

        $user = User::query()
            ->whereKey($challenge['user_id'])
            ->where('two_factor_enabled', true)
            ->first();

        if ($user === null || $user->email !== ($challenge['email'] ?? null)) {
            $request->session()->forget('tenant.auth.otp');

            return redirect()->to(url('/login'))
                ->withErrors(['email' => 'This account is no longer available for verification. Please sign in again.']);
        }

        Auth::guard('tenant')->login($user, (bool) ($challenge['remember'] ?? false));
        $request->session()->forget('tenant.auth.otp');
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $challenge = $request->session()->get('tenant.auth.otp');

        if (! is_array($challenge) || ! isset($challenge['user_id'], $challenge['email'], $challenge['code'])) {
            $request->session()->forget('tenant.auth.otp');

            return redirect()->to(url('/login'))
                ->withErrors(['email' => 'Your verification session has expired. Please sign in again.']);
        }

        if ((int) ($challenge['sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Please wait one minute before requesting another code.']);
        }

        $user = User::query()
            ->whereKey($challenge['user_id'])
            ->where('two_factor_enabled', true)
            ->first();

        if ($user === null || ! is_string($user->email) || filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
            $request->session()->forget('tenant.auth.otp');

            return redirect()->to(url('/login'))
                ->withErrors(['email' => 'This account is no longer available for verification. Please sign in again.']);
        }

        $this->sendOtp($request, $user, (bool) ($challenge['remember'] ?? false));

        return back()->with('success', 'A new verification code has been sent to your email address.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/login');
    }

    private function sendOtp(Request $request, User $user, bool $remember = false): void
    {
        $code = (string) random_int(100000, 999999);

        $request->session()->put('tenant.auth.otp', [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES)->timestamp,
            'sent_at' => now()->timestamp,
            'attempts' => 0,
            'remember' => $remember,
        ]);

        Notification::route('mail', $user->email)->notify(new LoginOtp($code));
    }
}
