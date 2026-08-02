<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Admin;
use App\Models\User;

class UserPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo(Permission::UsersViewAny);
    }

    public function view(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo(Permission::UsersView);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(Permission::UsersCreate);
    }

    public function update(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo(Permission::UsersUpdate);
    }

    public function delete(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo(Permission::UsersDelete);
    }
}
