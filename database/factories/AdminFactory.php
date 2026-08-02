<?php

namespace Database\Factories;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Assign the super_admin role after creating.
     */
    public function superAdmin(): static
    {
        return $this->afterCreating(function (Admin $admin): void {
            Role::findOrCreate(AdminRole::SuperAdmin->value, 'admin');
            $admin->assignRole(AdminRole::SuperAdmin);
        });
    }

    /**
     * Assign the admin role after creating.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (Admin $admin): void {
            Role::findOrCreate(AdminRole::Admin->value, 'admin');
            $admin->assignRole(AdminRole::Admin);
        });
    }

    /**
     * Assign the operator role after creating.
     */
    public function operator(): static
    {
        return $this->afterCreating(function (Admin $admin): void {
            Role::findOrCreate(AdminRole::Operator->value, 'admin');
            $admin->assignRole(AdminRole::Operator);
        });
    }
}
