<?php

use App\Http\Controllers\Admin\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\HolidayController;
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

// Settings API routes - using web auth for same-origin requests
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/settings/update', [SettingController::class, 'apiUpdate'])->name('api.settings.update');
    Route::get('/settings/{name}', [SettingController::class, 'apiGet'])->name('api.settings.get');
    // Holidays API
    Route::get('/holidays', [HolidayController::class, 'indexAPI']);
    // QR code 
    // API route to get booking detail by ID (returns JSON)
    Route::get('/bookings/api/list', [BookingController::class, 'apiList'])->name('bookings.api-list');
    // Assign booking to QR
    Route::post('/qr/assign-booking', [BookingController::class, 'assignBookingToQr'])->name('qr.assign-booking');
});