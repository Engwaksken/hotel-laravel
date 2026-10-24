<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileHotelController;
use App\Http\Controllers\Api\MobileProfileController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hotel HMS Mobile API
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Public Authentication
|--------------------------------------------------------------------------
*/

Route::post('/login', [MobileAuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

// Payment providers call this endpoint without a staff/guest access token.
Route::post('/v1/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->whereIn('gateway', ['mtn_momo', 'airtel_momo', 'pesapal', 'flutterwave', 'card'])
    ->middleware('throttle:60,1')
    ->name('api.v1.payments.webhook');

/*
|--------------------------------------------------------------------------
| Authenticated Mobile API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function (): void {

    Route::get('/v1/reservations', [ReservationController::class, 'index'])
        ->name('api.v1.reservations.index');

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::get('/me', [MobileAuthController::class, 'me'])
        ->name('api.me');

    Route::post('/logout', [MobileAuthController::class, 'logout'])
        ->name('api.logout');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::put('/profile', [MobileProfileController::class, 'update'])
        ->name('api.profile.update');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [MobileHotelController::class, 'dashboard'])
        ->name('api.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Rooms
    |--------------------------------------------------------------------------
    */

    Route::get('/rooms', [MobileHotelController::class, 'rooms'])
        ->name('api.rooms');

    Route::get('/rooms/{id}', [MobileHotelController::class, 'room'])
        ->whereNumber('id')
        ->name('api.rooms.show');

    Route::put('/rooms/{id}', [MobileHotelController::class, 'updateRoom'])
        ->whereNumber('id')
        ->name('api.rooms.update');

    Route::delete('/rooms/{id}', [MobileHotelController::class, 'deleteRoom'])
        ->whereNumber('id')
        ->name('api.rooms.destroy');

    /*
    |--------------------------------------------------------------------------
    | Bookings / Reservations
    |--------------------------------------------------------------------------
    */

    Route::get('/bookings', [MobileHotelController::class, 'bookings'])
        ->name('api.bookings');

    Route::get('/bookings/{id}', [MobileHotelController::class, 'booking'])
        ->whereNumber('id')
        ->name('api.bookings.show');

    Route::put('/bookings/{id}', [MobileHotelController::class, 'updateBooking'])
        ->whereNumber('id')
        ->name('api.bookings.update');

    Route::delete('/bookings/{id}', [MobileHotelController::class, 'deleteBooking'])
        ->whereNumber('id')
        ->name('api.bookings.destroy');

    /*
    |--------------------------------------------------------------------------
    | Guests
    |--------------------------------------------------------------------------
    */

    Route::get('/guests', [MobileHotelController::class, 'guests'])
        ->name('api.guests');

    Route::get('/guests/{id}', [MobileHotelController::class, 'guest'])
        ->whereNumber('id')
        ->name('api.guests.show');

    Route::put('/guests/{id}', [MobileHotelController::class, 'updateGuest'])
        ->whereNumber('id')
        ->name('api.guests.update');

    Route::delete('/guests/{id}', [MobileHotelController::class, 'deleteGuest'])
        ->whereNumber('id')
        ->name('api.guests.destroy');

    /*
    |--------------------------------------------------------------------------
    | Staff Tasks
    |--------------------------------------------------------------------------
    */

    Route::get('/tasks', [MobileHotelController::class, 'tasks'])
        ->name('api.tasks');

    /*
    |--------------------------------------------------------------------------
    | Restaurant / Bar / POS
    |--------------------------------------------------------------------------
    */

    Route::get('/pos/orders', [MobileHotelController::class, 'posOrders'])
        ->name('api.pos.orders');

    Route::put('/pos/orders/{id}', [MobileHotelController::class, 'updatePosOrder'])
        ->whereNumber('id')
        ->name('api.pos.orders.update');

    Route::delete('/pos/orders/{id}', [MobileHotelController::class, 'deletePosOrder'])
        ->whereNumber('id')
        ->name('api.pos.orders.destroy');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::get('/inventory/items', [MobileHotelController::class, 'inventoryItems'])
        ->name('api.inventory.items');

    Route::put('/inventory/items/{id}', [MobileHotelController::class, 'updateInventoryItem'])
        ->whereNumber('id')
        ->name('api.inventory.items.update');

    Route::delete('/inventory/items/{id}', [MobileHotelController::class, 'deleteInventoryItem'])
        ->whereNumber('id')
        ->name('api.inventory.items.destroy');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [MobileHotelController::class, 'notifications'])
        ->name('api.notifications');

    Route::post('/notifications/{id}/read', [MobileHotelController::class, 'markNotificationRead'])
        ->name('api.notifications.read');

    Route::post('/notifications/read-all', [MobileHotelController::class, 'markAllNotificationsRead'])
        ->name('api.notifications.read-all');
});
