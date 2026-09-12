<?php

namespace App\Policies;

use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManualAttendanceApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_manual::attendance::application');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ManualAttendanceApplication $manualAttendanceApplication): bool
    {
        return $user->can('view_manual::attendance::application');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_manual::attendance::application');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ManualAttendanceApplication $manualAttendanceApplication): bool
    {
        return $user->can('update_manual::attendance::application');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ManualAttendanceApplication $manualAttendanceApplication): bool
    {
        return $user->can('delete_manual::attendance::application');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_manual::attendance::application');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ManualAttendanceApplication $manualAttendanceApplication): bool
    {
        return $user->can('force_delete_manual::attendance::application');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_manual::attendance::application');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ManualAttendanceApplication $manualAttendanceApplication): bool
    {
        return $user->can('restore_manual::attendance::application');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_manual::attendance::application');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ManualAttendanceApplication $manualAttendanceApplication): bool
    {
        return $user->can('replicate_manual::attendance::application');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_manual::attendance::application');
    }
}
