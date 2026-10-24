<?php

use App\Http\Controllers\Client\ClientPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Client / Guest Frontend
|--------------------------------------------------------------------------
|
| The public-facing guest experience starts at "/".
| Staff and administrators continue to use "/login".
|
*/

Route::view('/', 'client.landing')
    ->name('client.landing');

/*
|--------------------------------------------------------------------------
| Authenticated Client Portal
|--------------------------------------------------------------------------
|
| Client account pages remain protected because they may contain personal
| booking and payment information.
|
*/

Route::middleware('auth')
    ->prefix('client')
    ->name('client.')
    ->group(function (): void {
        Route::get('/', [ClientPortalController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/services', [ClientPortalController::class, 'services'])
            ->name('services');

        Route::get('/bookings', [ClientPortalController::class, 'bookings'])
            ->name('bookings');

        Route::get('/payments', [ClientPortalController::class, 'payments'])
            ->name('payments');
    });
