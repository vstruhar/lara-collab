<?php

namespace App\Policies;

use App\Models\OwnerCompany;
use App\Models\User;

class OwnerCompanyPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OwnerCompany $ownerCompany): bool
    {
        return $user->can('edit owner company');
    }
}
