<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('view tasks') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('create task') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task, Project $project): bool
    {
        return $user->hasPermissionTo('edit task') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task, Project $project): bool
    {
        return $user->hasPermissionTo('archive task') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task, Project $project): bool
    {
        return $user->hasPermissionTo('restore task') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can reorder the model.
     */
    public function reorder(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('reorder task') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can complete the model.
     */
    public function complete(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('complete task') && $user->hasProjectAccess($project);
    }
}
