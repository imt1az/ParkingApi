

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpaceAvailabilityController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn() => response()->json(['ok' => true]));
    Route::post('/geocode', [GeocodeController::class, 'store']);

    // Public auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/refresh',  [AuthController::class, 'refresh']);

    // Protected
    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/me/location', [LocationController::class, 'store']);
        Route::get('/search/nearby', [SearchController::class, 'nearby']);

        // Provider/Admin only
        Route::post('/spaces', [SpaceController::class, 'store']);           // create
        Route::get('/spaces/my', [SpaceController::class, 'mySpaces']);      // list own
        Route::patch('/spaces/{id}', [SpaceController::class, 'update']);    // update
        Route::get('/bookings/for-my-spaces', [BookingController::class, 'forMySpaces']);

        Route::post('/spaces/{space}/availability', [SpaceAvailabilityController::class, 'store']);

        Route::post('/bookings', [BookingController::class, 'store']); // create booking
        Route::get('/bookings/my', [BookingController::class, 'myBookings']); // driver’s bookings
        
        // Status updates
        Route::patch('/bookings/{id}/confirm',   [BookingController::class, 'confirm']);   // provider/admin
        Route::patch('/bookings/{id}/cancel',    [BookingController::class, 'cancel']);    // driver/provider/admin
        Route::patch('/bookings/{id}/check-in',  [BookingController::class, 'checkIn']);   // provider/admin
        Route::patch('/bookings/{id}/check-out', [BookingController::class, 'checkOut']);  // provider/admin

        // Reports
        Route::get('/reports/provider/monthly', [BookingController::class, 'monthlyReport']);
    });


    // Public
    Route::get('/spaces/{id}', [SpaceController::class, 'show']);
    Route::get('/spaces/{space}/availability', [SpaceAvailabilityController::class, 'index']);

    // Public search
    Route::get('/search', [SearchController::class, 'index']);
});
