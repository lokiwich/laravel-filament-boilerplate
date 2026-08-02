<?php

use App\Http\Controllers\Auth\KeycloakController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('auth/keycloak/redirect', [KeycloakController::class, 'redirect'])
        ->name('auth.keycloak.redirect');
    Route::get('auth/keycloak/callback', [KeycloakController::class, 'callback'])
        ->name('auth.keycloak.callback');
});

require __DIR__.'/settings.php';
