<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $authUser)
    {
        return $authUser->hasPermissionTo('user.view');
    }

    public function create(User $authUser)
    {
        return $authUser->hasPermissionTo('user.create');
    }

    public function update(User $authUser)
    {
        return $authUser->hasPermissionTo('user.update');
    }

    public function delete(User $authUser)
    {
        return $authUser->hasPermissionTo('user.delete');
    }

    public function changeStatus(User $authUser)
    {
        return $authUser->hasPermissionTo('user.change-status');
    }
}
