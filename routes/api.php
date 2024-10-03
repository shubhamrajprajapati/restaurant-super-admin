<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/super-admin/settings/{childRestaurant}', [SettingsController::class, 'index']);
Route::post('/super-admin/settings/{childRestaurant}', [SettingsController::class, 'update']);
