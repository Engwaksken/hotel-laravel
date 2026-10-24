<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
|
| These routes use Laravel's password broker and the
| password_reset_tokens table.
|
*/

Route::middleware('guest')->group(function (): void {
    Route::get(
        '/forgot-password',
        [ForgotPasswordController::class, 'create']
    )->name('password.request');

    Route::post(
        '/forgot-password',
        [ForgotPasswordController::class, 'store']
    )->name('password.email');

    Route::get(
        '/reset-password/{token}',
        [ResetPasswordController::class, 'create']
    )->name('password.reset');

    Route::post(
        '/reset-password',
        [ResetPasswordController::class, 'store']
    )->name('password.update');
});
