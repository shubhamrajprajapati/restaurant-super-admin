<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::get('/super-admin/settings/{childRestaurant}', [SettingsController::class, 'index']);
Route::post('/super-admin/settings/{childRestaurant}', [SettingsController::class, 'update']);