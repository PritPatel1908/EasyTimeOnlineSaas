<?php

namespace App\Policies;

use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\User;

class CanteenFacilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_canteenfacility');
    }
    public function view(User $user, CanteenFacility $facility): bool
    {
        return $user->can('view_canteenfacility');
    }
    public function create(User $user): bool
    {
        return $user->can('create_canteenfacility');
    }
    public function update(User $user, CanteenFacility $facility): bool
    {
        return $user->can('update_canteenfacility');
    }
    public function delete(User $user, CanteenFacility $facility): bool
    {
        return $user->can('delete_canteenfacility');
    }
}
