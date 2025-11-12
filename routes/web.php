<?php

use App\Http\Controllers\OtpController;
use App\Http\Controllers\EmailOtpController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\AppointmentController;
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

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Standard Laravel Breeze authentication routes for Users/Brokers
| Location: routes/auth.php
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Routes accessible without any authentication
*/

// Scheduler Public Authentication Routes (OTP-based, no password)
Route::prefix('schedulers')->name('schedulers.')->group(function () {
    // OTP endpoints for scheduler authentication
    Route::post('otp/send', [SchedulerController::class, 'sendOtp'])->name('otp.send');
    Route::post('otp/verify', [SchedulerController::class, 'verifyOtp'])->name('otp.verify');
    
    // Registration and Login pages
    Route::get('register', [SchedulerController::class, 'showRegister'])->name('register');
    Route::post('register', [SchedulerController::class, 'register'])->name('register.store');
    Route::get('login', [SchedulerController::class, 'showLogin'])->name('login');
    Route::post('login', [SchedulerController::class, 'login'])->name('login.attempt');
});

/*
|--------------------------------------------------------------------------
| Standard User/Broker Routes (Laravel Auth)
|--------------------------------------------------------------------------
| Protected by 'auth' middleware - requires standard Laravel authentication
*/

Route::middleware(['auth'])->group(function () {
    
    // Dashboard (Root)
    Route::get('/', [AdminDashboardController::class, 'index'])->name('root');
    
    // Optional dashboard alias (to avoid Route [dashboard] not defined errors)
    Route::get('/dashboard', function () {
        return redirect()->route('root');
    })->name('dashboard');
    
    // OTP Routes for User Phone/Email Verification
    Route::prefix('otp')->name('otp.')->group(function () {
        Route::post('send', [OtpController::class, 'send'])->name('send');
        Route::post('verify', [OtpController::class, 'verify'])->name('verify');
    });
    
    Route::prefix('email-otp')->name('email_otp.')->group(function () {
        Route::post('send', [EmailOtpController::class, 'send'])->name('send');
        Route::post('verify', [EmailOtpController::class, 'verify'])->name('verify');
    });
    
    // User Profile Management
    Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
    
    // Broker Management
    Route::resource('broker', BrokerController::class);
    
    // Appointment Management (User/Broker side)
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('json', [AppointmentController::class, 'getAppointmentsJson'])->name('json');
    });
    
    Route::resource('appointments', AppointmentController::class);
    
    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    | Administrative panel routes for managing permissions, roles, users
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        // Permission Management
        Route::resource('permissions', PermissionController::class);
        
        // Role Management
        Route::resource('roles', RoleController::class);
        
        // User Management
        Route::resource('users', AdminUserController::class);
        
        // Activity Log
        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
    });
    
    /*
    |--------------------------------------------------------------------------
    | BrokerX Module Routes
    |--------------------------------------------------------------------------
    | Custom BrokerX functionality
    */
    Route::prefix('brokerx')->name('brokerx.')->group(function () {
        Route::get('/', [BrokerXController::class, 'index'])->name('index');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Theme Demo Routes
    |--------------------------------------------------------------------------
    | Routes for theme demonstration and UI components
    | Note: Uses original route names (second, third, any) for backward compatibility
    */
    Route::prefix('themes')->group(function () {
        Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
        Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
        Route::get('{any}', [RoutingController::class, 'root'])->name('any');
    });
});

/*
|--------------------------------------------------------------------------
| Scheduler Routes (Session-based Auth)
|--------------------------------------------------------------------------
| Protected by 'scheduler.auth' middleware - requires scheduler_id in session
| Schedulers use OTP-based mobile authentication (no password field)
*/

Route::middleware(['scheduler.auth'])->prefix('schedulers')->name('schedulers.')->group(function () {
    
    // Scheduler Dashboard & Profile
    Route::get('/', [SchedulerController::class, 'index'])->name('index');
    Route::get('create', [SchedulerController::class, 'create'])->name('create');
    Route::post('/', [SchedulerController::class, 'store'])->name('store');
    Route::get('{scheduler}', [SchedulerController::class, 'show'])->name('show');
    Route::get('{scheduler}/edit', [SchedulerController::class, 'edit'])->name('edit');
    Route::put('{scheduler}', [SchedulerController::class, 'update'])->name('update');
    Route::delete('{scheduler}', [SchedulerController::class, 'destroy'])->name('destroy');
    
    // Scheduler Appointments (FullCalendar JSON endpoint)
    Route::get('appointments/json', [SchedulerController::class, 'getAppointmentsJson'])->name('appointments.json');
    
    // Update Appointment
    Route::post('appointments/{id}/update', [SchedulerController::class, 'updateAppointment'])->name('appointments.update');
    
    // Logout
    Route::post('logout', [SchedulerController::class, 'logout'])->name('logout');
});
