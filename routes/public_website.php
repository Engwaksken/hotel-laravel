<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\GuestFeatureSettingsController;
use App\Http\Controllers\Admin\WebsiteContentController;
use App\Http\Controllers\PublicSite\ClientLandingController;
use App\Http\Controllers\PublicSite\GuestFeatureController;
use App\Http\Controllers\PublicSite\HotelWebsiteController;
use App\Http\Controllers\PublicSite\OnlineBookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Hotel Website
|--------------------------------------------------------------------------
|
| Public/client-facing hotel pages.
|
| - "/"           = main public hotel landing page
| - "/hotel"      = friendly alias to the public landing page
| - "/login"      = staff/admin login (defined in routes/web.php)
| - "/dashboard"  = authenticated staff/admin dashboard
|
| IMPORTANT:
| Do not register the staff dashboard on "/".
|
*/

Route::get(
    '/',
    [ClientLandingController::class, 'home']
)->name('client.landing');

/*
|--------------------------------------------------------------------------
| Public Hotel Entry
|--------------------------------------------------------------------------
|
| Keep the route name "public.hotel" because public website buttons,
| service pages and other guest-facing views use route('public.hotel').
|
*/

Route::get(
    '/hotel',
    [ClientLandingController::class, 'home']
)->name('public.hotel');

Route::get('/hotel/{slug}', [HotelWebsiteController::class, 'home'])
    ->name('public.hotel.property');

Route::get('/hotel/{slug}/book', [HotelWebsiteController::class, 'booking'])
    ->name('public.booking');

Route::post('/hotel/{slug}/book', [OnlineBookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.booking.store');

Route::get('/hotel/{slug}/booking-confirmation/{reservation:reference}', [OnlineBookingController::class, 'confirmation'])
    ->name('public.booking.confirmation');

/*
|--------------------------------------------------------------------------
| Public Hotel Services
|--------------------------------------------------------------------------
*/

Route::get(
    '/services',
    [ClientLandingController::class, 'services']
)->name('public.services');

Route::get(
    '/services/{slug}',
    [ClientLandingController::class, 'service']
)->name('public.services.show');

/*
|--------------------------------------------------------------------------
| Public Guest Comments
|--------------------------------------------------------------------------
|
| Guest comments are saved as pending and must be moderated before they
| are displayed publicly.
|
*/

Route::post(
    '/guest-comments',
    [GuestFeatureController::class, 'storeComment']
)->name('public.comments.store');

/*
|--------------------------------------------------------------------------
| Public Hotel Chatbot
|--------------------------------------------------------------------------
*/

Route::post(
    '/hotel-chatbot/message',
    [GuestFeatureController::class, 'chatbot']
)->name('public.chatbot.message');

/*
|--------------------------------------------------------------------------
| Admin Website Content Management
|--------------------------------------------------------------------------
|
| Allows authorised hotel administrators to manage:
| homepage banners, images, services, service descriptions, pricing,
| display order and publication status.
|
*/

Route::middleware('auth')
    ->prefix('admin/website')
    ->name('admin.website.')
    ->group(function (): void {

        Route::get(
            '/',
            [WebsiteContentController::class, 'index']
        )->name('index');

        /*
        |--------------------------------------------------------------------------
        | Homepage Banners
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/banners',
            [WebsiteContentController::class, 'storeBanner']
        )->name('banners.store');

        Route::put(
            '/banners/{banner}',
            [WebsiteContentController::class, 'updateBanner']
        )->name('banners.update');

        Route::delete(
            '/banners/{banner}',
            [WebsiteContentController::class, 'destroyBanner']
        )->name('banners.destroy');

        /*
        |--------------------------------------------------------------------------
        | Hotel Services
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/services',
            [WebsiteContentController::class, 'storeService']
        )->name('services.store');

        Route::put(
            '/services/{service}',
            [WebsiteContentController::class, 'updateService']
        )->name('services.update');

        Route::delete(
            '/services/{service}',
            [WebsiteContentController::class, 'destroyService']
        )->name('services.destroy');
    });

/*
|--------------------------------------------------------------------------
| Admin Guest Experience & Integrations
|--------------------------------------------------------------------------
|
| Allows authorised administrators to configure:
| accessibility, chatbot, FAQs, guest comments and URA EFRIS.
|
*/

Route::middleware('auth')
    ->prefix('admin/guest-features')
    ->name('admin.guest-features.')
    ->group(function (): void {

        Route::get(
            '/',
            [GuestFeatureSettingsController::class, 'index']
        )->name('index');

        /*
        |--------------------------------------------------------------------------
        | Accessibility Settings
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/accessibility',
            [GuestFeatureSettingsController::class, 'updateAccessibility']
        )->name('accessibility.update');

        /*
        |--------------------------------------------------------------------------
        | Chatbot Settings
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/chatbot',
            [GuestFeatureSettingsController::class, 'updateChatbot']
        )->name('chatbot.update');

        /*
        |--------------------------------------------------------------------------
        | Chatbot FAQs
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/faqs',
            [GuestFeatureSettingsController::class, 'storeFaq']
        )->name('faqs.store');

        Route::delete(
            '/faqs/{faq}',
            [GuestFeatureSettingsController::class, 'destroyFaq']
        )->name('faqs.destroy');

        /*
        |--------------------------------------------------------------------------
        | Guest Comments
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/comments',
            [GuestFeatureSettingsController::class, 'updateComments']
        )->name('comments.update');

        Route::put(
            '/comments/{comment}',
            [GuestFeatureSettingsController::class, 'moderateComment']
        )->name('comments.moderate');

        /*
        |--------------------------------------------------------------------------
        | URA EFRIS
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/efris',
            [GuestFeatureSettingsController::class, 'updateEfris']
        )->name('efris.update');
    });
