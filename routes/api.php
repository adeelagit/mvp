<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ServiceTicketController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\LocationController;

Route::post('/register', [AuthController::class, 'register']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('/ticket', [ServiceTicketController::class, 'store']);
    Route::post('/ticket/{id}', [ServiceTicketController::class, 'updateTicketStatus']);
    Route::get('/tickets', [ServiceTicketController::class, 'index']);
    Route::delete('/tickets/{id}', [ServiceTicketController::class, 'delete']);

    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicle/{vehicle}', [VehicleController::class, 'show']);
    Route::put('/vehicle/{vehicle}', [VehicleController::class, 'update']);
    Route::delete('/vehicle/{vehicle}', [VehicleController::class, 'destroy']);

    Route::post('/location', [LocationController::class, 'store']);
    Route::get('/location', [LocationController::class, 'current']);

    Route::post('/vehicle_category', [VehicleController::class, 'storeVehicleCategory']);
    Route::get('/vehicle_categories', [VehicleController::class, 'getVehicleCategory']);
    Route::post('/vehicle_category/{id}', [VehicleController::class, 'updateVehicleCategory']);
    Route::delete('/vehicle_category/{VehicleType}', [VehicleController::class, 'deleteVehicleCategory']);


    Route::post('/vehicle_brands', [VehicleController::class, 'storeVehicleBrands']);
    Route::get('/brands', [VehicleController::class, 'getBrand']);
    Route::delete('/brands/{brand}', [VehicleController::class, 'deleteBrand']);
    Route::post('/brands/{brand}', [VehicleController::class, 'updateBrand']);

    Route::post('/number-plate', [VehicleController::class, 'storeNumberPlate']);
    Route::post('/number-plate/{id}', [VehicleController::class, 'updateNumberPlate']);
    Route::delete('/number-plate/{id}', [VehicleController::class, 'destroyNumberPlate']);
    Route::get('/number-plates', [VehicleController::class, 'getNumberPlates']);

    Route::get('/users', [AuthController::class, 'index']);
    Route::post('/tickets', [ServiceTicketController::class, 'storeTicket']);
    Route::delete('/user/{id}', [AuthController::class, 'deleteUser']);
    Route::get('/tickets/{id}', [ServiceTicketController::class, 'viewTicket']);

});