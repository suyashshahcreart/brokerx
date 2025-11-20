<?php
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\QRController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\EmailOtpController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\SettingController;
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

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('admin/', [AdminDashboardController::class, 'index'])->name('root');
    // Route::get('', [RoutingController::class, 'index'])->name('root');
    // Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    // Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    // Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});

Route::group(['prefix' => 'themes', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['web', 'auth']], function () {
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', AdminUserController::class);
    Route::resource('bookings', BookingController::class);
    Route::post('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('admin.bookings.reschedule');
    Route::resource('portfolios', AdminPortfolioController::class);
    Route::resource('holidays', HolidayController::class);
    Route::resource('settings', SettingController::class);
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::resource('qr', QRController::class);
});

Route::group(['prefix' => 'brokerx', 'as' => 'brokerx.', 'middleware' => ['web', 'auth']], function () {
    Route::get('/', [BrokerXController::class, 'index'])->name('index');
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