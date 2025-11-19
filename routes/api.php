<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;

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
});
