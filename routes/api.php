<?php

use App\Http\Controllers\Admin\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Admin\ajax\BookingAssigneController;
use App\Http\Controllers\Admin\Api\TourManagerController;
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

// Rest API's
// Tour Manager APIs
Route::post('/tour-manager/login', [TourManagerController::class, 'login']);
Route::get('/tour-manager/customers', [TourManagerController::class, 'getCustomers']);
Route::get('/tour-manager/tours-by-customer', [TourManagerController::class, 'getToursByCustomer']);
Route::put('/tour-manager/working_json/{tour_id}', [TourManagerController::class, 'updateWorkingJson']);

// Tour Access APIs
Route::get('/tour/is_active/{tour_code}', [\App\Http\Controllers\Api\TourAccessController::class, 'checkIsActive']);
Route::get('/tour/tour_credentials/{tour_code}', [\App\Http\Controllers\Api\TourAccessController::class, 'checkIsCredentials']);
Route::post('/tour/login', [\App\Http\Controllers\Api\TourAccessController::class, 'login']);

// Route::middleware('auth')->group(function () {
// });



// Laravel authenticated user route
Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected Settings API routes - using web auth for same-origin requests
Route::middleware(['web', 'auth'])->group(function () {
    // Public Settings API routes (no auth required for frontend setup page)
    Route::get('/settings/{name}', [SettingController::class, 'apiGet'])->name('api.settings.get.public');
    
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

    // Booking assignee slots for photographers 
    Route::get('/booking-assignees/slots', [BookingAssigneController::class, 'slots'])->name('api.booking-assignees.slots');
    Route::get('/booking-assignees/all-bookings', [BookingAssigneController::class, 'getAllBookings'])->name('api.booking-assignees.all-bookings');
    Route::get('/bookings/{id}', [BookingApiController::class, 'show'])->name('api.bookings.show');
    Route::get('/bookings/{id}/json', [BookingApiController::class, 'getJson'])->name('api.bookings.get-json');
    Route::post('/bookings/{id}/json', [BookingApiController::class, 'setJson'])->name('api.bookings.set-json');
    Route::get('/bookings/api/list', [BookingController::class, 'apiList'])->name('bookings.api-list');
    Route::get('/bookings/details', [BookingController::class, 'getBookingDetails'])->name('bookings.details');

    // QR code 
    Route::post('/qr/assign-booking', [BookingController::class, 'assignBookingToQr'])->name('qr.assign-booking');
});