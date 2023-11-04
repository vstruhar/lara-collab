<?php

namespace App\Policies;

use App\Models\ClientCompany;
use App\Models\User;

class ClientCompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view client companies');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create client company');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientCompany $model): bool
    {
        return $user->hasPermissionTo('edit client company');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientCompany $model): bool
    {
        return $user->hasPermissionTo('archive client company');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClientCompany $model): bool
    {
        return $user->hasPermissionTo('restore client company');
    }
}
