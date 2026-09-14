<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class TenantPermissions
{
    private const ABILITY_ACTIONS = [
        'view_any' => 'read',
        'view' => 'read',
        'create' => 'create',
        'update' => 'write',
        'delete_any' => 'delete',
        'delete' => 'delete',
    ];

    public static function allows(?Authenticatable $user, string $module, string $action): bool
    {
        if ($user === null || ! method_exists($user, 'hasPermissionTo')) {
            return false;
        }

        if ((int) $user->getAuthIdentifier() === 1) {
            return true;
        }

        $required = self::normalize($module) . '.' . strtolower($action);

        return $user->getAllPermissions()->contains(
            fn ($permission): bool => self::normalizePermission((string) $permission->name) === $required
        );
    }

    public static function userCan(string $module, string $action): bool
    {
        return self::allows(Auth::guard('tenant')->user(), $module, $action);
    }

    public static function authorize(string $module, string $action): void
    {
        if (! self::userCan($module, $action)) {
            throw new AuthorizationException('This action is not allowed for your role.');
        }
    }

    public static function authorizeAbility(Authenticatable $user, string $ability, array $arguments = []): ?bool
    {
        if (! $user instanceof \App\Models\Tenant\User) {
            return null;
        }

        if ((int) $user->getAuthIdentifier() === 1) {
            return true;
        }

        foreach (self::ABILITY_ACTIONS as $prefix => $action) {
            if (! str_starts_with($ability, $prefix . '_')) {
                continue;
            }

            $module = self::moduleFromAbility($ability, $prefix, $arguments);

            return self::allows($user, $module, $action);
        }

        return null;
    }

    private static function moduleFromAbility(string $ability, string $prefix, array $arguments): string
    {
        $model = $arguments[0] ?? null;
        if (is_object($model)) {
            return class_basename($model);
        }

        $module = substr($ability, strlen($prefix) + 1);

        return str_starts_with($module, 'shield::')
            ? substr($module, strlen('shield::'))
            : $module;
    }

    private static function normalize(string $value): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]+/', '', $value));
    }

    private static function normalizePermission(string $permission): string
    {
        [$module, $action] = array_pad(explode('.', $permission, 2), 2, '');

        return self::normalize($module) . '.' . strtolower($action);
    }

    public static function registerGateHook(): void
    {
        Gate::before(function (Authenticatable $user, string $ability, ...$arguments): ?bool {
            return self::authorizeAbility($user, $ability, $arguments);
        });
    }
}
