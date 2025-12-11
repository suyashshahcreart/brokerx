<?php
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\QRController;
use App\Http\Controllers\Admin\BookingAssigneeController;
use App\Http\Controllers\Admin\PhotographerVisitJobController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\EmailOtpController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Photographer\JobController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BookingStatusController;
use App\Http\Controllers\Admin\PendingScheduleController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\PhotographerVisitController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\BrokerX\BrokerXController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// auth routes
require __DIR__ . '/auth.php';

// OTP routes (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::post('/otp/send', [OtpController::class, 'send'])->name('otp.send');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/email-otp/send', [EmailOtpController::class, 'send'])->name('email_otp.send');
    Route::post('/email-otp/verify', [EmailOtpController::class, 'verify'])->name('email_otp.verify');
});

// User routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
});

// Optional dashboard alias (to avoid Route [dashboard] not defined errors)
Route::middleware('auth')->get('/dashboard', function () {
    return redirect()->route('root');
})->name('dashboard');

// Broker routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::resource('broker', BrokerController::class);
});



Route::group(['prefix' => 'themes', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});



Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['web', 'auth']], function () {
    Route::get('/', [AdminDashboardController::class, 'index']);
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', AdminUserController::class);
    Route::get('assignment-calendar', [BookingController::class, 'AssignementCalender'])->name('assignment-calendar');
    // Booking custom routes (BEFORE resource to prevent route conflicts)
    Route::post('bookings/{booking}/update-ajax', [BookingController::class, 'updateAjax'])->name('bookings.update-ajax');
    Route::post('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');

    // Booking Status Management Routes
    Route::prefix('bookings/{booking}')->name('bookings.status.')->group(function () {
        Route::post('approve-schedule', [BookingStatusController::class, 'approveSchedule'])->name('approve-schedule');
        Route::post('decline-schedule', [BookingStatusController::class, 'declineSchedule'])->name('decline-schedule');
        Route::post('request-reschedule', [BookingStatusController::class, 'requestReschedule'])->name('request-reschedule');
        Route::post('approve-reschedule', [BookingStatusController::class, 'approveReschedule'])->name('approve-reschedule');
        Route::post('decline-reschedule', [BookingStatusController::class, 'declineReschedule'])->name('decline-reschedule');
        Route::post('assign-team-member', [BookingStatusController::class, 'assignToTeamMember'])->name('assign-team-member');
        Route::post('complete-tour', [BookingStatusController::class, 'completeTour'])->name('complete-tour');
        Route::post('start-processing', [BookingStatusController::class, 'startTourProcessing'])->name('start-processing');
        Route::post('complete-processing', [BookingStatusController::class, 'completeTourProcessing'])->name('complete-processing');
        Route::post('publish-tour', [BookingStatusController::class, 'publishTour'])->name('publish-tour');
        Route::post('maintenance', [BookingStatusController::class, 'putUnderMaintenance'])->name('maintenance');
        Route::post('remove-maintenance', [BookingStatusController::class, 'removeFromMaintenance'])->name('remove-maintenance');
        Route::post('expire', [BookingStatusController::class, 'expireBooking'])->name('expire');
        Route::post('change-status', [BookingStatusController::class, 'changeStatus'])->name('change-status');
        Route::get('history', [BookingStatusController::class, 'getBookingHistory'])->name('history');
    });

    // Booking Status Statistics
    Route::get('bookings-status/statistics', [BookingStatusController::class, 'getStatusStatistics'])->name('bookings.status.statistics');
    Route::post('bookings-status/bulk-update', [BookingStatusController::class, 'bulkUpdateStatus'])->name('bookings.status.bulk-update');

    // Pending Schedules Management
    Route::get('pending-schedules', [PendingScheduleController::class, 'index'])->name('pending-schedules.index');
    Route::post('pending-schedules/{booking}/accept', [PendingScheduleController::class, 'accept'])->name('pending-schedules.accept');
    Route::post('pending-schedules/{booking}/decline', [PendingScheduleController::class, 'decline'])->name('pending-schedules.decline');
    // Bookings
    Route::resource('bookings', BookingController::class);
    // Booking Assignees
    Route::resource('booking-assignees', BookingAssigneeController::class);
    Route::post('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('admin.bookings.reschedule');
    Route::post('bookings/{booking}/update-ajax', [BookingController::class, 'updateAjax'])->name('admin.bookings.update-ajax');
    // PHOTOGRAPHER VISITS
    Route::resource('photographer-visits', PhotographerVisitController::class);
    Route::resource('photographer-visit-jobs', PhotographerVisitJobController::class);
    Route::post('photographer-visit-jobs/{photographerVisitJob}/assign', [PhotographerVisitJobController::class, 'assign'])->name('photographer-visit-jobs.assign');
    Route::get('photographer-visit-jobs/{photographerVisitJob}/check-in', [PhotographerVisitJobController::class, 'checkInForm'])->name('photographer-visit-jobs.check-in-form');
    Route::post('photographer-visit-jobs/{photographerVisitJob}/check-in', [PhotographerVisitJobController::class, 'checkIn'])->name('photographer-visit-jobs.check-in');
    Route::get('photographer-visit-jobs/{photographerVisitJob}/check-out', [PhotographerVisitJobController::class, 'checkOutForm'])->name('photographer-visit-jobs.check-out-form');
    Route::post('photographer-visit-jobs/{photographerVisitJob}/check-out', [PhotographerVisitJobController::class, 'checkOut'])->name('photographer-visit-jobs.check-out');
    // Portfolios
    Route::resource('portfolios', AdminPortfolioController::class);
    Route::resource('holidays', HolidayController::class);
    Route::resource('tours', TourController::class);
    // AJAX Tour routes
    Route::post('tours/{tour}/update-ajax', [TourController::class, 'updateAjax'])->name('admin.tours.update-ajax');
    Route::post('tours/create-ajax', [TourController::class, 'createAjax'])->name('admin.tours.create-ajax');
    Route::post('tours/{tour}/unlink-ajax', [TourController::class, 'unlinkAjax'])->name('admin.tours.unlink-ajax');
    // Settings
    Route::resource('settings', SettingController::class);
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
    // QR Code Management
    Route::post('qr/bulk-generate', [QRController::class, 'bulkGenerate'])->name('qr.bulk-generate');
    Route::post('qr/bulk-delete', [QRController::class, 'bulkDelete'])->name('qr.bulk-delete');
    Route::get('qr/{qr}/download', [QRController::class, 'download'])->name('qr.download');
    // QR Code Resource Routes
    Route::resource('qr', QRController::class);
});

Route::group(['prefix' => 'brokerx', 'as' => 'brokerx.', 'middleware' => ['web', 'auth']], function () {
    Route::get('/', [BrokerXController::class, 'index'])->name('index');
});

// Photographer routes
Route::group(['prefix' => 'photo', 'as' => 'photographer.', 'middleware' => ['web', 'auth', 'role:photographer']], function () {
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/accept', [JobController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{job}/complete', [JobController::class, 'complete'])->name('jobs.complete');
    Route::get('/jobs/upcoming', [JobController::class, 'upcoming'])->name('jobs.upcoming');
});

// Public frontend routes
Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
Route::get('/login', [FrontendController::class, 'login'])->name('frontend.login');
Route::get('/setup', [FrontendController::class, 'setup'])->name('frontend.setup');
Route::post('/setup', [FrontendController::class, 'storeBooking'])->name('frontend.setup.store');
Route::get('/privacy-policy', [FrontendController::class, 'privacyPolicy'])->name('frontend.privacy-policy');
Route::get('/refund-policy', [FrontendController::class, 'refundPolicy'])->name('frontend.refund-policy');
Route::get('/terms-conditions', [FrontendController::class, 'termsConditions'])->name('frontend.terms');

// Protected frontend routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/booking-dashboard', [FrontendController::class, 'bookingDashboard'])->name('frontend.booking-dashboard');
});

// Frontend Portfolio routes (authenticated users)
Route::middleware('auth')->group(function () {
    Route::resource('portfolios', PortfolioController::class);
});

// Step-by-step AJAX routes
Route::post('/frontend/setup/save-property-step', [FrontendController::class, 'savePropertyStep'])->name('frontend.setup.save-property');
Route::post('/frontend/setup/save-address-step', [FrontendController::class, 'saveAddressStep'])->name('frontend.setup.save-address');
Route::post('/frontend/setup/get-booking-summary', [FrontendController::class, 'getBookingSummary'])->name('frontend.setup.summary');
Route::post('/frontend/setup/finalize-payment-step', [FrontendController::class, 'finalizePaymentStep'])->name('frontend.setup.finalize-payment');
Route::post('/frontend/setup/update-booking', [FrontendController::class, 'updateBooking'])->name('frontend.setup.update-booking');
Route::middleware('auth')->get('/frontend/setup/user-bookings', [FrontendController::class, 'listUserBookings'])->name('frontend.setup.bookings');
Route::post('/frontend/setup/payment/create-session', [FrontendController::class, 'createCashfreeSession'])->name('frontend.setup.payment.session');
Route::post('/frontend/setup/payment/status', [FrontendController::class, 'refreshCashfreeStatus'])->name('frontend.setup.payment.status');
Route::get('/frontend/setup/payment/callback', [FrontendController::class, 'cashfreeCallback'])->name('frontend.cashfree.callback');
Route::get('/frontend/receipt/download/{booking_id}', [FrontendController::class, 'downloadReceipt'])->name('frontend.download-receipt')->middleware('auth');

// Public OTP routes for frontend (no auth required)
Route::post('/frontend/check-user-send-otp', [FrontendController::class, 'checkUserAndSendOtp'])->name('frontend.check-user-send-otp');
Route::post('/frontend/verify-user-otp', [FrontendController::class, 'verifyUserOtp'])->name('frontend.verify-user-otp');
Route::post('/frontend/login/send-otp', [FrontendController::class, 'sendLoginOtp'])->name('frontend.login.send-otp');