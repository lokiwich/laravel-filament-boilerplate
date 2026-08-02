<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class AdminRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value, 'admin');
        }

        foreach (AdminRole::cases() as $role) {
            RoleModel::findOrCreate($role->value, 'admin');
        }

        RoleModel::findByName(AdminRole::Admin->value, 'admin')
            ->syncPermissions(Permission::forAdminRole());

        RoleModel::findByName(AdminRole::Operator->value, 'admin')
            ->syncPermissions(Permission::forOperatorRole());

        RoleModel::findByName(AdminRole::SuperAdmin->value, 'admin')
            ->syncPermissions(Permission::cases());
    }
}
