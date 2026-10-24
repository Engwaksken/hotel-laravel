<?php

use App\Http\Controllers\ManagedEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('managed-entries')->name('managed-entries.')->group(function () {
    Route::get('/{resource}/{id}/edit', [ManagedEntryController::class, 'edit'])->name('edit');
    Route::put('/{resource}/{id}', [ManagedEntryController::class, 'update'])->name('update');
    Route::delete('/{resource}/{id}', [ManagedEntryController::class, 'destroy'])->name('destroy');
});
