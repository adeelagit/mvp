<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ServiceTicketController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\LocationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/brands', [VehicleController::class, 'getBrand']);

Route::middleware('auth:api')->group(function () {
    Route::post('/tickets', [ServiceTicketController::class, 'store']);

    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicle/{vehicle}', [VehicleController::class, 'show']);
    Route::put('/vehicle/{vehicle}', [VehicleController::class, 'update']);
    Route::delete('/vehicle/{vehicle}', [VehicleController::class, 'destroy']);


    Route::post('/location', [LocationController::class, 'store']);
    Route::get('/location', [LocationController::class, 'current']);
});