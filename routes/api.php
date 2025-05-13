<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ImageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/drivers', [UserController::class, 'getDrivers']);
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload/image', [ImageController::class, 'upload']);
    Route::get('/user', function (Request $request) {
        
        return $request->user();
    });
    Route::post('/user/update', [UserController::class, 'updateProfile']);
    Route::put('/user/{id}', [UserController::class, 'update']);
   
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    
    // Locations
    Route::get('/locations', [LocationController::class, 'index']);
    
    // Reservations
    Route::get('/user/reservations', [ReservationController::class, 'getUserReservations']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    Route::get('/reservations/{id}/pdf', [ReservationController::class, 'downloadPdf']);
});


Route::get('/cars/{id}/reserve', [CarController::class, 'showReservation'])
    ->name('cars.reserve')
    ->middleware(['auth']);

// Staff-protected routes
Route::middleware(['auth:sanctum'])->prefix('staff')->group(function () {
    // Cars management
    Route::get('/cars', [CarController::class, 'adminIndex']);
    Route::get('/cars/{id}', [CarController::class, 'show']);
    Route::post('/cars', [CarController::class,'store']);
    Route::put('/cars/{id}', [CarController::class, 'update']);
    
    // Users management
    Route::get('/users', [UserController::class, 'index']);
    // Reservations management
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    
    // Users management
    Route::get('/users/drivers', [UserController::class, 'getDrivers']);
});

// Admin-protected routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Cars management
    Route::get('/cars', [CarController::class, 'adminIndex']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{id}', [CarController::class, 'update']);
    Route::delete('/cars/{id}', [CarController::class, 'destroy']);

    // Users management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/users/search', [UserController::class, 'search']);
    

    // Reservations management
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::post('/reservations/{id}/assign-driver', [ReservationController::class, 'assignDriver']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    Route::get('/reservations/{id}/pdf', [ReservationController::class, 'downloadPdf']);

});