<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->hasPermissionTo('manage-users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->isLandlord() || $user->hasPermissionTo('manage-users');
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->hasPermissionTo('create-user');
    }

    public function update(User $user, User $model): bool
    {
        if ($model->isLandlord() && ! $user->isLandlord()) {
            return false;
        }

        return $user->isLandlord() || $user->hasPermissionTo('manage-users');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isLandlord()) {
            return false;
        }

        if ($user->id === $model->id) {
            return false;
        }

        return $user->isLandlord() || $user->hasPermissionTo('delete-user');
    }
}
