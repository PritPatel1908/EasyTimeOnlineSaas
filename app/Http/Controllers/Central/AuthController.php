<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\User;
use App\Notifications\LoginOtp;
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
        if (Auth::check()) {
            return redirect()->to(route('admin.dashboard', absolute: false));
        }

        return view('auth.login');
    }

    public function login(Request $request): View|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
        }

        $this->sendOtp($request, $user);

        return redirect()->to(route('auth.otp', absolute: false))->with('success', 'A verification code has been sent to your email.');
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth.otp.user_id')) {
            return redirect()->to(route('login', absolute: false));
        }

        return view('auth.otp', [
            'email' => $request->session()->get('auth.otp.email'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);
        $challenge = $request->session()->get('auth.otp');
        $error = null;

        if (! is_array($challenge) || now()->greaterThan($challenge['expires_at'])) {
            $error = 'This code has expired. Please request a new one.';
        } elseif ($challenge['attempts'] >= 5) {
            $error = 'Too many incorrect attempts. Please request a new code.';
        } elseif (! Hash::check((string) $request->string('otp'), $challenge['code'])) {
            $request->session()->put('auth.otp.attempts', $challenge['attempts'] + 1);
            $error = 'The verification code is incorrect.';
        }

        if ($error !== null) {
            return back()->withErrors(['otp' => $error]);
        }

        $user = User::query()->find($challenge['user_id']);

        if ($user === null) {
            $request->session()->forget('auth.otp');

            return redirect()->to(route('login', absolute: false))->withErrors(['email' => 'This account is no longer available.']);
        }

        Auth::login($user, true);
        $request->session()->forget('auth.otp');
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('auth.otp.user_id');
        $error = null;

        if ($userId === null) {
            $error = 'Your verification session has expired.';
        }

        if ($error !== null) {
            return $userId === null
                ? redirect()->to(route('login', absolute: false))
                : back()->withErrors(['otp' => $error]);
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            $request->session()->forget('auth.otp');

            return redirect()->to(route('login', absolute: false));
        }

        $this->sendOtp($request, $user);

        return back()->with('success', 'A new verification code has been sent.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(route('login', absolute: false));
    }

    private function sendOtp(Request $request, User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $request->session()->put('auth.otp', [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'sent_at' => now(),
            'attempts' => 0,
        ]);

        Notification::route('mail', $user->email)->notify(new LoginOtp($code));
    }
}
