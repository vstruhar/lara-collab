<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\TaskGroup;
use App\Models\User;

class TaskGroupPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('create task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TaskGroup $taskGroup, Project $project): bool
    {
        return $user->hasPermissionTo('edit task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaskGroup $taskGroup, Project $project): bool
    {
        return $user->hasPermissionTo('archive task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaskGroup $taskGroup, Project $project): bool
    {
        return $user->hasPermissionTo('restore task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can reorder the model.
     */
    public function reorder(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('reorder task group') && $user->hasProjectAccess($project);
    }
}
