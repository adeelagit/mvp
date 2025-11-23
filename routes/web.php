<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\OneChargeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/phpinfo', function () {
    return phpinfo();
});

Route::get('/login-form', function () {
    return view('login');
})->name('login');

Route::get('/register-form', function () {
    return view('user_register');
})->name('user.register');

// Main Admin View
Route::get('admin/dashboard',[OneChargeController::class, 'index'])->name('admin.dashboard');

// Route::prefix('api')->group(function () {
    // Route::get('/init-data', [OneChargeController::class, 'getAllData']);
    
    // Route::post('/users', [OneChargeController::class, 'storeUser']);
    Route::post('/types', [OneChargeController::class, 'storeType']);
    // Route::post('/brands', [OneChargeController::class, 'storeBrand']);
    // Route::post('/vehicles', [OneChargeController::class, 'storeVehicle']);
    // Route::post('/tickets', [OneChargeController::class, 'updateTicket']); // Just updating status in this demo
    // Route::post('/plates', [OneChargeController::class, 'storePlate']);
    
    // // Deletion routes
    // Route::delete('/users/{id}', [OneChargeController::class, 'deleteUser']);
    // Route::delete('/types/{id}', [OneChargeController::class, 'deleteType']);
    // Route::delete('/brands/{id}', [OneChargeController::class, 'deleteBrand']);
    // Route::delete('/vehicles/{id}', [OneChargeController::class, 'deleteVehicle']);
    // Route::delete('/plates/{id}', [OneChargeController::class, 'deletePlate']);
// });
