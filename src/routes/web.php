<?php

use AMoschou\RemoteAuth\App\Http\Controllers\LoginController;
use AMoschou\RemoteAuth\App\Http\Middleware\Authenticate as Auth;
use AMoschou\RemoteAuth\App\Http\Middleware\RedirectIfAuthenticated as SoftGuest;
use AMoschou\RemoteAuth\App\Http\Middleware\AbortIfAuthenticated as HardGuest;
use AMoschou\RemoteAuth\Settings;
use Illuminate\Routing\Middleware\ValidateSignature as Signed;

// GET  /auth/login             mw: web, guest-soft          (optional)
// POST /auth/login             mw: web, guest-soft          (optional)
// POST /auth/logout            mw: web, auth                (optional)
// GET  /auth/sign/{username}   mw: web, guest-hard, signed

$web = [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
];

Route::prefix('auth')->middleware($web)->group(function () {

    if (Settings::$registersLogInOutRoutes) {
        Route::middleware(SoftGuest::class)->group(function () {

            Route::get('login', fn () => view('remote-auth::login'))->name('login');

            Route::post('login', [LoginController::class, 'authenticate'])->name('login.post');

        });

        Route::middleware(Auth::class)->group(function () {

            Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        });
    }

    Route::middleware([HardGuest::class, Signed::class])->group(function () {

        Route::get('sign/{username}', [LoginController::class, 'sign'])->name('sign');

    });
    
});
