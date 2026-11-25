<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Only the Super Admin manages the customer roster itself.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->customer_id === $customer->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isSuperAdmin();
    }

    public function toggleStatus(User $user, Customer $customer): bool
    {
        return $user->isSuperAdmin();
    }
}
