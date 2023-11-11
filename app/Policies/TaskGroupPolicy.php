<?php

namespace App\Policies;

use App\Models\TaskGroup;
use App\Models\User;

class TaskGroupPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create task group');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TaskGroup $model): bool
    {
        return $user->hasPermissionTo('edit task group');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaskGroup $model): bool
    {
        return $user->hasPermissionTo('archive task group');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaskGroup $model): bool
    {
        return $user->hasPermissionTo('restore task group');
    }

    /**
     * Determine whether the user can reorder the model.
     */
    public function reorder(User $user, TaskGroup $model): bool
    {
        return $user->hasPermissionTo('reorder task group');
    }
}
