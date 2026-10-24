<?php
use App\Http\Controllers\StaffNotificationController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth')->group(function(){
    Route::post('/staff-notifications/{notification}/read',[StaffNotificationController::class,'read'])->name('staff-notifications.read');
    Route::post('/staff-notifications/read-all',[StaffNotificationController::class,'readAll'])->name('staff-notifications.read-all');
});
