<?php

use App\Filament\Resources\RestaurantResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\SuperAdminController;

Route::get('/', function () {
    // return view('welcome');
    return redirect(RestaurantResource::getUrl());
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('/ssh-form', [SSHController::class, 'showForm'])->name('ssh.form');
Route::post('/check-ssh', [SSHController::class, 'checkSSH'])->name('check.ssh');
Route::view('/connect-ssh', 'connect-ssh')->name('connect-ssh');

Route::post('/ssh/execute', [SSHController::class, 'executeSimpleCommand']);

// Super Admin Manage Settings
Route::get('/super-admin/settings/{childRestaurant}', [SuperAdminController::class, 'manageSettings']);
Route::post('/super-admin/settings/{childRestaurant}', [SuperAdminController::class, 'overrideSettings']);
Route::post('/super-admin/install', [SuperAdminController::class, 'installChildRestaurant']);
