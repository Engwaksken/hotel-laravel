<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\WebDataToolsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Shared CSV / PDF / Print Tools
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/data-tools')
        ->name('admin.data-tools.')
        ->group(function (): void {
            Route::get('/', [WebDataToolsController::class, 'index'])
                ->name('index');

            Route::get('/{module}/template', [WebDataToolsController::class, 'template'])
                ->name('template');

            Route::post('/{module}/import', [WebDataToolsController::class, 'import'])
                ->name('import');

            Route::get('/{module}/csv', [WebDataToolsController::class, 'csv'])
                ->name('csv');

            Route::get('/{module}/pdf', [WebDataToolsController::class, 'pdf'])
                ->name('pdf');

            Route::get('/{module}/print', [WebDataToolsController::class, 'print'])
                ->name('print');
        });
});
