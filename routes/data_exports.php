<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\UniversalExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Universal CSV / PDF Exports
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin/exports')
    ->name('admin.exports.')
    ->group(function (): void {
        Route::get(
            '/{module}/csv',
            [UniversalExportController::class, 'csv']
        )->name('csv');

        Route::get(
            '/{module}/pdf',
            [UniversalExportController::class, 'pdf']
        )->name('pdf');
    });
