<?php

use App\Http\Controllers\Admin\GuestFeatureSettingsController;
use App\Http\Controllers\PublicSite\GuestFeatureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Guest Features
|--------------------------------------------------------------------------
*/

Route::post(
    '/guest-comments',
    [GuestFeatureController::class, 'storeComment']
)->name('public.comments.store');

Route::post(
    '/hotel-chatbot/message',
    [GuestFeatureController::class, 'chatbot']
)->name('public.chatbot.message');

/*
|--------------------------------------------------------------------------
| Admin Guest Experience / Integrations
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin/guest-features')
    ->name('admin.guest-features.')
    ->group(function (): void {
        Route::get(
            '/',
            [GuestFeatureSettingsController::class, 'index']
        )->name('index');

        Route::put(
            '/accessibility',
            [GuestFeatureSettingsController::class, 'updateAccessibility']
        )->name('accessibility.update');

        Route::put(
            '/chatbot',
            [GuestFeatureSettingsController::class, 'updateChatbot']
        )->name('chatbot.update');

        Route::put(
            '/efris',
            [GuestFeatureSettingsController::class, 'updateEfris']
        )->name('efris.update');

        Route::put(
            '/comments',
            [GuestFeatureSettingsController::class, 'updateComments']
        )->name('comments.update');

        Route::put(
            '/comments/{comment}',
            [GuestFeatureSettingsController::class, 'moderateComment']
        )->name('comments.moderate');

        Route::post(
            '/faqs',
            [GuestFeatureSettingsController::class, 'storeFaq']
        )->name('faqs.store');

        Route::delete(
            '/faqs/{faq}',
            [GuestFeatureSettingsController::class, 'destroyFaq']
        )->name('faqs.destroy');
    });
