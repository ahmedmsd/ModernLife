<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProductionRequest;

class ProductionRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin', 'factory_manager', 'sales', 'showroom_manager']);
    }

    public function view(User $user, ProductionRequest $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin', 'sales', 'showroom_manager']);
    }

    public function update(User $user, ProductionRequest $record): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin', 'factory_manager', 'sales', 'showroom_manager']);
    }

    public function delete(User $user, ProductionRequest $record): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, ProductionRequest $record): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, ProductionRequest $record): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

