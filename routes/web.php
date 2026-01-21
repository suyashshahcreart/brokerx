<?php
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\QRController;
use App\Http\Controllers\Admin\BookingAssigneeController;
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
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\PendingScheduleController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\PhotographerVisitController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\TourManagerController;
use App\Http\Controllers\Admin\TourNotificationController;
use App\Http\Controllers\Admin\QRAnalyticsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\BrokerX\BrokerXController;
use App\Http\Controllers\QR\QRManageController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

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

// Check domain and route accordingly
$domain = request()->getHost();

// Routes for qr.proppik.com domain
if (in_array($domain, ['qr.proppik.com', 'www.qr.proppik.com'])) {
    // Welcome page
    Route::get('/', [QRManageController::class, 'index'])->name('qr.welcome');

    // QR Analytics routes
    Route::get('/analytics', [QRManageController::class, 'analytics'])->name('qr.analytics');

    // Screen resolution and GPS tracking endpoint (AJAX)
    Route::post('/track-screen', function (\Illuminate\Http\Request $request) {
        try {
            if ($request->has('screen_resolution')) {
                $request->session()->put('qr_screen_resolution', $request->input('screen_resolution'));
            }
            if ($request->has('gps_latitude') && $request->has('gps_longitude')) {
                $gpsLat = $request->input('gps_latitude');
                $gpsLng = $request->input('gps_longitude');

                // Validate GPS coordinates
                if (is_numeric($gpsLat) && is_numeric($gpsLng)) {
                    $request->session()->put('qr_gps_latitude', (float) $gpsLat);
                    $request->session()->put('qr_gps_longitude', (float) $gpsLng);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('QR track-screen error: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('qr.track-screen');

    // AJAX endpoint to track visit after GPS coordinates are captured
    Route::post('/track-visit', [QRManageController::class, 'trackVisitAjax'])->name('qr.track-visit');

    // Save tour notification (phone number)
    Route::post('/save-notification', [QRManageController::class, 'saveNotification'])->name('qr.save-notification');

    // Dynamic tour_code route - must be last to catch any parameter
    // Example: /1234Aber
    Route::get('/{tour_code}', [QRManageController::class, 'showByTourCode'])
        ->where('tour_code', '[A-Za-z0-9]+')
        ->name('qr.tour-code');

    // Stop here - don't load other routes for qr.proppik.com
    return;
}

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

// Frontend Portfolio routes (authenticated users)
Route::middleware('auth')->group(function () {
    Route::resource('portfolios', PortfolioController::class);
});

// Optional dashboard alias (to avoid Route [dashboard] not defined errors)
Route::middleware('auth')->get('/dashboard', function () {
    return redirect()->route('admin.index');
})->name('dashboard');

// Broker routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::resource('broker', BrokerController::class);
});



// Route::group(['prefix' => 'themes', 'middleware' => 'auth'], function () {
//     Route::get('', [RoutingController::class, 'index'])->name('root');
//     Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
//     Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
//     Route::get('{any}', [RoutingController::class, 'root'])->name('any');
// });


Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('admin.login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');

Route::group(['prefix' => 'ppadmlog', 'as' => 'admin.', 'middleware' => ['web', 'auth', 'not.customer']], function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    
    
    
    // Profile routes
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/change-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::put('profile/change-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', AdminUserController::class);
    Route::resource('customer', CustomerController::class);
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
    // Cancel and Reassign
    Route::post('booking-assignees/{bookingAssignee}/cancel', [BookingAssigneeController::class, 'cancel'])->name('booking-assignees.cancel');
    Route::post('booking-assignees/{bookingAssignee}/reassign', [BookingAssigneeController::class, 'reassign'])->name('booking-assignees.reassign');
    // Photographer visit check-in/out using BookingAssignee
    Route::get('booking-assignees/{bookingAssignee}/check-in', [BookingAssigneeController::class, 'checkInForm'])->name('booking-assignees.check-in-form');
    Route::post('booking-assignees/{bookingAssignee}/check-in', [BookingAssigneeController::class, 'checkIn'])->name('booking-assignees.check-in');
    Route::get('booking-assignees/{bookingAssignee}/check-out', [BookingAssigneeController::class, 'checkOutForm'])->name('booking-assignees.check-out-form');
    Route::post('booking-assignees/{bookingAssignee}/check-out', [BookingAssigneeController::class, 'checkOut'])->name('booking-assignees.check-out');
    
    Route::post('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('admin.bookings.reschedule');
    Route::post('bookings/{booking}/update-ajax', [BookingController::class, 'updateAjax'])->name('admin.bookings.update-ajax');




    // PHOTOGRAPHER VISITS
    Route::resource('photographer-visits', PhotographerVisitController::class);
    // Portfolios
    Route::resource('portfolios', AdminPortfolioController::class);
    Route::resource('holidays', HolidayController::class);
    Route::resource('tours', TourController::class);
    Route::put('tours/{tour}/update-tour-details', [TourController::class, 'updateTourDetails'])->name('tours.updateTourDetails');
    Route::put('admin/tours/{tour}/update-seo', [TourController::class, 'updateTourSeo'])->name('tours.updateSeo');

    // AJAX Tour routes
    Route::post('tours/{tour}/update-ajax', [TourController::class, 'updateAjax'])->name('admin.tours.update-ajax');
    Route::post('tours/create-ajax', [TourController::class, 'createAjax'])->name('admin.tours.create-ajax');
    Route::post('tours/{tour}/unlink-ajax', [TourController::class, 'unlinkAjax'])->name('admin.tours.unlink-ajax');


    // Tour Manager routes
    Route::get('tour-manager', [TourManagerController::class, 'index'])->name('tour-manager.index');
    Route::get('tour-manager/{booking}', [TourManagerController::class, 'show'])->name('tour-manager.show');
    Route::get('tour-manager/{booking}/upload', [TourManagerController::class, 'edit'])->name('tour-manager.upload');
    Route::put('tour-manager/{booking}', [TourManagerController::class, 'update'])->name('tour-manager.update');
    Route::post('tour-manager/upload-file', [TourManagerController::class, 'uploadFile'])->name('tour-manager.upload-file');
    Route::post('tour-manager/schedule-tour', [TourManagerController::class, 'scheduleTour'])->name('tour-manager.schedule-tour');

    // Tour Notifications routes
    Route::get('tour-notifications', [TourNotificationController::class, 'index'])->name('tour-notifications.index');
    Route::get('tour-notifications/{id}', [TourNotificationController::class, 'show'])->name('tour-notifications.show');

    // QR Analytics routes
    Route::get('qr-analytics', [QRAnalyticsController::class, 'index'])->name('qr-analytics.index');
    Route::get('qr-analytics/{id}', [QRAnalyticsController::class, 'show'])->name('qr-analytics.show');

    Route::resource('settings', SettingController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
    });

    // Settings AJAX/API routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/settings/update', [SettingController::class, 'apiUpdate'])->name('settings.update');
        Route::get('/settings/{name}', [SettingController::class, 'apiGet'])->name('settings.get');

        // FTP Configuration routes
        Route::get('/ftp-configurations', [SettingController::class, 'apiGetFtpConfigurations'])->name('ftp-configurations.index');
        Route::get('/ftp-configurations/{id}', [SettingController::class, 'apiGetFtpConfiguration'])->name('ftp-configurations.show');
        Route::post('/ftp-configurations', [SettingController::class, 'apiStoreFtpConfiguration'])->name('ftp-configurations.store');
        Route::delete('/ftp-configurations/{id}', [SettingController::class, 'apiDeleteFtpConfiguration'])->name('ftp-configurations.destroy');
    });

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
Route::get('/', [FrontendController::class, 'redirectPropPik'])->name('frontend.index');

// Route::get('/', [FrontendController::class, 'login'])->name('frontend.index');
// Route::get('/login', [FrontendController::class, 'login'])->name('frontend.login');
// Route::get('/setup', [FrontendController::class, 'setup'])->name('frontend.setup');
// Route::post('/setup', [FrontendController::class, 'storeBooking'])->name('frontend.setup.store');
// Route::get('/contact', function () {
//     return view('frontend.contact');
// })->name('frontend.contact');

// Route::post('/logout', function () {
//     \Illuminate\Support\Facades\Auth::logout();
//     request()->session()->invalidate();
//     request()->session()->regenerateToken();
//     return redirect()->route('frontend.index');
// })->name('frontend.logout');
// Route::get('/privacy-policy', [FrontendController::class, 'privacyPolicy'])->name('frontend.privacy-policy');
// Route::get('/refund-policy', [FrontendController::class, 'refundPolicy'])->name('frontend.refund-policy');
// Route::get('/terms-conditions', [FrontendController::class, 'termsConditions'])->name('frontend.terms');

// // Protected frontend routes (require authentication)
// Route::middleware('auth')->group(function () {
//     Route::get('/booking-dashboard', [FrontendController::class, 'bookingDashboard'])->name('frontend.booking-dashboard');
//     Route::get('/booking-dashboard-v2', [FrontendController::class, 'bookingDashboardV2'])->name('frontend.booking-dashboard-v2');
//     Route::get('/booking/{id}', [FrontendController::class, 'showBooking'])->name('frontend.booking.show');
//     Route::get('/booking-v2/{id}', [FrontendController::class, 'showBookingV2'])->name('frontend.booking.show-v2');
//     Route::get('/profile', [FrontendController::class, 'profile'])->name('frontend.profile');
// });



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