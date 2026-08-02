<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo(Permission::AdminsViewAny);
    }

    public function view(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo(Permission::AdminsView);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo(Permission::AdminsCreate);
    }

    public function update(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo(Permission::AdminsUpdate);
    }

    public function delete(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo(Permission::AdminsDelete);
    }
}
