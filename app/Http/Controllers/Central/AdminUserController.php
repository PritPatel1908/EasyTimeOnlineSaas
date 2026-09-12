<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.users.index', ['users' => $this->paginate($request)]);
    }

    public function pagination(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('admin.users.partials.pagination', ['users' => $this->paginate($request)])->render(),
        ]);
    }

    private function paginate(Request $request): mixed
    {
        $perPage = $request->validate(['per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])]])['per_page'] ?? 10;

        return User::query()->latest()->paginate($perPage, ['*'], 'page', $request->integer('page', 1))->withQueryString();
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(int $user): View
    {
        return view('admin.users.form', ['user' => User::query()->findOrFail($user)]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $userRecord = User::query()->findOrFail($user);
        $userRecord->name = $validated['name'];
        $userRecord->email = $validated['email'];

        if (! empty($validated['password'])) {
            $userRecord->password = Hash::make($validated['password']);
        }

        $userRecord->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(int $user): RedirectResponse
    {
        User::query()->findOrFail($user)->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
