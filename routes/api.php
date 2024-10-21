<?php

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/settings/{any}', [RestaurantController::class, 'index'])->where('any', '.*');
});

Route::post('/settings/{childRestaurant}', [SettingsController::class, 'update']);
