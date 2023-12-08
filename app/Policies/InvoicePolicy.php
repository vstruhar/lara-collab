<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view invoices');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create invoice');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $model): bool
    {
        return $user->hasPermissionTo('edit invoice');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $model): bool
    {
        return $user->hasPermissionTo('archive invoice');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $model): bool
    {
        return $user->hasPermissionTo('restore invoice');
    }

    /**
     * Determine whether the user can change status of the model.
     */
    public function changeStatus(User $user, Invoice $model): bool
    {
        return $user->hasPermissionTo('change invoice status');
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, Invoice $model): bool
    {
        return $user->hasPermissionTo('download invoice');
    }

    /**
     * Determine whether the user can print the model.
     */
    public function print(User $user, Invoice $model): bool
    {
        return $user->hasPermissionTo('print invoice');
    }
}
