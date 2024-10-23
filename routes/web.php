<?php

use App\Filament\Resources\RestaurantResource;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\SuperAdminController;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect(RestaurantResource::getUrl());
});

Route::get('/env/{id}', function ($id) {
    $restaurant = Restaurant::with(['db' => function ($query) {
        $query->whereActive(true)
            ->whereIsValid(true);
    }])->findOrFail($id);

    return view('installation.env', compact('restaurant'));
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

Route::get('/stream', function () {
    header("Content-Type: text/event-stream");
    header("Cache-Control: no-cache");
    header("Connection: keep-alive");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

    ob_end_flush();

    while (true) {
        echo "event: message\n";
        echo "data: sdsdhdhsgsf\n";

        if (connection_aborted()) {
            break;
        }

        sleep(1);
    }

    echo "event: stop\n";
    echo "data: stopped\n\n";
});
