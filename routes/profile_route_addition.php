use App\Http\Controllers\Api\MobileProfileController;

// Add inside the existing auth:sanctum route group:
Route::put('/profile', [MobileProfileController::class, 'update'])
    ->name('api.profile.update');
