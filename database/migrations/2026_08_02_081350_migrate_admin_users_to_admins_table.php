<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\User;
use Database\Seeders\AdminRolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new AdminRolePermissionSeeder)->run();

        $legacyAdminRole = Role::query()
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->first();

        $userIds = [];

        if ($legacyAdminRole !== null) {
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $legacyAdminRole->id)
                ->where('model_type', User::class)
                ->pluck('model_id')
                ->all();
        }

        $users = User::query()
            ->when(
                $userIds !== [],
                fn ($query) => $query->whereIn('id', $userIds),
                fn ($query) => $query->whereRaw('0 = 1'),
            )
            ->get();

        foreach ($users as $user) {
            $now = now();
            $adminId = (string) Str::ulid();

            DB::table('admins')->updateOrInsert(
                ['email' => $user->email],
                [
                    'id' => $adminId,
                    'name' => $user->name,
                    'password' => $user->getAttributes()['password'],
                    'email_verified_at' => $user->email_verified_at,
                    'keycloak_id' => $user->keycloak_id,
                    'avatar' => $user->avatar,
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at ?? $now,
                    'updated_at' => $now,
                ],
            );

            $admin = Admin::query()->where('email', $user->email)->firstOrFail();
            $admin->syncRoles([AdminRole::SuperAdmin]);

            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->delete();

            $user->delete();
        }

        foreach (['admin', 'user'] as $legacyRoleName) {
            $legacyRole = Role::query()
                ->where('name', $legacyRoleName)
                ->where('guard_name', 'web')
                ->first();

            if ($legacyRole === null) {
                continue;
            }

            DB::table('model_has_roles')->where('role_id', $legacyRole->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $legacyRole->id)->delete();
            $legacyRole->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
