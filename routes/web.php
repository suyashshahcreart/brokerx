<?php

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

require __DIR__ . '/auth.php';

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('root');
    // Route::get('', [RoutingController::class, 'index'])->name('root');
    // Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    // Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    // Route::get('{any}', [RoutingController::class, 'root'])->name('any');
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