<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Project;
use App\Models\User;

class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('view notes') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('create note') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note, Project $project): bool
    {
        return $user->hasPermissionTo('edit note') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note, Project $project): bool
    {
        return $user->hasPermissionTo('delete note') && $user->hasProjectAccess($project);
    }
}
