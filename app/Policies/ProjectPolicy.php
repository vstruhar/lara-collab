<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Services\PermissionService;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view projects');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function view(User $user, Project $model): bool
    {
        $users = PermissionService::usersWithAccessToProject($model);

        $hasAccessToProject = collect($users)->pluck('id')->contains($user->id);

        return $user->hasPermissionTo('view project') && $hasAccessToProject;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create project');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $model): bool
    {
        return $user->hasPermissionTo('edit project');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $model): bool
    {
        return $user->hasPermissionTo('archive project');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $model): bool
    {
        return $user->hasPermissionTo('restore project');
    }

    /**
     * Determine whether the user can edit the model user access.
     */
    public function editUserAccess(User $user, Project $model): bool
    {
        return $user->hasPermissionTo('edit project user access');
    }
}
