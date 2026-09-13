<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('tenant')->check()) {
            return redirect()->to('/dashboard');
        }

        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
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

        if ($user === null || ! Auth::guard('tenant')->attempt([
            'email' => $user->email,
            'password' => (string) $request->input('password'),
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email/username or password.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/login');
    }
}
