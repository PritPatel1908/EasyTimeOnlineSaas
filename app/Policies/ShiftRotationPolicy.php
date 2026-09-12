<?php

namespace App\Policies;

use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftRotationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_shift::rotation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShiftRotation $shiftRotation): bool
    {
        return $user->can('view_shift::rotation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_shift::rotation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShiftRotation $shiftRotation): bool
    {
        return $user->can('update_shift::rotation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShiftRotation $shiftRotation): bool
    {
        return $user->can('delete_shift::rotation');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_shift::rotation');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ShiftRotation $shiftRotation): bool
    {
        return $user->can('force_delete_shift::rotation');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_shift::rotation');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ShiftRotation $shiftRotation): bool
    {
        return $user->can('restore_shift::rotation');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_shift::rotation');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ShiftRotation $shiftRotation): bool
    {
        return $user->can('replicate_shift::rotation');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_shift::rotation');
    }
}
