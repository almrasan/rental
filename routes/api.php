<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProfileController;


Route::get('/user', function (Request $request) {
    return $request->user();
        })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/listings', [ListingController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/listings', [ListingController::class, 'store']);
    Route::get('/my-listings', [ListingController::class, 'myListings']);
    Route::get('/my-listings/{id}', [ListingController::class, 'showOwn']);

    Route::put('/listings/{id}', [ListingController::class, 'update']);
    Route::delete('/listings/{id}', [ListingController::class, 'destroy']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    Route::post('/listings/{listingId}/book', [BookingController::class, 'store']);
    Route::put('/bookings/{id}/confirm', [BookingController::class, 'confirm']);
    Route::put('/bookings/{id}/reject', [BookingController::class, 'rejectBooking']);
    Route::get('/my-listing-bookings', [BookingController::class, 'myListingBookings']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::get('/profile/summary', [ProfileController::class, 'summary']);

});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::put('/listings/{id}/approve', [ListingController::class, 'approve']);
    Route::put('/listings/{id}/reject', [ListingController::class, 'reject']);
    Route::get('/listings/pending', [ListingController::class, 'pending']);

});

// Public dynamic route
Route::get('/listings/{id}', [ListingController::class, 'show']);
