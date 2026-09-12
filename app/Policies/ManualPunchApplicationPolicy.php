<?php

namespace App\Policies;

use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManualPunchApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_manual::punch::application');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ManualPunchApplication $manualPunchApplication): bool
    {
        return $user->can('view_manual::punch::application');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_manual::punch::application');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ManualPunchApplication $manualPunchApplication): bool
    {
        return $user->can('update_manual::punch::application');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ManualPunchApplication $manualPunchApplication): bool
    {
        return $user->can('delete_manual::punch::application');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_manual::punch::application');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ManualPunchApplication $manualPunchApplication): bool
    {
        return $user->can('force_delete_manual::punch::application');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_manual::punch::application');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ManualPunchApplication $manualPunchApplication): bool
    {
        return $user->can('restore_manual::punch::application');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_manual::punch::application');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ManualPunchApplication $manualPunchApplication): bool
    {
        return $user->can('replicate_manual::punch::application');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_manual::punch::application');
    }
}
