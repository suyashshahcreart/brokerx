<?php
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
Route::middleware('auth')->get('/dashboard', function() {
    return redirect()->route('root');
})->name('dashboard');

// Broker routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::resource('broker', BrokerController::class);
});

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('root');
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

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['web','auth']], function () {
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', AdminUserController::class);
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
});

Route::group(['prefix' => 'brokerx', 'as' => 'brokerx.', 'middleware' => ['web','auth']], function () {
    Route::get('/', [BrokerXController::class, 'index'])->name('index');
});