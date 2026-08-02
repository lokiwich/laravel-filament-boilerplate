<?php

namespace App\Console\Commands;

use App\Enums\AdminRole;
use App\Models\Admin;
use Database\Seeders\AdminRolePermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('admin:create {email? : The admin email address} {--name= : The admin name} {--password= : The admin password} {--role= : The admin role (super_admin, admin, operator)}')]
#[Description('Create an admin, or update an existing admin by email and assign a role')]
class CreateAdminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->callSilent('db:seed', ['--class' => AdminRolePermissionSeeder::class]);

        $email = $this->argument('email') ?: text(
            label: __('admin_command.prompts.email'),
            required: true,
            validate: ['email' => 'required|email'],
        );

        $name = $this->option('name') ?: text(
            label: __('admin_command.prompts.name'),
            default: str($email)->before('@')->replace('.', ' ')->title()->toString(),
            required: true,
        );

        $role = $this->resolveRole();

        if ($role === null) {
            return self::FAILURE;
        }

        $plainPassword = $this->option('password');

        $admin = Admin::query()->where('email', $email)->first();

        if ($admin === null && blank($plainPassword)) {
            $plainPassword = password(
                label: __('admin_command.prompts.password'),
                required: true,
            );
        }

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $plainPassword,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => $admin === null
                    ? ['required', 'string', 'min:8']
                    : ['nullable', 'string', 'min:8'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if ($admin === null) {
            $admin = Admin::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $plainPassword,
                'email_verified_at' => now(),
            ]);

            $admin->syncRoles([$role]);

            activity()
                ->causedBy($admin)
                ->performedOn($admin)
                ->event('created')
                ->log('Admin created via artisan command');

            $this->components->info(__('admin_command.created', ['email' => $admin->email]));

            return self::SUCCESS;
        }

        $admin->name = $name;
        $admin->email_verified_at ??= now();

        if (filled($plainPassword)) {
            $admin->password = $plainPassword;
        }

        $admin->save();
        $admin->syncRoles([$role]);

        activity()
            ->causedBy($admin)
            ->performedOn($admin)
            ->event('updated')
            ->log('Admin updated via artisan command');

        $this->components->info(__('admin_command.updated', ['email' => $admin->email]));

        return self::SUCCESS;
    }

    private function resolveRole(): ?AdminRole
    {
        $roleOption = $this->option('role');

        if (filled($roleOption)) {
            $role = AdminRole::tryFrom($roleOption);

            if ($role === null) {
                $this->components->error(__('admin_command.invalid_role', [
                    'role' => (string) $roleOption,
                ]));
            }

            return $role;
        }

        if (! $this->input->isInteractive()) {
            return AdminRole::SuperAdmin;
        }

        $roleValue = select(
            label: __('admin_command.prompts.role'),
            options: collect(AdminRole::cases())
                ->mapWithKeys(fn (AdminRole $adminRole): array => [$adminRole->value => $adminRole->value])
                ->all(),
            default: AdminRole::SuperAdmin->value,
        );

        return AdminRole::from($roleValue);
    }
}
