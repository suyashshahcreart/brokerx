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

Route::get('/admin/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('admin.register');

Route::post('/admin/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::get('/admin/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('admin.password.request');

Route::post('/admin/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('admin.password.email');

Route::get('/admin/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('admin.password.reset');

Route::post('/admin/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('admin.password.update');

Route::get('/admin/verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')
    ->name('admin.verification.notice');

Route::get('/admin/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('admin.verification.verify');

Route::post('/admin/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('admin.verification.send');

Route::get('/admin/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth')
    ->name('admin.password.confirm');

Route::post('/admin/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware('auth');

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
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

Route::get('/admin/photographer/register', [PhotographerAuthController::class, 'createRegister'])
    ->middleware('guest')
    ->name('admin.photographer.register');

Route::post('/admin/photographer/register', [PhotographerAuthController::class, 'storeRegister'])
    ->middleware('guest');

Route::get('/admin/photographer/login', [PhotographerAuthController::class, 'createLogin'])
    ->middleware('guest')
    ->name('admin.photographer.login');

Route::post('/admin/photographer/login', [PhotographerAuthController::class, 'storeLogin'])
    ->middleware('guest');

Route::post('/admin/photographer/logout', [PhotographerAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.photographer.logout');
