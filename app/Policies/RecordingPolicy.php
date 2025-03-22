<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Recording;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecordingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_recording');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Recording $recording): bool
    {
        return $user->can('view_recording');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_recording');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Recording $recording): bool
    {
        return $user->can('update_recording');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Recording $recording): bool
    {
        return $user->can('delete_recording');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_recording');
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Recording $recording): bool
    {
        return $user->can('force_delete_recording');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_recording');
    }

    /**
     * Determine whether the user can restore.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Recording $recording): bool
    {
        return $user->can('restore_recording');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_recording');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function replicate(User $user, Recording $recording): bool
    {
        return $user->can('replicate_recording');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_recording');
    }
}
