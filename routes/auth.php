<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/ppadmlog/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('admin.register');

Route::post('/ppadmlog/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

Route::get('/ppadmlog/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/ppadmlog/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::get('/ppadmlog/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('admin.password.request');

Route::post('/ppadmlog/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('admin.password.email');

Route::get('/ppadmlog/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('admin.password.reset');

Route::post('/ppadmlog/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('admin.password.update');

Route::get('/ppadmlog/verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')
    ->name('admin.verification.notice');

Route::get('/ppadmlog/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('admin.verification.verify');

Route::post('/ppadmlog/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('admin.verification.send');

Route::get('/ppadmlog/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth')
    ->name('admin.password.confirm');

Route::post('/ppadmlog/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware('auth');

Route::post('/ppadmlog/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.logout');

// OTP endpoints for mobile and email (used by frontend auth-login.js)
use App\Http\Controllers\Auth\OtpController;

// Login OTP endpoints (registered users only)
Route::post('/otp/send', [OtpController::class, 'sendMobile'])
    ->middleware('guest')
    ->name('otp.send');

Route::post('/otp/verify', [OtpController::class, 'verifyMobile'])
    ->middleware('guest')
    ->name('otp.verify');

Route::post('/email-otp/send', [OtpController::class, 'sendEmail'])
    ->middleware('guest')
    ->name('email_otp.send');

Route::post('/email-otp/verify', [OtpController::class, 'verifyEmail'])
    ->middleware('guest')
    ->name('email_otp.verify');

// Registration/Verification OTP endpoints (non-registered users allowed)
Route::post('/registration/otp/send', [OtpController::class, 'sendMobileForRegistration'])
    ->middleware('guest')
    ->name('registration.otp.send');

Route::post('/registration/otp/verify', [OtpController::class, 'verifyMobileForRegistration'])
    ->middleware('guest')
    ->name('registration.otp.verify');

Route::post('/registration/email-otp/send', [OtpController::class, 'sendEmailForRegistration'])
    ->middleware('guest')
    ->name('registration.email_otp.send');

Route::post('/registration/email-otp/verify', [OtpController::class, 'verifyEmailForRegistration'])
    ->middleware('guest')
    ->name('registration.email_otp.verify');

// Photographer authentication routes
use App\Http\Controllers\Admin\PhotographerAuthController;

Route::get('/ppadmlog/photographer/register', [PhotographerAuthController::class, 'createRegister'])
    ->middleware('guest')
    ->name('admin.photographer.register');

Route::post('/ppadmlog/photographer/register', [PhotographerAuthController::class, 'storeRegister'])
    ->middleware('guest');

Route::get('/ppadmlog/photographer/login', [PhotographerAuthController::class, 'createLogin'])
    ->middleware('guest')
    ->name('admin.photographer.login');

Route::post('/ppadmlog/photographer/login', [PhotographerAuthController::class, 'storeLogin'])
    ->middleware('guest');

Route::post('/ppadmlog/photographer/logout', [PhotographerAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.photographer.logout');
