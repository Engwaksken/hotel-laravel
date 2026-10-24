<?php

use App\Http\Controllers\Admin\BulkDataController;
use App\Http\Controllers\Admin\PermissionBulkController;
use App\Http\Controllers\Admin\SystemConfigurationController;
use App\Http\Controllers\GuestQrController;
use App\Http\Controllers\InvoiceDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HMS Enhancement Routes
|--------------------------------------------------------------------------
|
| Require this file from routes/web.php:
| require __DIR__.'/hms_enhancements.php';
|
*/

Route::middleware('auth')->group(function (): void {
    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/configuration', [SystemConfigurationController::class, 'index'])
            ->name('configuration.index');
        Route::post('/configuration/general', [SystemConfigurationController::class, 'updateGeneral'])
            ->name('configuration.general');
        Route::post('/configuration/branding', [SystemConfigurationController::class, 'updateBranding'])
            ->name('configuration.branding');
        Route::post('/configuration/documents', [SystemConfigurationController::class, 'updateDocuments'])
            ->name('configuration.documents');

        Route::get('/permissions/bulk', [PermissionBulkController::class, 'index'])
            ->name('permissions.bulk');
        Route::post('/permissions/bulk', [PermissionBulkController::class, 'sync'])
            ->name('permissions.bulk.sync');

        Route::get('/bulk/{module}/template', [BulkDataController::class, 'template'])
            ->name('bulk.template');
        Route::get('/bulk/{module}/export', [BulkDataController::class, 'export'])
            ->name('bulk.export');
        Route::post('/bulk/{module}/import', [BulkDataController::class, 'import'])
            ->name('bulk.import');
    });

    Route::get('/invoices/{invoice}/preview', [InvoiceDocumentController::class, 'show'])
        ->name('invoices.preview');
    Route::get('/invoices/{invoice}/pdf', [InvoiceDocumentController::class, 'pdf'])
        ->name('invoices.pdf');
    Route::post('/invoices/{invoice}/receipts', [InvoiceDocumentController::class, 'receipt'])
        ->name('invoices.receipts.store');
    Route::get('/receipts/{receipt}/pdf', [InvoiceDocumentController::class, 'receiptPdf'])
        ->name('receipts.pdf');

    Route::get('/guests/{guest}/qr', [GuestQrController::class, 'show'])
        ->name('guests.qr');
    Route::get('/guests/{guest}/qr.svg', [GuestQrController::class, 'svg'])
        ->name('guests.qr.svg');
});

Route::get('/guest/verify/{guest}/{token}', [GuestQrController::class, 'verify'])
    ->middleware('signed')
    ->name('guests.qr.verify');
