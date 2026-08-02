<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\User;
use Database\Seeders\AdminRolePermissionSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->seed(AdminRolePermissionSeeder::class);
});

test('admin:create creates a new admin', function () {
    $exitCode = Artisan::call('admin:create', [
        'email' => 'new-admin@example.com',
        '--name' => 'New Admin',
        '--password' => 'password123',
        '--role' => AdminRole::SuperAdmin->value,
    ]);

    expect($exitCode)->toBe(0);

    $admin = Admin::query()->where('email', 'new-admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('New Admin')
        ->and($admin->hasRole(AdminRole::SuperAdmin))->toBeTrue();
});

test('admin:create updates an existing admin by email and assigns role', function () {
    $admin = Admin::factory()->create([
        'name' => 'Old Name',
        'email' => 'existing@example.com',
    ]);

    $exitCode = Artisan::call('admin:create', [
        'email' => 'existing@example.com',
        '--name' => 'Updated Admin',
        '--password' => 'password123',
        '--role' => AdminRole::Admin->value,
    ]);

    expect($exitCode)->toBe(0);

    $admin->refresh();

    expect($admin->name)->toBe('Updated Admin')
        ->and($admin->hasRole(AdminRole::Admin))->toBeTrue();
});

test('admins can access the filament panel', function () {
    $admin = Admin::factory()->superAdmin()->create();

    expect($admin->canAccessPanel(filament()->getPanel('admin')))->toBeTrue();
});

test('admins with admin role can view the users list', function () {
    $admin = Admin::factory()->admin()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('filament.admin.resources.users.index'))
        ->assertOk();
});

test('operators cannot view the users list', function () {
    $operator = Admin::factory()->operator()->create();

    $this->actingAs($operator, 'admin')
        ->get(route('filament.admin.resources.users.index'))
        ->assertForbidden();
});

test('operators can view the dashboard', function () {
    $operator = Admin::factory()->operator()->create();

    $this->actingAs($operator, 'admin')
        ->get(route('filament.admin.pages.dashboard'))
        ->assertOk();
});

test('operators cannot view the admins list', function () {
    $operator = Admin::factory()->operator()->create();

    $this->actingAs($operator, 'admin')
        ->get(route('filament.admin.resources.admins.index'))
        ->assertForbidden();
});

test('super admins can view the admins list', function () {
    $superAdmin = Admin::factory()->superAdmin()->create();

    $this->actingAs($superAdmin, 'admin')
        ->get(route('filament.admin.resources.admins.index'))
        ->assertOk();
});

test('regular users are redirected away from the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

test('keycloak login rejects emails that are not admins', function () {
    User::factory()->create([
        'email' => 'member@example.com',
    ]);

    $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('kc-123');
    $socialiteUser->shouldReceive('getEmail')->andReturn('member@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Member User');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
        ->with('keycloak')
        ->andReturn($provider);

    $this->get(route('auth.keycloak.callback'))
        ->assertRedirect(route('filament.admin.auth.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});

test('keycloak login authenticates existing admins', function () {
    $admin = Admin::factory()->admin()->create([
        'email' => 'admin-sso@example.com',
    ]);

    $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('kc-admin-1');
    $socialiteUser->shouldReceive('getEmail')->andReturn('admin-sso@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Admin SSO');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
        ->with('keycloak')
        ->andReturn($provider);

    $this->get(route('auth.keycloak.callback'))
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $this->assertAuthenticatedAs($admin, 'admin');

    $admin->refresh();
    expect($admin->keycloak_id)->toBe('kc-admin-1');
});

test('keycloak login does not create a new admin', function () {
    $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('kc-new');
    $socialiteUser->shouldReceive('getEmail')->andReturn('unknown@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Unknown');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
        ->with('keycloak')
        ->andReturn($provider);

    $this->get(route('auth.keycloak.callback'))
        ->assertRedirect(route('filament.admin.auth.login'))
        ->assertSessionHasErrors('email');

    expect(Admin::query()->where('email', 'unknown@example.com')->exists())->toBeFalse();
    $this->assertGuest('admin');
});

test('admin panel login page shows keycloak button', function () {
    app()->setLocale('en');

    $this->get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee(__('keycloak.login_button'))
        ->assertSee(route('auth.keycloak.redirect'), false);
});

test('admin panel login page shows persian keycloak button for fa locale', function () {
    app()->setLocale('fa');

    $this->get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee(__('keycloak.login_button'))
        ->assertSee('لاگین با Keycloak');
});

test('admin role seeder assigns expected permissions', function () {
    $adminRole = \Spatie\Permission\Models\Role::findByName(AdminRole::Admin->value, 'admin');
    $operatorRole = \Spatie\Permission\Models\Role::findByName(AdminRole::Operator->value, 'admin');

    expect($adminRole->hasPermissionTo('users.viewAny'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('admins.viewAny'))->toBeFalse()
        ->and($operatorRole->hasPermissionTo('domains.viewAny'))->toBeTrue()
        ->and($operatorRole->hasPermissionTo('users.viewAny'))->toBeFalse();
});
