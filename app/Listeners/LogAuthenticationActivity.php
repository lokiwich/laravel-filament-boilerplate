<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        if (! request()->is('admin', 'admin/*') && ! request()->routeIs('filament.admin.*')) {
            return;
        }

        activity()
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('login')
            ->withProperties([
                'guard' => $event->guard,
                'ip' => request()->ip(),
            ])
            ->log('Admin logged in');
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user === null) {
            return;
        }

        if (! request()->is('admin', 'admin/*') && ! request()->routeIs('filament.admin.*')) {
            return;
        }

        activity()
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('logout')
            ->withProperties([
                'guard' => $event->guard,
                'ip' => request()->ip(),
            ])
            ->log('Admin logged out');
    }
}
