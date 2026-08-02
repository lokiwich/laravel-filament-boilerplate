<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

class KeycloakController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('keycloak')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            /** @var SocialiteUser $keycloakUser */
            $keycloakUser = Socialite::driver('keycloak')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors(['email' => __('keycloak.errors.unable_to_authenticate')]);
        }

        $email = $keycloakUser->getEmail();

        if (blank($email)) {
            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors(['email' => __('keycloak.errors.missing_email')]);
        }

        $admin = Admin::query()->where('email', $email)->first();

        if ($admin === null) {
            activity()
                ->event('login_failed')
                ->withProperties([
                    'provider' => 'keycloak',
                    'email' => $email,
                ])
                ->log('Keycloak login rejected: admin not found');

            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors(['email' => __('keycloak.errors.no_admin_access')]);
        }

        $admin->forceFill([
            'keycloak_id' => $keycloakUser->getId(),
            'avatar' => $keycloakUser->getAvatar() ?: $admin->avatar,
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();

        Auth::guard('admin')->login($admin, remember: true);

        activity()
            ->causedBy($admin)
            ->performedOn($admin)
            ->event('login')
            ->withProperties(['provider' => 'keycloak'])
            ->log('Admin logged in via Keycloak');

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
