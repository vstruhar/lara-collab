<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;

class AttachmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('view tasks') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attachment $attachment, Project $project): bool
    {
        return $user->hasPermissionTo('edit task') && $user->hasProjectAccess($project);
    }
}
