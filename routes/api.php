<?php

use App\Http\Controllers\Admin\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Api\BookingApiController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Settings API routes (no auth required for frontend setup page)
Route::get('/settings/{name}', [SettingController::class, 'apiGet'])->name('api.settings.get.public');

// Protected Settings API routes - using web auth for same-origin requests
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/settings/update', [SettingController::class, 'apiUpdate'])->name('api.settings.update');
    // Note: GET /settings/{name} is public (defined above) - no auth required
    // Holidays API
    Route::get('/holidays', [HolidayController::class, 'indexAPI']);

    // API route to get booking detail by ID (returns JSON)
    Route::get('/bookings/api/list', [BookingController::class, 'apiList'])->name('bookings.api-list');
    Route::get('/bookings/details', [BookingController::class, 'getBookingDetails'])->name('bookings.details');

    // Assign booking to QR
    Route::post('/qr/assign-booking', [BookingController::class, 'assignBookingToQr'])->name('qr.assign-booking');

    // Bookings API
    Route::get('/bookings', [BookingApiController::class, 'index'])->name('api.bookings.index');
    Route::get('/bookings/by-date-range', [BookingApiController::class, 'getByDateRange'])->name('api.bookings.by-date-range');
    Route::get('/bookings/{id}', [BookingApiController::class, 'show'])->name('api.bookings.show');
    Route::get('/bookings/{id}/json', [BookingApiController::class, 'getJson'])->name('api.bookings.get-json');
    Route::post('/bookings/{id}/json', [BookingApiController::class, 'setJson'])->name('api.bookings.set-json');
    Route::get('/bookings/api/list', [BookingController::class, 'apiList'])->name('bookings.api-list');
    Route::get('/bookings/details', [BookingController::class, 'getBookingDetails'])->name('bookings.details');

    // QR code 
    Route::post('/qr/assign-booking', [BookingController::class, 'assignBookingToQr'])->name('qr.assign-booking');
});