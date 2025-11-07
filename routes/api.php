<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ServiceTicketController;
use App\Http\Controllers\API\VehicleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/tickets', [ServiceTicketController::class, 'store']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
});