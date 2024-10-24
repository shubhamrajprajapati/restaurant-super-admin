<?php

use App\Http\Controllers\API\V1\SuperAdminController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    Route::get('/settings/{any}', [RestaurantController::class, 'index'])->where('any', '.*');
    // Receive installation success and failure status
    Route::post('/update-installation-status', [SuperAdminController::class, 'installationStatus'])->name('update-installation-status');
});

Route::post('/settings/{childRestaurant}', [SettingsController::class, 'update']);